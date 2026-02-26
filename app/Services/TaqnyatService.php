<?php

namespace App\Services;

use App\Services\Contracts\SmsGatewayInterface;
use Illuminate\Support\Facades\Http;

class TaqnyatService implements SmsGatewayInterface
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function sendMessage($phone, $message)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->config->bearer_token,
            'Content-Type' => 'application/json',
        ])->post('https://api.taqnyat.sa/v1/messages', [
            'recipients' => [$phone],
            'body' => $message,
            'sender' => $this->config->sender,
        ]);

        return $response->successful();
    }
}
