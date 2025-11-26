<?php

namespace App\Http\Controllers\Gateway\Tap;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ProcessController extends Controller
{
    /**
     * Process payment via Tap
     *
     * @return string
     */
    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        $config = json_decode($paymentGateway->config);

        // توليد payment token
        $paymentToken = Str::uuid()->toString();
        $payment->update(['payment_token' => $paymentToken]);

        // إعداد success و cancel URLs
        if ($info && $info['type'] === 'subscription') {
            $successUrl = route('subscription.payment.success', ['payment' => $payment, 'token' => $paymentToken]);
            $cancelUrl = route('subscription.payment.cancel', ['payment' => $payment, 'token' => $paymentToken]);
        } else {
            $successUrl = route('payment.success', $payment->id);
            $cancelUrl = route('payment.cancel', $payment->id);
        }

        // بيانات العميل
        $customerName = $info['name'] ?? $payment->orders[0]?->customer?->user?->name ?? 'Example Name';
        $customerEmail = $info['email'] ?? $payment->orders[0]?->customer?->user?->email ?? 'example@gmail.com';
        $customerPhone = $info['phone'] ?? $payment->orders[0]?->customer?->user?->phone ?? '01000000000';

        // إعداد body للـ API request
        $requestBody = [
            'amount' => $payment->amount,
            'currency' => $config->currency ?? 'SAR',
            'threeDSecure' => true,
            'save_card' => false,
            'description' => 'Payment for order #' . $payment->id,
            'statement_descriptor' => 'Payment',
            'customer' => [
                'first_name' => $customerName,
                'email' => $customerEmail,
                'phone' => [
                    'country_code' => '966',
                    'number' => $customerPhone
                ]
            ],
            'source' => [
                'type' => 'card'
            ],
            'redirect' => [
                'url' => route('tap.payment.execute', ['payment' => $payment->id, 'token' => $paymentToken])
            ]
        ];

        $endpoint = $paymentGateway->mode === 'live'
            ? 'https://api.tap.company/v2/charges'
            : 'https://sandbox.tap.company/v2/charges';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config->secret_key,
                'Content-Type' => 'application/json',
            ])->post($endpoint, $requestBody)->json();

            if (isset($response['url'])) {
                return $response['url']; // رابط الدفع النهائي
            }

            return json_encode(['error' => $response]);
        } catch (\Throwable $th) {
            return json_encode(['error' => $th->getMessage()]);
        }
    }

    /**
     * Callback after payment
     */
    public function callback(Request $request, Payment $payment)
    {
        $paymentGateway = PaymentGateway::where('name', 'tap')->first();
        $config = json_decode($paymentGateway->config);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config->secret_key,
                'Content-Type' => 'application/json',
            ])->get('https://api.tap.company/v2/charges/' . $request->query('tap_id'))->json();

            if (isset($response['status']) && $response['status'] === 'CAPTURED') {
                // Payment successful
                if ($request->info['type'] ?? null === 'subscription') {
                    return to_route('subscription.payment.success', ['payment' => $payment, 'token' => $payment->payment_token]);
                }
                return to_route('payment.success', ['payment' => $payment]);
            } else {
                $errorMessage = $response['response']['message'] ?? 'Payment failed';
                if ($request->info['type'] ?? null === 'subscription') {
                    return to_route('subscription.payment.cancel', ['payment' => $payment, 'token' => $payment->payment_token, 'error' => $errorMessage]);
                }
                return to_route('payment.cancel', ['payment' => $payment, 'error' => $errorMessage]);
            }
        } catch (\Throwable $th) {
            return to_route('payment.cancel', ['payment' => $payment, 'error' => $th->getMessage()]);
        }
    }
}
