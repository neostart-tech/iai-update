<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Client\{PendingRequest, Response};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

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
		$this->isBooted = false;
		if (($configs = config('semoa')) === []) {
			$configs = authenticateToSemoa();
		}

		$this->refreshConfigs($configs);
		$this->refreshToken($this->getAccessToken());

		self::loadStaticData();
		$this->isBooted = true;
	}

	private function refreshConfigs(array $configs): self
	{
		$this->accessToken = $configs['access_token'];
		$this->expiresIn = $configs['expires_in'];
		$this->refreshExpiresIin = $configs['refresh_expires_in'];
		$this->refreshToken = $configs['refresh_token'];
		$this->tokenType = $configs['token_type'];
		$this->notBeforePolicy = $configs['not-before-policy'];
		$this->sessionState = $configs['session_state'];
		$this->scope = $configs['scope'];

		$this->isBooted = true;
		return $this;
	}

	private function refreshToken(string $token): self
	{
		$this->headers["Authorization"] = 'Bearer ' . $token;
		return $this;
	}

	private static function loadStaticData(): void
	{
		self::$url = env('SEMOA_URL') . '/';
		self::$userName = env('SEMOA_CLIENT_ID');
		self::$password = ('SEMOA_CLIENT_SECRET');
		self::$clientSecret = env('SEMOA_USERNAME');
		self::$clientId = env('SEMOA_PASSWORD');
		self::$apiKey = env('SEMOA_API_KEY');
		self::$apiReference = env('SEMOA_API_REFERENCE');
	}

	private function authenticateToSemoa(): array
	{
		if ($this->isBooted) {
			return config('semoa');
		}

		dump('called', $this->isBooted);
		$configs = authenticateToSemoa();
		$this->isBooted = true;
		dump($this->isBooted);
		return $configs;
	}

	public function get(string $url, array $data = [], array $headers = []): Response
	{
		return $this->performRequest(verb: 'get', url: $url, data: $data, headers: $headers);
//		return $this;
	}

	public function post(string $url, array $data, array $headers = [], bool $encode = false): Response
	{
		return $this->performRequest(verb: 'post', url: $url, data: $data, headers: $headers, encode: $encode);
//		return $this;
	}

	private function performRequest(string $verb, string $url, array $data = [], array $headers = [], bool $encode = false): Response
	{
		if (empty($headers)) {
			$headers = $this->headers;
		}

		if ($encode) {
			$data = Json::encode($data);
		}

		$this->request = Http::withHeaders($headers)->{$verb}(self::$url . $url, $data);

		$responseBody = $this->request->json();
		dump($responseBody);
		dd($this);
		if ($responseBody['code'] == 400 && $this->request->json()['message'] == "Expired token" || $this->request->json()['message'] == "Invalid token") {
			$this->isBooted = false;
//			dd('ici', $this->isBooted, $responseBody);
			$this->refreshConfigs($this->authenticateToSemoa())->performRequest($verb, $url, $data, $headers);
		}
		return $this->request;
	}

	public function json(): array
	{
		return $this->request->json();
	}

	public function collect(Response $response): Collection
	{
		return $this->request->collect();
	}

	public function toString(): string
	{
		return 'Not implemented';
	}
}
