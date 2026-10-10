<?php

namespace App\Http\Controllers\Gateway\Tap;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
        
        $paymentToken = Str::uuid()->toString();
        $payment->update(['payment_token' => $paymentToken]);

        $successUrl = $cancelUrl = null;

        if ($info && $info['type'] == 'subscription') {
            $successUrl = route('subscription.payment.success', ['payment' => $payment, 'token' => $paymentToken]);
            $cancelUrl = route('subscription.payment.cancel', ['payment' => $payment, 'token' => $paymentToken]);
        } else {
            $successUrl = route('tap.payment.execute', $payment->id);
            $cancelUrl = route('payment.cancel', $payment->id);
        }


        try {

            $user = $payment->orders->first()?->customer?->user;
            $customer = ['first_name' => $info['name'] ?? $user?->name ?? 'Customer'];
            $email = $info['email'] ?? $user?->email;
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $customer['email'] = $email;
            }
            $phone = preg_replace('/\D/', '', $info['phone'] ?? $user?->phone ?? '');
            if (str_starts_with($phone, '00966')) {
                $phone = substr($phone, 5);
            } elseif (str_starts_with($phone, '966')) {
                $phone = substr($phone, 3);
            }
            $phone = ltrim($phone, '0');
            if (preg_match('/^5[0-9]{8}$/', $phone)) {
                $customer['phone'] = ['country_code' => '966', 'number' => $phone];
            }

            $payload = [
                "amount" => floatval($payment->amount),
                "currency" => $payment->currency ?: "SAR",
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
                ]
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

            if (isset($data['id']) && ! ($info && $info['type'] == 'subscription')) {
                $payment->update(['payment_token' => $data['id']]);
            }

            return $data['transaction']['url'];

        } catch (\Throwable $th) {
            Log::error('Tap request failed', ['payment_id' => $payment->id, 'exception' => get_class($th)]);
            return json_encode(['error' => 'Unable to connect to Tap. Please try again.']);
        }
    }
    public function callback(Request $request, Payment $payment)
    {
        $chargeId = $request->input('tap_id', $request->input('id'));
        if (! is_string($chargeId) || ! str_starts_with($chargeId, 'chg_') ||
            ! hash_equals((string) $payment->payment_token, $chargeId)) {
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

        $valid = ($charge['id'] ?? null) === $chargeId
            && strtoupper($charge['currency'] ?? '') === strtoupper($payment->currency ?: 'SAR')
            && isset($charge['amount'])
            && (int) round($charge['amount'] * 100) === (int) round($payment->amount * 100);
        if (! $valid) {
            abort(400, 'Tap payment details do not match');
        }

        if (($charge['status'] ?? null) === 'CAPTURED') {
            $payment->orders()->update(['payment_status' => PaymentStatus::PAID->value]);
            $payment->update(['is_paid' => true]);
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
}
