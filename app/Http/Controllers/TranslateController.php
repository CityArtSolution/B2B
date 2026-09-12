<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Stichoza\GoogleTranslate\GoogleTranslate;
use GuzzleHttp\Client;

class TranslateController extends Controller
{
    public function translate(Request $request)
    {
        $text = trim($request->text ?? '');

        if ($text === '') {
            return response()->json([
                'in' => '',
                'ur' => '',
                'en' => ''
            ]);
        }

        $hindi   = $this->translateTo($text, 'hi');
        $urdu    = $this->translateTo($text, 'ur');
        $english = $this->translateTo($text, 'en');

        return response()->json([
            'in' => $hindi,
            'ur' => $urdu,
            'en' => $english
        ]);
    }

    protected function translateTo(string $text, string $targetLang): string
    {
        $cacheKey = 'ctrl_trans_' . $targetLang . '_' . md5(mb_strtolower($text));

        return Cache::remember($cacheKey, 86400, function () use ($text, $targetLang) {
            // Attempt 1: Google Translate
            try {
                $tr = new GoogleTranslate($targetLang, null, [
                    'timeout' => 2,
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                    ]
                ]);
                $translated = $tr->translate($text);
                if (!empty($translated)) {
                    return $translated;
                }
            } catch (\Throwable $e) {
                // Rate limited or error, fall through to fallback
            }

            // Attempt 2: MyMemory API fallback
            try {
                $client = new Client(['timeout' => 2]);
                $response = $client->get('https://api.mymemory.translated.net/get', [
                    'query' => [
                        'q' => $text,
                        'langpair' => "ar|{$targetLang}"
                    ]
                ]);
                $data = json_decode($response->getBody(), true);
                $translated = $data['responseData']['translatedText'] ?? null;
                if (!empty($translated)) {
                    return $translated;
                }
            } catch (\Throwable $e) {
                // Ignore
            }

            // Safe fallback: return empty or original text without crashing
            return '';
        });
    }
}
