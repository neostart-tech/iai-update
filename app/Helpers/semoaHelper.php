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


	function saveAuthData(array $data): int|false
	{
		$formattedData = '[
		"access_token" => "' . $data['access_token'] . '",
		"expires_in" => "' . $data['expires_in'] . '",
		"refresh_expires_in" => "' . $data['refresh_expires_in'] . '",
		"refresh_token" => "' . $data['refresh_token'] . '",
		"token_type" => "' . $data['token_type'] . '",
		"not-before-policy" => "' . $data['not-before-policy'] . '",
		"session_state" => "' . $data['session_state'] . '",
		"scope" => "' . $data['scope'] . '"
]';

		return file_put_contents('config/semoa.php', '<?php 
	return ' . $formattedData . '; 
	'
		);
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
