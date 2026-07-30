<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SemoaService
{
	private static string $url = '';

	private static string $userName;

	private static string $password;
	private static string $clientSecret;
	private static string $clientId;
	private static string $apiKey;
	private static string $apiReference;
	private static string $gatewayReference;

    public function __construct()
    {
        self::loadStaticData();
    }

    private static function loadStaticData(): void
    {
        self::$url = rtrim(config('semoa.url'), '/') . '/';
        self::$userName = trim(config('semoa.username'));
        self::$password = trim(config('semoa.password'));
        self::$clientSecret = trim(config('semoa.client_secret'));
        self::$clientId = trim(config('semoa.client_id'));
        self::$apiKey = trim(config('semoa.api_key'));
        self::$apiReference = trim(config('semoa.api_reference'));
        self::$gatewayReference = trim(config('semoa.gateway_reference'));
    }

    private function getToken(): string
    {
        return Cache::remember('semoa_access_token', 30 * 60, function () {
            $response = Http::post(self::$url . 'auth', [
                "grant_type" => "password",
                "username" => self::$userName,
                "password" => self::$password,
                "client_id" => self::$clientId,
                "client_secret" => self::$clientSecret,
            ]);

            if ($response->failed()) {
                \Log::error('SEMOA Auth Failed:', ['status' => $response->status(), 'body' => $response->body()]);
                throw new \Exception("Échec d'authentification SEMOA : " . $response->body());
            }

            $data = $response->json();
            \Log::debug('SEMOA Auth Success:', ['data' => array_keys($data)]); // On logue les clés reçues

            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            if (!isset($data['access_token'])) {
                throw new \Exception("Token absent de la réponse SEMOA : " . $response->body());
            }

            return $data['access_token'];
        });
    }

    /**
     * Initialise un paiement Link2Pay (V3 Partner API)
     */
    public function initializePayment(array $data, bool $isRetry = false): array
    {
        $token = $this->getToken();

        $gatewayRef = $data['gateway_reference'] ?? self::$gatewayReference;

        $payload = [
            "amount" => $data['amount'],
            // "currency" => "XOF",
            "client" => [
                "phone" => $data['phone']
            ],
            "gateway" => [
                "reference" => $gatewayRef
            ]
        ];

        // On ajoute les infos de callback et description s'ils sont présents
        if (isset($data['description'])) $payload["description"] = $data['description'];

        // Sécurité : Forcer l'URL Ngrok si configurée dans le .env
        $baseUrl = rtrim(env('APP_URL'), '/');
        $payload["callback_url"] = $data['callback_url'] ?? ($baseUrl . '/api/semoa-callback-url');

        Log::info('SEMOA Payment Initialization Payload', [
            'url' => self::$url . 'orders',
            'payload' => $payload
        ]);

        $response = Http::withHeaders([
            "Authorization" => "Bearer $token",
            "Content-Type" => "application/json",
            "Accept" => "application/json"
        ])->post(self::$url . 'orders', $payload);

        if ($response->failed()) {
            if ($response->status() === 401 && !$isRetry) {
                Cache::forget('semoa_access_token');
                return $this->initializePayment($data, true);
            }
            throw new \Exception("Erreur SEMOA (Initialisation) : " . $response->body());
        }

        return $response->json();
    }

    private function getHeaders(string $token): array
    {
        $salt = (string) random_int(0, 999999);
        // Signature basée sur le Username (demo_escen) + Api Key + Salt, en MINUSCULES
        $signature = hash('sha256', self::$userName . self::$apiKey . $salt);

        $headers = [
            "Authorization" => "Bearer $token",
            "login" => self::$userName, // demo_escen
            "apisecure" => $signature,
            "apireference" => self::$apiReference, // 20
            "api-key" => self::$apiKey,
            "salt" => $salt,
            "Content-Type" => "application/json",
            "Accept" => "application/json"
        ];

        \Log::debug('SEMOA Headers Debug (USERNAME-LOWER-SIG):', [
            'login' => $headers['login'],
            'apireference' => $headers['apireference'],
            'salt' => $headers['salt'],
            'apisecure' => $headers['apisecure']
        ]);

        return $headers;
    }

    /**
     * Vérifie le statut d'une commande
     */
    public function checkPaymentStatus(string $reference): array
    {
        $token = $this->getToken();

        $response = Http::withHeaders($this->getHeaders($token))
            ->get(self::$url . "orders/{$reference}");

        if ($response->failed()) {
            throw new \Exception("Erreur SEMOA (Vérification) : " . $response->body());
        }

        return $response->json();
    }
}
