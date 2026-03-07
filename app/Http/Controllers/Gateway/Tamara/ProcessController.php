<?php

namespace App\Http\Controllers\Gateway\Tamara;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ProcessController extends Controller
{
    /**
     * Process to Tamara Payment
     *
     * @return string
     */
    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        $secretKey = env('TAMARA_SECRET_KEY');
        $baseUrl   = env('TAMARA_BASE_URL', 'https://api-sandbox.tamara.co');
        $currency  = 'SAR';

        if (!$baseUrl || !$secretKey) {
            throw new \Exception('Tamara configuration error');
        }
        
        $paymentToken = Str::uuid()->toString();
        $payment->update(['payment_token' => $paymentToken]);

        $successUrl = $cancelUrl = null;

        if ($info && $info['type'] == 'subscription') {
            $successUrl = route('subscription.payment.success', ['payment' => $payment, 'token' => $paymentToken]);
            $cancelUrl = route('subscription.payment.cancel', ['payment' => $payment, 'token' => $paymentToken]);
        } else {
            $successUrl = route('payment.success', $payment->id);
            $cancelUrl = route('payment.cancel', $payment->id);
        }

        $customer = $payment->orders[0]->customer?->user ?? null;

        $payload = [
            "total_amount" => [
                "amount"   => number_format($payment->amount, 2, '.', ''),
                "currency" => $currency
            ],
            "shipping_amount" => [
                "amount"   => "0.00",
                "currency" => $currency
            ],
            "tax_amount" => [
                'amount'   => 0,
                "currency" => $currency
            ],
        
            "order_reference_id" => (string) $payment->id,
            'order_number'       => (string) $payment->id,
            'description'        => "Invoice #{$payment->id}",
            'country_code'       => 'SA',
            'payment_type'       => 'PAY_BY_INSTALMENTS',
    
            "consumer" => [
                "first_name"   => $customer->name ?? 'Customer',
                "phone_number" => $customer->phone ?? '0500000000',
            ],
        
            "merchant_url" => [
                "success" => $successUrl,
                "cancel"  => $cancelUrl,
                "failure" => $cancelUrl
            ],
            "platform" => "COQUI",
            "is_mobile" => false
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $secretKey,
            'Content-Type' => 'application/json',
            'Accept'        => 'application/json',
        ])->post($baseUrl . '/checkout', $payload);


        if (!$response->successful()) {
            throw new \Exception('Tamara checkout failed: ' . $response->body());
        }
        
        $data = $response->json();

        if (isset($data['checkout_url'])) {
            return $data['checkout_url'];
        }

        // Debug Tamara error
        throw new \Exception(json_encode($data));
    }
}
