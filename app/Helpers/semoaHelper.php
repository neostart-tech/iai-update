<?php


use App\Facades\Semoa;
use App\Services\SemoaService;
use Illuminate\Support\Facades\Http;


if (!function_exists('getResponseData')) {

	/**
	 * @throws Exception
	 */
	function getResponseData(): array
	{
		$url = env('SEMOA_URL') . '/';
		$body = [
			"client_id" => env('SEMOA_CLIENT_ID'),
			"client_secret" => env('SEMOA_CLIENT_SECRET'),
			"username" => env('SEMOA_USERNAME'),
			"password" => env('SEMOA_PASSWORD')
		];

		$response = Http::post($url . 'auth', $body);

//		dd($response);
//		if (!$response->successful()) {
//			throw new Exception(message: $response->json());
//		}

		return $response->json();
	}
}

if (!function_exists('saveAuthData')) {
	/**
	 * @deprecated Utiliser le Cache pour stocker les tokens
	 */
	function saveAuthData(array $data): bool
	{
		return true;
	}
}

if (!function_exists('authenticateToSemoa')) {
	function authenticateToSemoa(): array
	{
		saveAuthData(getResponseData());
		return config('semoa');
	}
}

if (!function_exists('semoa')) {
	function semoa(): SemoaService
	{
		return Semoa::init();
	}
}
