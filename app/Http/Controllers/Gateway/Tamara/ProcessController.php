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
        $config = json_decode($paymentGateway->config);
        
        $apiToken      = $config->api_token;
        $baseUrl       = 'https://api-sandbox.tamara.co';

        if (!$baseUrl || !$apiToken) {
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
                "currency" => "SAR"
            ],
            "shipping_amount" => [
                "amount"   => "0.00",
                "currency" => "SAR"
            ],
            "tax_amount" => [
                "amount"   => "0.00",
                "currency" => "SAR"
            ],
        
            "order_reference_id" => (string) $payment->id,
            "risk_assessment" => [
                "is_premium_customer"   => false,
                "account_creation_date"    => '31-01-2019',
                "total_order_count" => 12,
                "risk_score"        => 5
            ],
            "consumer" => [
                "first_name"   => $customer->name ?? 'Customer',
                "last_name"    => $customer->name ?? 'Customer',
                "phone_number" => $customer->phone ?? '0500000000',
            ],
        
            "items" => [
                [
                    "reference_id" => "item_{$payment->id}",
                    "type" => "Physical",
                    "name" => "Order #{$payment->id}",
                    "sku"  => "SKU-{$payment->id}",
                    "quantity" => 1,
                    "total_amount" => [
                        "amount"   => number_format($payment->amount, 2, '.', ''),
                        "currency" => "SAR"
                    ]
                ]
            ],
            
            "country_code"       => "SA",
            "description"        => "Invoice #{$payment->id}",
            "merchant_urls" => [
                "success" => $successUrl,
                "cancel"  => $cancelUrl,
                "failure" => $cancelUrl
            ],
            "locale"             => app()->getLocale() === 'ar' ? 'ar_SA' : 'en_US',
            "platform" => "COQUI",
            "is_mobile" => false
        ];
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type' => 'application/json',
            ])->post($baseUrl, $payload);


            if (!$response->successful()) {
                return json_encode(['error' => 'integration not successful']);
            }

            $data = $response->json();

            if (isset($data['checkout_url'])) {
                return $data['checkout_url'];
            }

            // Debug Tamara error
            throw new \Exception(json_encode($data));

        } catch (\Throwable $e) {
            return json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }
}
