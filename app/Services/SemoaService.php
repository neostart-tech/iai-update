<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Client\{PendingRequest, Response};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class SemoaService
{
	private string $accessToken;

	/**
	 * @return string
	 */
	public function getAccessToken(): string
	{
		return $this->accessToken;
	}

	private bool $isBooted;

	/**
	 * @return string
	 */
	public function getExpiresIn(): string
	{
		return $this->expiresIn;
	}

	/**
	 * @return string
	 */
	public function getRefreshExpiresIin(): string
	{
		return $this->refreshExpiresIin;
	}

	/**
	 * @return string
	 */
	public function getRefreshToken(): string
	{
		return $this->refreshToken;
	}

	/**
	 * @return string
	 */
	public function getTokenType(): string
	{
		return $this->tokenType;
	}

	/**
	 * @return string
	 */
	public function getSessionState(): string
	{
		return $this->sessionState;
	}

	/**
	 * @return string
	 */
	public function getScope(): string
	{
		return $this->scope;
	}

	private string $expiresIn = '';

	private string $refreshExpiresIin = '';

	private string $refreshToken = '';

	private string $tokenType = '';

	private string $sessionState = '';

	private string $scope = '';

	/**
	 * @var PendingRequest $response
	 */
	public mixed $request;

	public string $notBeforePolicy = '';

	public array $headers = [
		'Content-Type' => 'application/json',
		'Accept' => 'Application/json'
	];

	private static string $url = '';

	private static string $userName;

	private static string $password;
	private static string $clientSecret;
	private static string $clientId;
	private static string $apiKey;
	private static string $apiReference;

    public function __construct()
    {
        self::loadStaticData();
    }

    private static function loadStaticData(): void
    {
        self::$url = config('semoa.url') . '/';
        self::$userName = config('semoa.username');
        self::$password = config('semoa.password');
        self::$clientSecret = config('semoa.client_secret');
        self::$clientId = config('semoa.client_id');
        self::$apiKey = config('semoa.api_key');
        self::$apiReference = config('semoa.api_reference');
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
                throw new \Exception("Échec d'authentification SEMOA : " . $response->body());
            }

            return $response->json()['access_token'];
        });
    }

    private function getHeaders(string $token): array
    {
        $salt = random_int(0, 999999);
        $signature = strtoupper(hash('sha256', self::$userName . self::$apiKey . $salt));
        
        $headers = [
            "Authorization" => "Bearer $token",
            "login" => self::$userName,
            "apisecure" => $signature,
            "apireference" => self::$userName,
            "api-key" => self::$apiKey,
            "salt" => $salt,
            "Content-Type" => "application/json",
            "Accept" => "application/json"
        ];
        
        \Log::debug('SEMOA Headers Debug (MAJ):', [
            'login' => $headers['login'],
            'apireference' => $headers['apireference'],
            'salt' => $headers['salt'],
            'apisecure' => $headers['apisecure'],
            'api_key_used' => substr(self::$apiKey, 0, 5) . '...'
        ]);

        return $headers;
    }

    /**
     * Initialise un paiement Link2Pay
     */
    public function initializePayment(array $data, bool $isRetry = false): array
    {
        $token = $this->getToken();
        
        $response = Http::withHeaders($this->getHeaders($token))
            ->post(self::$url . 'orders', [
                'amount' => $data['amount'],
                'description' => $data['description'] ?? 'Paiement',
                'client' => [
                    'lastname' => $data['lastname'],
                    'firstname' => $data['firstname'],
                    'phone' => $data['phone'],
                ],
                'currency' => 'XOF',
                'callback_url' => $data['callback_url'] ?? route('api.semoa.callback'),
                'success_url' => $data['success_url'] ?? null,
                'cancel_url' => $data['cancel_url'] ?? null,
            ]);

        if ($response->failed()) {
            if ($response->status() === 401 && !$isRetry) {
                Cache::forget('semoa_access_token');
                return $this->initializePayment($data, true);
            }
            throw new \Exception("Erreur SEMOA (Initialisation) : " . $response->body());
        }

        return $response->json();
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
