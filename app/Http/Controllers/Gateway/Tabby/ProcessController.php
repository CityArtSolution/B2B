<?php

namespace App\Http\Controllers\Gateway\Tabby;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $secretKey = $config->secret_key ?? null;
        $merchantCode = $config->merchant_code ?? null;
        $baseUrl      = 'https://api.tabby.ai/api/v2/checkout';

        if (!$secretKey || !$merchantCode) {
            return json_encode(['error' => 'Tabby secret key or merchant code is not configured']);
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
                    "name"  => $info['name'] ?? $payment->orders->first()?->customer?->user?->name ?? 'Customer',
                    "phone" => $info['phone'] ?? $payment->orders->first()?->customer?->user?->phone ?? '0000000000',
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

            $webUrl = $data['configuration']['available_products']['installments'][0]['web_url'] ?? null;
            if ($webUrl) {
                return $webUrl;
            }

            // Tabby created the session but rejected the buyer/amount, or the request itself failed
            $rejectionReason = $data['configuration']['products']['installments']['rejection_reason'] ?? null;
            Log::warning('Tabby checkout not available', [
                'payment_id' => $payment->id,
                'http_status' => $response->status(),
                'status' => $data['status'] ?? null,
                'rejection_reason' => $rejectionReason,
                'error' => $data['error'] ?? $data['errors'] ?? null,
            ]);

            return json_encode(['error' => self::rejectionMessage($rejectionReason, $response->successful())]);
        } catch (\Throwable $th) {
            Log::error('Tabby request failed', ['payment_id' => $payment->id, 'exception' => get_class($th)]);
            return json_encode(['error' => 'Unable to connect to Tabby. Please try again.']);
        }
    }

    /**
     * Customer-facing message for a Tabby rejection reason
     */
    private static function rejectionMessage(?string $reason, bool $requestSucceeded): string
    {
        return match ($reason) {
            'order_amount_too_high' => 'This purchase is above your current spending limit with Tabby, try a smaller cart or use another payment method.',
            'order_amount_too_low' => 'The purchase amount is below the minimum amount required to use Tabby, try adding more items or use another payment method.',
            'not_available' => 'Sorry, Tabby is unable to approve this purchase. Please use an alternative payment method for your order.',
            default => $requestSucceeded
                ? 'Sorry, Tabby is unable to approve this purchase. Please use an alternative payment method for your order.'
                : 'Tabby could not create the payment. Please check the gateway settings.',
        };
    }
}
