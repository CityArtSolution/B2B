<?php

namespace App\Http\Controllers\Gateway\Tap;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ProcessController extends Controller
{
    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        $config = json_decode($paymentGateway->config);

        // توليد payment token
        $paymentToken = Str::uuid()->toString();
        $payment->update([
            'payment_token' => $paymentToken
        ]);

        // بيانات العميل و callback URL
        $callbackUrl = route('tap.payment.execute', ['payment' => $payment->id, 'token' => $paymentToken]);

        // إعداد body للـ API request
        $requestBody = [
            'amount' => $payment->amount,
            'currency' => 'SAR',
            'customer' => [
                'name' => $info['name'] ?? 'Example Name',
                'email' => $info['email'] ?? 'example@gmail.com',
                'phone' => $info['phone'] ?? '01000000000',
            ],
            'callback' => $callbackUrl,
        ];

        // إرسال request للبوابة
        $endpoint = $paymentGateway->mode === 'live'
            ? 'https://api.tap.company/v2/charges'
            : 'https://sandbox.tap.company/v2/charges';

        $headers = [
            'Authorization' => 'Bearer ' . $config->secret_key,
            'Content-Type' => 'application/json',
        ];

        $response = Http::withHeaders($headers)->post($endpoint, $requestBody)->json();

        if (isset($response['url'])) {
            return $response['url']; // رابط الدفع النهائي
        }

        return json_encode(['error' => $response]);
    }
}
