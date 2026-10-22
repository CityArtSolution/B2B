<?php

namespace App\Console\Commands;

use App\Http\Controllers\Gateway\Tabby\ProcessController as Tabby;
use Illuminate\Console\Command;

/**
 * Registers (or updates) this site's webhook with Tabby, for the environment of the saved secret key
 * (sk_test_ registers the test webhook, sk_ the live one). Run it again after changing the key,
 * the merchant code, APP_KEY or the site URL.
 */
class TabbyRegisterWebhook extends Command
{
    protected $signature = 'tabby:register-webhook {url? : Public HTTPS webhook URL, defaults to this site\'s /tabby/webhook}';

    protected $description = 'Register this site\'s webhook with Tabby so it receives payment status updates';

    public function handle(): int
    {
        $config = Tabby::gatewayConfig();
        if (empty($config->secret_key) || empty($config->merchant_code)) {
            $this->error('Save the Tabby secret key and merchant code in Admin > Payment Gateways first.');

            return self::FAILURE;
        }

        $url = $this->argument('url') ?: route('tabby.webhook');
        if (! str_starts_with($url, 'https://')) {
            $this->error("Tabby only calls HTTPS URLs, got: {$url}");
            $this->line('Set APP_URL to the public https address, or pass the URL: php artisan tabby:register-webhook https://example.com/tabby/webhook');

            return self::FAILURE;
        }

        $client = Tabby::http($config)->withHeaders(['X-Merchant-Code' => $config->merchant_code]);
        $body = [
            'url' => $url,
            'header' => ['title' => Tabby::WEBHOOK_HEADER, 'value' => Tabby::webhookSecret($config)],
        ];

        $existing = $client->get('/api/v1/webhooks');
        if (! $existing->successful()) {
            $this->error('Tabby refused the request ('.$existing->status().'): '.$existing->body());

            return self::FAILURE;
        }

        // Tabby answers with one webhook object, a list of them, or null when none is registered
        $webhooks = $existing->json() ?? [];
        if (isset($webhooks['id'])) {
            $webhooks = [$webhooks];
        }
        $webhook = collect($webhooks)->firstWhere('url', $url);
        $response = $webhook
            ? $client->put('/api/v1/webhooks/'.$webhook['id'], $body)
            : $client->post('/api/v1/webhooks', $body);

        if (! $response->successful()) {
            $this->error('Tabby refused the webhook ('.$response->status().'): '.$response->body());

            return self::FAILURE;
        }

        $mode = str_starts_with((string) $config->secret_key, 'sk_test_') ? 'test' : 'live';
        $this->info(($webhook ? 'Updated' : 'Registered')." Tabby {$mode} webhook: {$url}");

        return self::SUCCESS;
    }
}
