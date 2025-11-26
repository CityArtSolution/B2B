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

        // Generate token
        $paymentToken = Str::uuid()->toString();
        $payment->update(['payment_token' => $paymentToken]);

        // ---------------------
        // USER DATA
        // ---------------------
        if ($info) {
            $name  = $info['name'] ?? 'Not Available';
            $email = $info['email'] ?? 'not_available@email.com';
            $phone = $info['phone'] ?? '0000000000';
            $description = $info['description'] ?? 'Payment';

            if ($info['type'] == 'subscription') {
                $successUrl = route('subscription.payment.success', ['payment' => $payment, 'token' => $paymentToken]);
                $cancelUrl  = route('subscription.payment.cancel', ['payment' => $payment, 'token' => $paymentToken]);
            } else {
                $successUrl = route('payment.success', $payment->id);
                $cancelUrl  = route('payment.cancel', $payment->id);
            }
        } else {
            $user = $payment->orders[0]->customer?->user;
            $name  = $user->name ?? 'Not Available';
            $email = $user->email ?? 'Not Available';
            $phone = $user->phone ?? '0000000000';
            $description = "Order Payment - Total {$payment->amount}";

            $successUrl = route('payment.success', $payment->id);
            $cancelUrl  = route('payment.cancel', $payment->id);
        }

        // -------------------------
        // TAP API – CREATE PAYMENT
        // -------------------------
        try {

            $payload = [
                "amount" => floatval($payment->amount),
                "currency" => $config->currency ?? "SAR",
                "description" => $description,
                "customer" => [
                    "first_name" => $name,
                    "email" => $email,
                    "phone" => [
                        "country_code" => "966",
                        "number" => $phone
                    ]
                ],
                "source" => [
                    "id" => "src_all"   // To allow all payment methods
                ],
                "redirect" => [
                    "url" => $successUrl
                ],
                "post" => [
                    "url" => $successUrl
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => "Bearer ".$config->secret_key,
                'Content-Type'  => 'application/json'
            ])->post("https://api.tap.company/v2/charges", $payload);

            if (!$response->successful()) {
                return json_encode(['error' => $response->json()]);
            }

            $data = $response->json();

            // Save tap charge id
            if (isset($data['id'])) {
                $payment->update(['payment_token' => $data['id']]);
            }

            // Return redirect URL
            return $data['transaction']['url'];

        } catch (\Throwable $th) {
            return json_encode(['error' => $th->getMessage()]);
        }
    }
}
