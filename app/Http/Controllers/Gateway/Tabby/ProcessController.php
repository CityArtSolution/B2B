<?php

namespace App\Http\Controllers\Gateway\Tabby;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\ShopSubscription;
use App\Services\OrderStockService;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Tabby (pay in 4) checkout.
 *
 * Flow: a checkout session is created and the customer is sent to Tabby. When Tabby authorises the
 * payment, either the customer's redirect (callback) or Tabby's webhook asks Tabby for the payment,
 * captures it, and only then marks the payment and its orders as paid. Neither the redirect nor the
 * webhook body is trusted on its own: the payment is always read back from the Tabby API.
 */
class ProcessController extends Controller
{
    /** Header Tabby sends with every webhook, registered by `php artisan tabby:register-webhook` */
    public const WEBHOOK_HEADER = 'X-Tabby-Webhook-Secret';

    private const UUID = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    /**
     * Process to Tabby Payment
     *
     * @return string   // redirect url, or a JSON {"error": "..."} string
     */
    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        $config = json_decode($paymentGateway->config);

        if (empty($config->secret_key) || empty($config->merchant_code)) {
            return json_encode(['error' => 'Tabby secret key or merchant code is not configured']);
        }

        $orders = $payment->orders()->with(['products.categories', 'address', 'customer.user'])->get();
        $subscription = $orders->isEmpty() ? ShopSubscription::with('shop')->where('payment_id', $payment->id)->first() : null;
        $user = $orders->first()?->customer?->user ?? $subscription?->shop?->user;

        $name = trim(($info['name'] ?? $user?->name ?? '').' '.($info['last_name'] ?? $user?->last_name ?? ''));
        $email = $info['email'] ?? $user?->email;
        $phone = customerPhone($info['phone'] ?? $user?->phone, $info['phone_code'] ?? $user?->phone_code);

        // Tabby needs a real email and mobile number: it sends the customer an OTP and scores on them
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! $phone) {
            Log::warning('Tabby checkout skipped: customer has no valid email or phone', ['payment_id' => $payment->id]);

            return json_encode(['error' => 'Please add a valid email and mobile number to your account before paying with Tabby.']);
        }

        $currency = self::currency($config);
        $buyer = [
            'name' => $name !== '' ? $name : 'Customer',
            'email' => $email,
            'phone' => '+'.$phone['country_code'].$phone['number'],
        ];

        $payload = [
            'merchant_code' => $config->merchant_code,
            'lang' => app()->getLocale() === 'en' ? 'en' : 'ar',
            'payment' => [
                'amount' => self::money($payment->amount),
                'currency' => $currency,
                'description' => $info['description'] ?? 'COQUI Store order #'.$payment->id,
                'buyer' => $buyer,
                'shipping_address' => self::shippingAddress($orders->first(), $subscription),
                'order' => self::orderDetails($payment, $orders, $info),
                'buyer_history' => self::buyerHistory($user),
                'order_history' => self::orderHistory($orders, $buyer),
                'meta' => [
                    'order_id' => (string) $payment->id,
                ],
            ],
            // Tabby adds ?payment_id= to these; the callback reads the payment back from Tabby
            'merchant_urls' => [
                'success' => route('tabby.payment.callback', $payment->id),
                'cancel' => route('tabby.payment.callback', $payment->id),
                'failure' => route('tabby.payment.callback', $payment->id),
            ],
        ];

        try {
            $response = self::http($config)->post('/api/v2/checkout', $payload);
            $data = $response->json() ?? [];

            $webUrl = $data['configuration']['available_products']['installments'][0]['web_url'] ?? null;
            if ($response->successful() && $webUrl && ! empty($data['payment']['id'])) {
                // The callback and webhook only accept the Tabby payment created for this payment
                $payment->update(['payment_token' => $data['payment']['id']]);

                return $webUrl;
            }

            // Tabby created the session but rejected the buyer/amount, or the request itself failed
            $rejectionReason = $data['configuration']['products']['installments']['rejection_reason'] ?? null;
            Log::warning('Tabby checkout not available', [
                'payment_id' => $payment->id,
                'http_status' => $response->status(),
                'status' => $data['status'] ?? null,
                'rejection_reason' => $rejectionReason,
                'error' => $data['error'] ?? $data['errors'] ?? null,
            ]);

            return json_encode(['error' => self::rejectionMessage($rejectionReason, $response->successful())]);
        } catch (\Throwable $th) {
            Log::error('Tabby request failed', ['payment_id' => $payment->id, 'exception' => get_class($th)]);

            return json_encode(['error' => 'Unable to connect to Tabby. Please try again.']);
        }
    }

    /**
     * Customer comes back from Tabby (success, cancel or failure).
     * The payment status is read from Tabby, never taken from the URL.
     */
    public function callback(Request $request, Payment $payment)
    {
        $tabbyId = $request->query('payment_id');
        if (! is_string($tabbyId) || ! preg_match(self::UUID, $tabbyId)) {
            $tabbyId = $payment->payment_token;
        }
        if (! is_string($tabbyId) || ! preg_match(self::UUID, $tabbyId)) {
            abort(400, 'Invalid Tabby payment reference');
        }

        $isSubscription = ShopSubscription::where('payment_id', $payment->id)->exists();
        $config = self::gatewayConfig();

        try {
            $tabbyPayment = self::retrieve($config, $tabbyId);
        } catch (\Throwable $exception) {
            Log::warning('Tabby payment verification failed', ['payment_id' => $payment->id, 'exception' => get_class($exception)]);

            return $isSubscription
                ? to_route('subscription.payment.cancel', ['payment' => $payment, 'token' => $payment->payment_token])
                : to_route('order.payment.cancel', ['payment' => $payment, 'error' => 'Payment verification is temporarily unavailable.']);
        }

        if (! self::belongsTo($tabbyPayment, $payment, $config)) {
            abort(400, 'Tabby payment details do not match');
        }

        $paid = self::settle($config, $payment, $tabbyPayment, ! $isSubscription);

        // Subscription payments are activated by SubscriptionController once the payment is captured
        if ($isSubscription) {
            $params = ['payment' => $payment, 'token' => $payment->payment_token];
            if (! $paid) {
                return to_route('subscription.payment.cancel', $params);
            }

            // Signed so the success page cannot be reached by editing the cancel URL
            return redirect()->to(URL::temporarySignedRoute('subscription.payment.success', now()->addMinutes(30), $params));
        }

        $payment->refresh();
        if ($payment->is_paid) {
            return to_route('order.payment.success', $payment);
        }

        $status = strtoupper($tabbyPayment['status'] ?? '');

        return to_route('order.payment.cancel', ['payment' => $payment, 'error' => match ($status) {
            'REJECTED' => 'Sorry, Tabby is unable to approve this purchase. Please use an alternative payment method for your order.',
            'EXPIRED', 'CREATED' => 'Payment with Tabby was cancelled.',
            default => 'Payment has not been completed.',
        }]);
    }

    /**
     * Tabby server notification, sent on every payment status change (authorized, closed, rejected, ...),
     * also when the customer never comes back to the site.
     * Returns 200 once handled or deliberately ignored; any other status makes Tabby retry.
     */
    public function webhook(Request $request)
    {
        $config = self::gatewayConfig();

        if (! hash_equals(self::webhookSecret($config), (string) $request->header(self::WEBHOOK_HEADER))) {
            Log::warning('Tabby webhook rejected: bad signature header', ['ip' => $request->ip()]);

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $tabbyId = $request->input('id');
        if (! is_string($tabbyId) || ! preg_match(self::UUID, $tabbyId)) {
            return response()->json(['received' => true]);
        }

        try {
            // The webhook body only says which payment changed; its status is read from Tabby
            $tabbyPayment = self::retrieve($config, $tabbyId);
        } catch (\Throwable $exception) {
            Log::warning('Tabby webhook: payment lookup failed', ['tabby_payment_id' => $tabbyId, 'exception' => get_class($exception)]);

            return response()->json(['message' => 'Verification unavailable'], 503);
        }

        $payment = Payment::find($tabbyPayment['order']['reference_id'] ?? null);
        if (! $payment || strcasecmp((string) $payment->payment_method, 'tabby') !== 0 || ! self::belongsTo($tabbyPayment, $payment, $config)) {
            Log::warning('Tabby webhook for an unknown payment', ['tabby_payment_id' => $tabbyId]);

            return response()->json(['received' => true]);
        }

        // A subscription is activated when the shop comes back to the site (see callback), which also
        // captures the payment; an abandoned subscription payment is left to expire uncaptured
        if (ShopSubscription::where('payment_id', $payment->id)->exists()) {
            return response()->json(['received' => true]);
        }

        self::settle($config, $payment, $tabbyPayment, true);

        return response()->json(['received' => true]);
    }

    /**
     * Refund part or all of a captured Tabby payment.
     * Throws a RuntimeException with a message for the admin when Tabby refuses it.
     *
     * @return array  the Tabby payment after the refund
     */
    public static function refund(Payment $payment, float $amount, ?string $reason = null): array
    {
        $config = self::gatewayConfig();
        $tabbyId = (string) $payment->payment_token;

        if (! $payment->is_paid || ! preg_match(self::UUID, $tabbyId)) {
            throw new \RuntimeException(__('This order has no captured Tabby payment to refund.'));
        }

        $tabbyPayment = self::retrieve($config, $tabbyId);
        if (strtoupper($tabbyPayment['status'] ?? '') !== 'CLOSED') {
            throw new \RuntimeException(__('Tabby can only refund a captured (closed) payment.'));
        }

        $refunds = $tabbyPayment['refunds'] ?? [];
        $refunded = collect($refunds)->sum(fn ($refund) => (float) ($refund['amount'] ?? 0));
        if (round($refunded + $amount, 2) > round((float) $tabbyPayment['amount'], 2)) {
            throw new \RuntimeException(__('Refund is more than the amount left on this Tabby payment (:left).', [
                'left' => self::money((float) $tabbyPayment['amount'] - $refunded),
            ]));
        }

        $payload = [
            'amount' => self::money($amount),
            // Numbered from the refunds Tabby already has, so a double-submitted form refunds once
            'reference_id' => 'refund-'.$payment->id.'-'.(count($refunds) + 1),
        ];
        if (filled($reason)) {
            $payload['reason'] = mb_substr($reason, 0, 255);
        }

        $response = self::http($config)->post('/api/v2/payments/'.$tabbyId.'/refunds', $payload);
        if (! $response->successful()) {
            Log::warning('Tabby refund failed', ['payment_id' => $payment->id, 'http_status' => $response->status(), 'error' => $response->json('error')]);

            throw new \RuntimeException(__('Tabby refused the refund: :error', ['error' => $response->json('error') ?? $response->status()]));
        }

        Log::info('Tabby refund sent', ['payment_id' => $payment->id, 'amount' => $payload['amount']]);

        return $response->json();
    }

    /**
     * Capture an authorised Tabby payment and mark ours paid once Tabby holds the full amount.
     * Runs under a row lock, so the redirect and the webhook cannot capture or mark it twice.
     *
     * @return bool  whether the Tabby payment is fully captured
     */
    private static function settle(object $config, Payment $payment, array $tabbyPayment, bool $markPaid): bool
    {
        $tabbyId = $tabbyPayment['id'];

        return DB::transaction(function () use ($config, $payment, $tabbyPayment, $tabbyId, $markPaid) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();
            $status = strtoupper($tabbyPayment['status'] ?? '');

            // The customer opened Tabby twice and both were authorised: release the one we don't keep
            if ($locked->is_paid && $locked->payment_token !== $tabbyId) {
                if ($status === 'AUTHORIZED') {
                    self::http($config)->post('/api/v2/payments/'.$tabbyId.'/close');
                    Log::warning('Tabby duplicate authorisation closed', ['payment_id' => $payment->id, 'tabby_payment_id' => $tabbyId]);
                } elseif ($status === 'CLOSED' && ! empty($tabbyPayment['captures'])) {
                    Log::error('Tabby payment captured twice, refund the duplicate from the Tabby dashboard', ['payment_id' => $payment->id, 'tabby_payment_id' => $tabbyId]);
                }

                return false;
            }

            if ($status === 'AUTHORIZED' && empty($tabbyPayment['captures'])) {
                $response = self::http($config)->post('/api/v2/payments/'.$tabbyId.'/captures', [
                    'amount' => self::money($payment->amount),
                    // Idempotency key: Tabby ignores a repeat of the same capture
                    'reference_id' => 'capture-'.$payment->id,
                ]);

                if (! $response->successful()) {
                    Log::warning('Tabby capture failed', ['payment_id' => $payment->id, 'http_status' => $response->status(), 'error' => $response->json('error')]);

                    return false;
                }
                $tabbyPayment = $response->json();
                $status = strtoupper($tabbyPayment['status'] ?? '');
            }

            $captured = collect($tabbyPayment['captures'] ?? [])->sum(fn ($capture) => (float) ($capture['amount'] ?? 0));
            $fullyCaptured = $status === 'CLOSED' && round($captured, 2) >= round((float) $payment->amount, 2);

            if (! $fullyCaptured) {
                return false;
            }

            if ($locked->payment_token !== $tabbyId) {
                $locked->update(['payment_token' => $tabbyId]);
                $payment->payment_token = $tabbyId;
            }

            if ($markPaid && ! $locked->is_paid) {
                $payment->markPaid();
                Log::info('Tabby payment captured', ['payment_id' => $payment->id, 'tabby_payment_id' => $tabbyId]);
            }

            return true;
        });
    }

    /**
     * Whether a Tabby payment was created for this payment, for its exact amount and currency
     */
    private static function belongsTo(array $tabbyPayment, Payment $payment, object $config): bool
    {
        return (string) ($tabbyPayment['order']['reference_id'] ?? '') === (string) $payment->id
            && strtoupper($tabbyPayment['currency'] ?? '') === self::currency($config)
            && isset($tabbyPayment['amount'])
            && (int) round((float) $tabbyPayment['amount'] * 100) === (int) round((float) $payment->amount * 100);
    }

    private static function retrieve(object $config, string $tabbyId): array
    {
        $response = self::http($config)->get('/api/v2/payments/'.$tabbyId);
        if (! $response->successful() || ($response->json('id') !== $tabbyId)) {
            throw new \RuntimeException('Tabby payment lookup failed ('.$response->status().')');
        }

        return $response->json();
    }

    /**
     * Order block of the checkout session: the items of all orders in this payment, or the plan for a subscription
     */
    private static function orderDetails(Payment $payment, Collection $orders, ?array $info): array
    {
        $items = [];
        foreach ($orders as $order) {
            foreach ($order->products as $product) {
                $items[] = [
                    'reference_id' => (string) $product->id,
                    'title' => mb_substr((string) ($product->name ?: 'Product'), 0, 255),
                    'quantity' => max(1, (int) $product->pivot->quantity),
                    'unit_price' => self::money($product->pivot->price),
                    'category' => mb_substr((string) ($product->categories->first()?->name ?: 'General'), 0, 255),
                ];
            }
        }

        if (! $items) {
            $items[] = [
                'reference_id' => 'payment-'.$payment->id,
                'title' => mb_substr($info['description'] ?? 'Subscription', 0, 255),
                'quantity' => 1,
                'unit_price' => self::money($payment->amount),
                'category' => 'Subscription',
            ];
        }

        return [
            'reference_id' => (string) $payment->id,
            'items' => $items,
            'tax_amount' => self::money($orders->sum('tax_amount')),
            'shipping_amount' => self::money($orders->sum('delivery_charge')),
            'discount_amount' => self::money($orders->sum('coupon_discount')),
        ];
    }

    private static function shippingAddress(?Order $order, ?ShopSubscription $subscription): array
    {
        $address = $order?->address;
        if ($address) {
            $line = collect([$address->address_line, $address->address_line2, $address->neighborhood, $address->road_no, $address->house_no])
                ->filter()->implode(', ');

            return [
                'city' => $address->area ?: 'Riyadh',
                'address' => $line !== '' ? mb_substr($line, 0, 255) : ($address->area ?: 'N/A'),
                'zip' => $address->post_code ?: '00000',
            ];
        }

        // Pickup orders and subscriptions have no delivery address
        return [
            'city' => 'Riyadh',
            'address' => mb_substr($subscription?->shop?->address ?: 'N/A', 0, 255),
            'zip' => '00000',
        ];
    }

    private static function buyerHistory($user): array
    {
        $loyalty = $user?->customer
            ? Order::where('customer_id', $user->customer->id)->where('order_status', OrderStatus::DELIVERED->value)->count()
            : 0;

        return [
            'registered_since' => ($user?->created_at ?? now())->toIso8601String(),
            'loyalty_level' => $loyalty,
            'is_email_verified' => (bool) $user?->email_verified_at,
            'is_phone_number_verified' => (bool) $user?->phone_verified_at,
        ];
    }

    /**
     * The customer's last 10 orders before this one; Tabby approves more customers with a history
     */
    private static function orderHistory(Collection $orders, array $buyer): array
    {
        $customerId = $orders->first()?->customer_id;
        if (! $customerId) {
            return [];
        }

        return Order::where('customer_id', $customerId)
            ->whereNotIn('id', $orders->pluck('id'))
            ->latest('id')->limit(10)->get()
            ->map(fn (Order $order) => [
                'purchased_at' => $order->created_at->toIso8601String(),
                'amount' => self::money($order->payable_amount),
                'status' => match ($order->order_status) {
                    OrderStatus::DELIVERED => 'complete',
                    OrderStatus::CANCELLED => 'canceled',
                    OrderStatus::PENDING => 'new',
                    default => 'processing',
                },
                'payment_method' => OrderStockService::defersStock($order->payment_method) ? 'card' : 'cod',
                'buyer' => $buyer,
            ])->all();
    }

    /**
     * Customer-facing message for a Tabby rejection reason
     */
    private static function rejectionMessage(?string $reason, bool $requestSucceeded): string
    {
        return match ($reason) {
            'order_amount_too_high' => 'This purchase is above your current spending limit with Tabby, try a smaller cart or use another payment method.',
            'order_amount_too_low' => 'The purchase amount is below the minimum amount required to use Tabby, try adding more items or use another payment method.',
            'not_available' => 'Sorry, Tabby is unable to approve this purchase. Please use an alternative payment method for your order.',
            default => $requestSucceeded
                ? 'Sorry, Tabby is unable to approve this purchase. Please use an alternative payment method for your order.'
                : 'Tabby could not create the payment. Please check the gateway settings.',
        };
    }

    /**
     * Value of the webhook header, derived from the app key so no extra setting is needed.
     * Re-run `php artisan tabby:register-webhook` after changing APP_KEY or the merchant code.
     */
    public static function webhookSecret(object $config): string
    {
        return hash_hmac('sha256', 'tabby-webhook|'.($config->merchant_code ?? ''), (string) config('app.key'));
    }

    public static function gatewayConfig(): object
    {
        $gateway = PaymentGateway::where('alias', 'Tabby')->first();

        return json_decode($gateway?->config ?? '{}') ?: (object) [];
    }

    /**
     * Tabby API client. Saudi merchants (SAR) use api.tabby.sa, UAE merchants (AED) api.tabby.ai.
     * Test or live is decided by the key itself (sk_test_ or sk_).
     */
    public static function http(object $config): PendingRequest
    {
        $baseUrl = self::currency($config) === 'AED' ? 'https://api.tabby.ai' : 'https://api.tabby.sa';

        return Http::baseUrl($baseUrl)->withToken((string) ($config->secret_key ?? ''))
            ->acceptJson()->asJson()->timeout(30);
    }

    /**
     * Payment currency: the gateway config `currency`, defaulting to SAR
     */
    private static function currency(object $config): string
    {
        return strtoupper($config->currency ?? 'SAR');
    }

    private static function money($amount): string
    {
        return number_format(round((float) $amount, 2), 2, '.', '');
    }
}
