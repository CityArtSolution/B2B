<?php

namespace App\Http\Controllers\Gateway\Tabby;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ProcessController extends Controller
{
    /**
     * Process to Tabby Payment
     *
     * @return string
     */
    public static function process($paymentGateway, Payment $payment, ?array $info = null)
    {
        $config = json_decode($paymentGateway->config);
        
        $secretKey = $config->secret_key;
        $merchantCode = $config->merchant_code;
        $baseUrl      = 'https://api.tabby.ai/api/v2/checkout';

        if (!$secretKey || !$merchantCode) {
            throw new \Exception('Tabby configuration error');
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


        $payload = [
            "merchant_code" => $merchantCode,
            "payment" => [
                "amount"      => $payment->amount,  
                "currency"    => "SAR",
                "description" => "Invoice #{$payment->id}",
                "buyer" => [
                    "name"  => $payment->orders[0]->customer?->user?->name ?? 'Customer',
                    "phone" => $payment->orders[0]->customer?->user?->phone ?? '0000000000',
                ],
            ],
            "order" => [
                "reference_id" => $payment->id,
            ],
            "lang" => app()->getLocale() ?? 'en',
            "merchant_urls" => [
                "success" => $successUrl,
                "cancel"  => $cancelUrl,
                "failure" => $cancelUrl,
            ],
        ];
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
                'Content-Type' => 'application/json',
            ])->post($baseUrl, $payload);
            

            $data = $response->json();

            if (isset($data['id']) && isset($data['configuration']['available_products'])) {
                return $data['configuration']['available_products']['installments'][0]['web_url'] ?? null;
            } else {
                dd($data);
            }
        } catch (\Throwable $th) {
            return json_encode(['error' => $th->getMessage()]);
        }
    }
}
