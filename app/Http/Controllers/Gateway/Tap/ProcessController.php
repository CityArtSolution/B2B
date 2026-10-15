<?php

namespace App\Http\Controllers\Gateway\Tap;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\ShopSubscription;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class ProcessController extends Controller
{
    /**
     * Process to Tap
     *
     * @return string   // redirect url
     */
    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        $config = json_decode($paymentGateway->config);
            
            
        $baseUrl      = 'https://api.tap.company/v2/';
        $secretKey = $config->secret_key ?? null;
        
        if (!$secretKey || !$baseUrl) {
            return json_encode(['error' => 'Tap secret key is not configured']);
        }
        
        // Tap redirects here for both success and failure; the callback verifies the charge
        $successUrl = route('tap.payment.execute', $payment->id);


        try {

            $user = $payment->orders->first()?->customer?->user;
            $customer = ['first_name' => $info['name'] ?? $user?->name ?? 'Customer'];
            $email = $info['email'] ?? $user?->email;
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $customer['email'] = $email;
            }
            $phone = self::customerPhone($info['phone'] ?? $user?->phone, $info['phone_code'] ?? $user?->phone_code);
            if ($phone) {
                $customer['phone'] = $phone;
            }

            // Tap rejects a charge that has neither an email nor a phone number
            if (! isset($customer['email']) && ! isset($customer['phone'])) {
                Log::warning('Tap charge skipped: customer has no valid email or phone', ['payment_id' => $payment->id]);

                return json_encode(['error' => 'Please add a valid email or phone number to your account before paying with Tap.']);
            }

            $payload = [
                "amount" => round((float) $payment->amount, 2),
                "currency" => self::currency($config),
                "statement_descriptor" => "COQUI Store",
                "customer" => $customer,
                "source" => [
                    "id" => "src_all"
                ],
                "redirect" => [
                    "url" => $successUrl
                ],
                "post" => [
                    "url" => $successUrl
                ],
                // Ties the charge to this payment, so the callback can accept any charge made for it
                "reference" => [
                    "transaction" => 'pay_'.$payment->id,
                    "order" => (string) $payment->id,
                ],
                "metadata" => [
                    "payment_id" => (string) $payment->id,
                ],
            ];

            $response = Http::withToken($secretKey)->acceptJson()->timeout(30)
                ->post($baseUrl . "charges", $payload);

            if (!$response->successful()) {
                Log::warning('Tap charge creation failed', ['payment_id' => $payment->id, 'http_status' => $response->status(), 'errors' => $response->json('errors')]);
                return json_encode(['error' => $response->json('errors.0.description') ?? 'Tap could not create the payment. Please check the gateway settings.']);
            }

            $data = $response->json();

            if (empty($data['id']) || empty($data['transaction']['url'])) {
                Log::warning('Tap did not return a payment URL', ['payment_id' => $payment->id, 'status' => $data['status'] ?? null, 'response' => $data['response'] ?? null]);
                return json_encode(['error' => $data['response']['message'] ?? 'Tap did not return a payment URL']);
            }

            $payment->update(['payment_token' => $data['id']]);

            return $data['transaction']['url'];

        } catch (\Throwable $th) {
            Log::error('Tap request failed', ['payment_id' => $payment->id, 'exception' => get_class($th)]);
            return json_encode(['error' => 'Unable to connect to Tap. Please try again.']);
        }
    }
    public function callback(Request $request, Payment $payment)
    {
        $chargeId = $request->input('tap_id', $request->input('id'));
        if (! is_string($chargeId) || ! preg_match('/^chg_[A-Za-z0-9]+$/', $chargeId)) {
            abort(400, 'Invalid Tap payment reference');
        }

        $gateway = PaymentGateway::where('alias', 'Tap')->first();
        $config = json_decode($gateway?->config ?? '{}');
        try {
            $response = Http::withToken($config->secret_key ?? '')->acceptJson()->timeout(30)
                ->get('https://api.tap.company/v2/charges/'.rawurlencode($chargeId));
            if (! $response->successful()) {
                throw new \RuntimeException('Tap verification failed');
            }
            $charge = $response->json();
        } catch (\Throwable $exception) {
            Log::warning('Tap payment verification failed', ['payment_id' => $payment->id]);
            if ($request->isMethod('post')) {
                return response()->json(['message' => 'Verification unavailable'], 503);
            }
            return to_route('order.payment.cancel', ['payment' => $payment, 'error' => 'Payment verification is temporarily unavailable.']);
        }

        // The charge must belong to this payment: either the latest charge saved on it, or an earlier
        // one created for it (customer opened Tap twice) as recorded by Tap in the charge metadata
        $belongs = hash_equals((string) $payment->payment_token, $chargeId)
            || (string) ($charge['metadata']['payment_id'] ?? '') === (string) $payment->id;

        $valid = $belongs && ($charge['id'] ?? null) === $chargeId
            && strtoupper($charge['currency'] ?? '') === self::currency($config)
            && isset($charge['amount'])
            && (int) round($charge['amount'] * 100) === (int) round($payment->amount * 100);
        if (! $valid) {
            abort(400, 'Tap payment details do not match');
        }

        $captured = ($charge['status'] ?? null) === 'CAPTURED';

        // Keep the paid charge as the payment's reference (the subscription pages check it)
        if ($captured && $payment->payment_token !== $chargeId && ! $payment->is_paid) {
            $payment->update(['payment_token' => $chargeId]);
        }

        // Subscription payments are activated by SubscriptionController once the charge is verified
        if (ShopSubscription::where('payment_id', $payment->id)->exists()) {
            if ($request->isMethod('post')) {
                return response()->json(['received' => true]);
            }
            $params = ['payment' => $payment, 'token' => $payment->payment_token];
            if (! $captured) {
                return to_route('subscription.payment.cancel', $params);
            }

            // Signed so the success page cannot be reached by editing the cancel URL
            return redirect()->to(URL::temporarySignedRoute('subscription.payment.success', now()->addMinutes(30), $params));
        }

        if ($captured) {
            self::markPaid($payment);
            $payment->refresh();
        }

        if ($request->isMethod('post')) {
            return response()->json(['received' => true]);
        }
        if ($payment->is_paid) {
            return to_route('order.payment.success', $payment);
        }
        Log::warning('Tap payment not captured', ['payment_id' => $payment->id,
            'charge_id' => $chargeId, 'status' => $charge['status'] ?? null,
            'response' => $charge['response'] ?? null]);
        return to_route('order.payment.cancel', ['payment' => $payment,
            'error' => $charge['response']['message'] ?? 'Payment has not been completed.']);
    }

    /**
     * Mark a verified payment and its orders as paid, exactly once.
     * The browser redirect and Tap's server POST can arrive together, so the rows are locked.
     * Each order is saved as a model so the Order updated event takes its stock.
     */
    private static function markPaid(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->first();
            if (! $locked || $locked->is_paid) {
                return;
            }

            foreach ($locked->orders()->lockForUpdate()->get() as $order) {
                if ($order->payment_status !== PaymentStatus::PAID) {
                    $order->update(['payment_status' => PaymentStatus::PAID->value]);
                }
            }

            $locked->update(['is_paid' => true]);
        });
    }

    /**
     * Customer phone in Tap format, using the stored country code when there is one.
     * Saudi (05xxxxxxxx) and Egyptian (01xxxxxxxxx) local numbers are recognised without it.
     */
    private static function customerPhone(?string $phone, $phoneCode): ?array
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        $code = ltrim(preg_replace('/\D/', '', (string) $phoneCode), '0');

        if ($code === '') {
            if (preg_match('/^(966)?0?5[0-9]{8}$/', $digits)) {
                $code = '966';
            } elseif (preg_match('/^(20)?0?1[0-9]{9}$/', $digits)) {
                $code = '20';
            } else {
                $code = '966';
            }
        }

        if (str_starts_with($digits, $code) && strlen($digits) - strlen($code) >= 8) {
            $digits = substr($digits, strlen($code));
        }
        $number = ltrim($digits, '0');

        if (strlen($number) < 7 || strlen($number) > 12 || strlen($code) > 4) {
            return null;
        }

        return ['country_code' => $code, 'number' => $number];
    }

    /**
     * Charge currency: the gateway config `currency`, defaulting to SAR
     */
    private static function currency($config): string
    {
        return strtoupper($config->currency ?? 'SAR');
    }
}
