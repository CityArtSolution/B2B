<?php

namespace App\Http\Controllers\Gateway\Tap;

use App\Http\Controllers\Controller;
use App\Models\Payment;
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
        $secretKey = $config->secret_key;
        
        if (!$secretKey || !$baseUrl) {
            throw new \Exception('Tap configuration error');
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


        try {

            $payload = [
                "amount" => floatval($payment->amount),
                "currency" => "SAR",
                "statement_descriptor" => "COQUI Store",
                "customer" => [
                    "first_name" =>  $payment->orders[0]->customer?->user?->name ?? 'Customer',
                    "phone" => [
                        "country_code" => "966",
                        "number" =>  $payment->orders[0]->customer?->user?->phone ?? '0000000000',
                    ]
                ],
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

            $response = Http::withToken($secretKey)->acceptJson()
                ->post($baseUrl . "charges", $payload);

            if (!$response->successful()) {
                return json_encode(['error' => $response->json()]);
            }

            $data = $response->json();

            if (isset($data['id'])) {
                $payment->update(['payment_token' => $data['id']]);
            }

            return $data['transaction']['url'];

        } catch (\Throwable $th) {
            return json_encode(['error' => $th->getMessage()]);
        }
    }
}
