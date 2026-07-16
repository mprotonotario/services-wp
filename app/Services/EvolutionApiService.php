<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionApiService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $instance;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.evolution.url', env('EVOLUTION_API_URL', 'http://evolution:8080')), '/');
        $this->apiKey = config('services.evolution.key', env('EVOLUTION_API_KEY', ''));
        $this->instance = config('services.evolution.instance', env('EVOLUTION_INSTANCE_NAME', 'laravel'));
    }

    /**
     * Set target chat presence state (composing, recording, paused)
     *
     * @param string $number
     * @param string $presence (composing, recording, paused)
     * @return bool
     */
    public function sendPresence(string $number, string $presence = 'composing'): bool
    {
        try {
            $url = "{$this->baseUrl}/chat/sendPresence/{$this->instance}";
            
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'number' => $number,
                'presence' => $presence,
                'delay' => 0
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error("Evolution API Presence failed", [
                'status' => $response->status(),
                'response' => $response->json(),
                'number' => $number,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error("Evolution API Presence error", [
                'message' => $e->getMessage(),
                'number' => $number,
            ]);
            return false;
        }
    }

    /**
     * Send plain text message
     *
     * @param string $number
     * @param string $text
     * @param int $delayMs
     * @return array|null
     */
    public function sendText(string $number, string $text, int $delayMs = 1200): ?array
    {
        try {
            $url = "{$this->baseUrl}/message/sendText/{$this->instance}";

            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'number' => $number,
                'textMessage' => [
                    'text' => $text
                ],
                'options' => [
                    'delay' => $delayMs,
                    'presence' => 'composing'
                ]
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("Evolution API SendText failed", [
                'status' => $response->status(),
                'response' => $response->json(),
                'number' => $number,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("Evolution API SendText error", [
                'message' => $e->getMessage(),
                'number' => $number,
            ]);
            return null;
        }
    }

    /**
     * Set instance settings (like read_messages, always_online, etc.)
     *
     * @param array $settings
     * @return bool
     */
    public function setSettings(array $settings): bool
    {
        try {
            $url = "{$this->baseUrl}/settings/set/{$this->instance}";

            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, $settings);

            if ($response->successful()) {
                return true;
            }

            Log::error("Evolution API Set Settings failed", [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error("Evolution API Set Settings error: " . $e->getMessage());
            return false;
        }
    }
}
