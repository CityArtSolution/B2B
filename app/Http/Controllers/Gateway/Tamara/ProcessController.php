<?php

namespace App\Http\Controllers\Gateway\Tamara;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\ShopSubscription;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Tamara (buy now, pay later) checkout.
 *
 * Flow: the customer's eligibility is checked, a checkout session is created and the customer is sent
 * to Tamara. When Tamara approves the order, either the customer's redirect (callback) or Tamara's
 * webhook reads the order back from the Tamara API, authorises it, captures it, and only then marks the
 * payment and its orders as paid. Neither the redirect nor the webhook body is trusted on its own.
 *
 * Settings (Admin > Payment Gateways > TAMARA): api_token, webhook_token (the Notification Token)
 * and public_key (for the widgets). The gateway Mode picks sandbox (test) or production (live).
 */
class ProcessController extends Controller
{
    private const UUID = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    /**
     * Process to Tamara Payment
     *
     * @return string   // redirect url, or a JSON {"error": "..."} string
     */
    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        $config = self::config($paymentGateway);

        if (empty($config->api_token)) {
            return json_encode(['error' => 'Tamara API token is not configured']);
        }

        $orders = $payment->orders()->with(['products.categories', 'address', 'customer.user'])->get();
        $subscription = $orders->isEmpty() ? ShopSubscription::with('shop.user')->where('payment_id', $payment->id)->first() : null;
        $user = $orders->first()?->customer?->user ?? $subscription?->shop?->user;

        $email = $info['email'] ?? $user?->email;
        $phone = customerPhone($info['phone'] ?? $user?->phone, $info['phone_code'] ?? $user?->phone_code);
        if (! $phone) {
            Log::warning('Tamara checkout skipped: customer has no valid phone', ['payment_id' => $payment->id]);

            return json_encode(['error' => 'Please add a valid mobile number to your account before paying with Tamara.']);
        }
        $phoneNumber = '+'.$phone['country_code'].$phone['number'];
        $email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;

        try {
            // Tamara asks to check the customer before every checkout session
            if (! self::isEligible($config, (float) $payment->amount, $phoneNumber, $email)) {
                return json_encode(['error' => 'Sorry, Tamara is not available for this order. Please use another payment method.']);
            }

            $currency = $config->currency;
            $firstName = trim((string) ($info['name'] ?? $user?->name ?? '')) ?: 'Customer';
            $lastName = trim((string) ($info['last_name'] ?? $user?->last_name ?? ''));
            // Tamara needs both names; a full name typed in one field is split on its first space
            if ($lastName === '') {
                [$firstName, $lastName] = array_pad(preg_split('/\s+/u', $firstName, 2), 2, $firstName);
            }

            $payload = [
                'order_reference_id' => (string) $payment->id,
                'order_number' => (string) $payment->id,
                'total_amount' => self::amount($payment->amount, $currency),
                'shipping_amount' => self::amount($orders->sum('delivery_charge'), $currency),
                'tax_amount' => self::amount($orders->sum('tax_amount'), $currency),
                'discount' => [
                    'name' => 'Discount',
                    'amount' => self::amount($orders->sum('coupon_discount'), $currency),
                ],
                'description' => mb_substr($info['description'] ?? 'COQUI Store order #'.$payment->id, 0, 256),
                'country_code' => $config->country,
                'payment_type' => 'PAY_BY_INSTALMENTS',
                'locale' => app()->getLocale() === 'en' ? 'en_US' : 'ar_SA',
                'items' => self::items($payment, $orders, $currency, $info),
                'consumer' => array_filter([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone_number' => $phoneNumber,
                    'email' => $email,
                ]),
                'shipping_address' => self::shippingAddress($orders->first(), $subscription, $firstName, $lastName, $phoneNumber, $config->country),
                'risk_assessment' => self::riskAssessment($user, $orders),
                // Tamara adds ?orderId=&paymentStatus= to these; the callback reads the order back from Tamara
                'merchant_url' => [
                    'success' => route('tamara.payment.callback', $payment->id),
                    'failure' => route('tamara.payment.callback', $payment->id),
                    'cancel' => route('tamara.payment.callback', $payment->id),
                    'notification' => route('tamara.webhook'),
                ],
                'platform' => 'COQUI',
                'is_mobile' => false,
            ];

            $response = self::http($config)->post('/checkout', $payload);
            $data = $response->json() ?? [];

            if ($response->successful() && ! empty($data['checkout_url']) && ! empty($data['order_id'])) {
                // The callback and webhook only accept the Tamara order created for this payment
                $payment->update(['payment_token' => $data['order_id']]);

                return $data['checkout_url'];
            }

            Log::warning('Tamara checkout failed', [
                'payment_id' => $payment->id,
                'http_status' => $response->status(),
                'message' => $data['message'] ?? null,
                'errors' => $data['errors'] ?? null,
            ]);

            return json_encode(['error' => 'Tamara could not create the payment. Please use another payment method.']);
        } catch (\Throwable $th) {
            Log::error('Tamara request failed', ['payment_id' => $payment->id, 'exception' => get_class($th)]);

            return json_encode(['error' => 'Unable to connect to Tamara. Please try again.']);
        }
    }

    /**
     * Whether Tamara is offered to the signed-in customer for this amount; the checkout page hides
     * Tamara when it is not. Errors count as eligible, the checkout itself checks again.
     */
    public function eligibility(Request $request)
    {
        $amount = (float) $request->query('amount', 0);
        $gateway = PaymentGateway::where('alias', 'Tamara')->where('is_active', true)->first();
        $user = $request->user();

        if (! $gateway || $amount <= 0) {
            return $this->json('Tamara eligibility', ['eligible' => (bool) $gateway]);
        }

        $config = self::config($gateway);
        $phone = customerPhone($user?->phone, $user?->phone_code);
        if (empty($config->api_token) || ! $phone) {
            return $this->json('Tamara eligibility', ['eligible' => ! empty($config->api_token)]);
        }

        try {
            $email = filter_var($user?->email, FILTER_VALIDATE_EMAIL) ? $user->email : null;
            $eligible = self::isEligible($config, $amount, '+'.$phone['country_code'].$phone['number'], $email);
        } catch (\Throwable $th) {
            Log::warning('Tamara eligibility check failed', ['exception' => get_class($th)]);
            $eligible = true;
        }

        return $this->json('Tamara eligibility', ['eligible' => $eligible]);
    }

    /**
     * Customer comes back from Tamara (success, failure or cancel).
     * The order status is read from Tamara, never taken from the URL.
     */
    public function callback(Request $request, Payment $payment)
    {
        $tamaraId = $payment->payment_token;
        if (! is_string($tamaraId) || ! preg_match(self::UUID, $tamaraId)) {
            abort(400, 'Invalid Tamara payment reference');
        }

        $isSubscription = ShopSubscription::where('payment_id', $payment->id)->exists();
        $config = self::config();

        try {
            $paid = self::settle($config, $payment, $tamaraId, ! $isSubscription);
        } catch (\Throwable $exception) {
            Log::warning('Tamara payment verification failed', ['payment_id' => $payment->id, 'exception' => get_class($exception), 'message' => $exception->getMessage()]);
            $paid = false;
        }

        // Subscription payments are activated by SubscriptionController once the order is captured
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

        return to_route('order.payment.cancel', ['payment' => $payment, 'error' => match ($request->query('paymentStatus')) {
            'declined' => 'Sorry, Tamara is unable to approve this purchase. Please use an alternative payment method for your order.',
            'canceled' => 'Payment with Tamara was cancelled.',
            default => 'Payment has not been completed.',
        }]);
    }

    /**
     * Tamara server notification (order_approved, order_authorised, order_captured, order_declined, ...).
     * The tamaraToken (a JWT signed with the Notification Token) proves it comes from Tamara.
     * Returns 200 once handled or deliberately ignored; any other status makes Tamara retry.
     */
    public function webhook(Request $request)
    {
        $config = self::config();
        $token = $request->query('tamaraToken') ?: $request->bearerToken();

        if (! self::validToken((string) $token, (string) ($config->webhook_token ?? ''))) {
            Log::warning('Tamara webhook rejected: invalid tamaraToken', ['ip' => $request->ip()]);

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $tamaraId = $request->input('order_id');
        $payment = Payment::find($request->input('order_reference_id'));
        if (! is_string($tamaraId) || ! preg_match(self::UUID, $tamaraId) || ! $payment
            || strcasecmp((string) $payment->payment_method, 'tamara') !== 0) {
            Log::info('Tamara webhook for an unknown order', ['order_id' => $tamaraId, 'event' => $request->input('event_type')]);

            return response()->json(['received' => true]);
        }

        // A subscription is activated when the shop comes back to the site (see callback), which also
        // captures the order; an abandoned subscription order is left to expire uncaptured
        if (ShopSubscription::where('payment_id', $payment->id)->exists()) {
            return response()->json(['received' => true]);
        }

        try {
            self::settle($config, $payment, $tamaraId, true);
        } catch (\Throwable $exception) {
            Log::warning('Tamara webhook: order processing failed', ['payment_id' => $payment->id, 'exception' => get_class($exception), 'message' => $exception->getMessage()]);

            return response()->json(['message' => 'Processing failed'], 503);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Refund part or all of a captured Tamara order.
     * Throws a RuntimeException with a message for the admin when Tamara refuses it.
     *
     * @return array  Tamara's refund response (status, refunded_amount, ...)
     */
    public static function refund(Payment $payment, float $amount, ?string $reason = null): array
    {
        $config = self::config();
        $tamaraId = (string) $payment->payment_token;

        if (! $payment->is_paid || ! preg_match(self::UUID, $tamaraId)) {
            throw new \RuntimeException(__('This order has no captured Tamara payment to refund.'));
        }

        $order = self::retrieve($config, $tamaraId);
        $captured = (float) ($order['captured_amount']['amount'] ?? 0);
        $refunded = (float) ($order['refunded_amount']['amount'] ?? 0);
        if (round($refunded + $amount, 2) > round($captured, 2)) {
            throw new \RuntimeException(__('Refund is more than the amount left on this Tamara order (:left).', [
                'left' => number_format($captured - $refunded, 2, '.', ''),
            ]));
        }

        $response = self::http($config)->post('/payments/simplified-refund/'.$tamaraId, [
            'total_amount' => self::amount($amount, $config->currency),
            'comment' => filled($reason) ? mb_substr($reason, 0, 255) : 'Refund for order #'.$payment->id,
            // Numbered from the amount Tamara already refunded, to tell refunds apart in the Tamara portal
            'merchant_refund_id' => 'refund-'.$payment->id.'-'.number_format($refunded, 2, '', ''),
        ]);

        if (! $response->successful()) {
            Log::warning('Tamara refund failed', ['payment_id' => $payment->id, 'http_status' => $response->status(), 'message' => $response->json('message')]);

            throw new \RuntimeException(__('Tamara refused the refund: :error', ['error' => $response->json('message') ?? $response->status()]));
        }

        Log::info('Tamara refund sent', ['payment_id' => $payment->id, 'amount' => $amount]);

        return $response->json() + ['refunded_total' => $refunded + $amount];
    }

    /**
     * Move a Tamara order forward (approved -> authorised -> fully captured) and mark our payment paid
     * once Tamara holds the full amount. Runs under a row lock, so the redirect and the webhook cannot
     * authorise, capture or mark it twice.
     *
     * @return bool  whether the Tamara order is fully captured
     */
    private static function settle(object $config, Payment $payment, string $tamaraId, bool $markPaid): bool
    {
        return DB::transaction(function () use ($config, $payment, $tamaraId, $markPaid) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();

            // An older Tamara order of a payment that was already paid through another one
            if ($locked->is_paid && $locked->payment_token !== $tamaraId) {
                return false;
            }

            $order = self::retrieve($config, $tamaraId);
            if (! self::belongsTo($order, $payment, $config)) {
                Log::warning('Tamara order does not match the payment', ['payment_id' => $payment->id, 'order_id' => $tamaraId]);

                return false;
            }

            $status = strtolower((string) ($order['status'] ?? ''));

            if ($status === 'approved') {
                $response = self::http($config)->post('/orders/'.$tamaraId.'/authorise');
                if (! $response->successful()) {
                    throw new \RuntimeException('Tamara authorise failed ('.$response->status().')');
                }
                $status = ($response->json('auto_captured') === true) ? 'fully_captured' : strtolower((string) $response->json('status'));
            }

            if ($status === 'authorised') {
                $response = self::http($config)->post('/payments/capture', [
                    'order_id' => $tamaraId,
                    'total_amount' => self::amount($payment->amount, $config->currency),
                    'shipping_info' => [
                        'shipped_at' => now()->toIso8601String(),
                        'shipping_company' => 'COQUI',
                    ],
                ]);
                if (! $response->successful()) {
                    throw new \RuntimeException('Tamara capture failed ('.$response->status().')');
                }
                $status = strtolower((string) $response->json('status'));
            }

            if ($status !== 'fully_captured') {
                return false;
            }

            if ($locked->payment_token !== $tamaraId) {
                $locked->update(['payment_token' => $tamaraId]);
                $payment->payment_token = $tamaraId;
            }

            if ($markPaid && ! $locked->is_paid) {
                $payment->markPaid();
                Log::info('Tamara order captured', ['payment_id' => $payment->id, 'order_id' => $tamaraId]);
            }

            return true;
        });
    }

    private static function isEligible(object $config, float $amount, string $phoneNumber, ?string $email): bool
    {
        $response = self::http($config)->post('/pre-checkout/v1/eligibility', [
            'order' => ['amount' => round($amount, 2), 'currency' => $config->currency],
            'customer' => array_filter(['phone_number' => ltrim($phoneNumber, '+'), 'email' => $email]),
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Tamara eligibility check failed ('.$response->status().')');
        }

        return (bool) $response->json('is_eligible');
    }

    /**
     * Whether a Tamara order was created for this payment, for its exact amount and currency
     */
    private static function belongsTo(array $order, Payment $payment, object $config): bool
    {
        return (string) ($order['order_reference_id'] ?? '') === (string) $payment->id
            && strtoupper($order['total_amount']['currency'] ?? '') === $config->currency
            && isset($order['total_amount']['amount'])
            && (int) round((float) $order['total_amount']['amount'] * 100) === (int) round((float) $payment->amount * 100);
    }

    private static function retrieve(object $config, string $tamaraId): array
    {
        $response = self::http($config)->get('/orders/'.$tamaraId);
        if (! $response->successful() || $response->json('order_id') !== $tamaraId) {
            throw new \RuntimeException('Tamara order lookup failed ('.$response->status().')');
        }

        return $response->json();
    }

    /**
     * Verify a tamaraToken: a JWT signed with HS256 using the Notification Token
     */
    private static function validToken(string $token, string $secret): bool
    {
        $parts = explode('.', $token);
        if ($secret === '' || count($parts) !== 3) {
            return false;
        }

        [$header, $body, $signature] = $parts;
        $decode = fn (string $part) => base64_decode(strtr($part, '-_', '+/').str_repeat('=', (4 - strlen($part) % 4) % 4));

        if ((json_decode($decode($header), true)['alg'] ?? null) !== 'HS256') {
            return false;
        }

        $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', $header.'.'.$body, $secret, true)), '+/', '-_'), '=');
        if (! hash_equals($expected, $signature)) {
            return false;
        }

        $claims = json_decode($decode($body), true) ?? [];

        return ! isset($claims['exp']) || $claims['exp'] >= time() - 60;
    }

    /**
     * Items of all orders in this payment, or the plan for a subscription
     */
    private static function items(Payment $payment, Collection $orders, string $currency, ?array $info): array
    {
        $items = [];
        foreach ($orders as $order) {
            foreach ($order->products as $product) {
                $quantity = max(1, (int) $product->pivot->quantity);
                $items[] = [
                    'reference_id' => (string) $product->id,
                    'type' => 'Physical',
                    'name' => mb_substr((string) ($product->name ?: 'Product'), 0, 255),
                    'sku' => (string) ($product->code ?? $product->sku ?? $product->id),
                    'quantity' => $quantity,
                    'unit_price' => self::amount($product->pivot->price, $currency),
                    'total_amount' => self::amount($product->pivot->price * $quantity, $currency),
                    'item_url' => url('/products/'.$product->id.'/details'),
                ];
            }
        }

        if (! $items) {
            $items[] = [
                'reference_id' => 'payment-'.$payment->id,
                'type' => 'Digital',
                'name' => mb_substr($info['description'] ?? 'Subscription', 0, 255),
                'sku' => 'subscription',
                'quantity' => 1,
                'unit_price' => self::amount($payment->amount, $currency),
                'total_amount' => self::amount($payment->amount, $currency),
            ];
        }

        return $items;
    }

    private static function shippingAddress(?Order $order, ?ShopSubscription $subscription, string $firstName, string $lastName, string $phone, string $country): array
    {
        $address = $order?->address;
        $line = $address
            ? collect([$address->address_line, $address->address_line2, $address->neighborhood, $address->road_no, $address->house_no])->filter()->implode(', ')
            : (string) ($subscription?->shop?->address ?? '');

        return array_filter([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'line1' => mb_substr($line !== '' ? $line : ($address?->area ?: 'N/A'), 0, 255),
            // Pickup orders and subscriptions have no delivery address
            'city' => $address?->area ?: 'Riyadh',
            'postal_code' => $address?->post_code,
            'country_code' => $country,
            'phone_number' => $phone,
        ]);
    }

    /**
     * Customer history; Tamara approves more customers with it
     */
    private static function riskAssessment($user, Collection $orders): array
    {
        $customerId = $orders->first()?->customer_id ?? $user?->customer?->id;
        $paid = $customerId
            ? Order::where('customer_id', $customerId)->whereNotIn('id', $orders->pluck('id'))
                ->where('payment_status', 'Paid')->where('order_status', '!=', OrderStatus::CANCELLED->value)
                ->selectRaw('count(*) as total, min(created_at) as first_paid, max(created_at) as last_paid')->first()
            : null;

        return [
            'account_creation_date' => ($user?->created_at ?? now())->format('d-m-Y'),
            'total_order_count' => (int) ($paid?->total ?? 0),
            'date_first_paid' => $paid?->first_paid ? date('d-m-Y', strtotime($paid->first_paid)) : null,
            'date_last_paid' => $paid?->last_paid ? date('d-m-Y', strtotime($paid->last_paid)) : null,
            'is_premium_customer' => false,
            'is_existing_customer' => (int) ($paid?->total ?? 0) > 0,
            'phone_verified' => (bool) $user?->phone_verified_at,
            'email_verified' => (bool) $user?->email_verified_at,
        ];
    }

    /**
     * Gateway settings, with the old .env values as a fallback.
     * Mode "live" uses the production API, anything else the sandbox.
     */
    public static function config(?PaymentGateway $gateway = null): object
    {
        $gateway ??= PaymentGateway::where('alias', 'Tamara')->first();
        $config = json_decode($gateway?->config ?? '{}') ?: (object) [];

        $config->api_token = ($config->api_token ?? null) ?: env('TAMARA_SECRET_KEY');
        $config->webhook_token = ($config->webhook_token ?? null) ?: env('TAMARA_NOTIFICATION_TOKEN');
        $config->public_key = ($config->public_key ?? null) ?: env('TAMARA_PUBLIC_KEY');
        $config->currency = strtoupper($config->currency ?? 'SAR');
        $config->country = ['AED' => 'AE', 'KWD' => 'KW', 'BHD' => 'BH', 'OMR' => 'OM'][$config->currency] ?? 'SA';
        $config->live = $gateway?->mode === 'live';

        return $config;
    }

    public static function http(object $config): PendingRequest
    {
        return Http::baseUrl($config->live ? 'https://api.tamara.co' : 'https://api-sandbox.tamara.co')
            ->withToken((string) $config->api_token)->acceptJson()->asJson()->timeout(30);
    }

    private static function amount($amount, string $currency): array
    {
        return ['amount' => round((float) $amount, 2), 'currency' => $currency];
    }
}
