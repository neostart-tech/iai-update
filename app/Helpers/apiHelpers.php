<?php

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

if (!function_exists('__200')) {

	/**
	 * Return a standard 200 response with optional data
	 *
	 * @param string $message
	 * @param array|null $data
	 * @return Response|ResponseFactory
	 * @package IAI-SITE
	 * @author SOSSOU-GAH Ézéchiel
	 * @created 2024-07-10
	 */
	function __200(string $message = 'No special message for this response ', array $data = null): Response|ResponseFactory
	{
		$content = [
			'status' => 'ok',
			'code' => ResponseAlias::HTTP_OK,
			'message' => $message
		];

		if ($data) {
			$content = [...$content, ...$data];
		}

		return response($content, ResponseAlias::HTTP_OK);
	}
}

if (!function_exists('__404')) {

	/**
	 * Return a standard 404 error message response
	 *
	 * @param string $message
	 * @return Response|ResponseFactory
	 * @package IAI-SITE
	 * @author SOSSOU-GAH Ézéchiel
	 * @created 2024-07-10
	 */
	function __404(string $message = 'Oups, aucune resource n\'a été trouvée'): Response|ResponseFactory
	{
		return response([
			'message' => $message,
			'status' => 'failed',
			'code' => ResponseAlias::HTTP_NOT_FOUND
		], ResponseAlias::HTTP_NOT_FOUND);
	}
}

if (!function_exists('__422')) {

	/**
	 * Return a standard 422 error message response after a failed validation
	 *
	 * @param string|array $messages
	 * @return Response|ResponseFactory
	 * @package IAI-SITE
	 * @author SOSSOU-GAH Ézéchiel
	 * @created 2024-07-10
	 */
	function __422(string|array $messages): Response|ResponseFactory
	{
		return response([
			'message' => is_array($messages) ? $messages[0] : $messages,
			'status' => 'Validation failed',
			'code' => ResponseAlias::HTTP_UNPROCESSABLE_ENTITY
		], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
	}
}

if (!function_exists('__500')) {

	/**
	 * Return a standard 500 error message response
	 *
	 * @param array|string|null $message
	 * @return Response|ResponseFactory
	 * @package IAI-SITE
	 * @author SOSSOU-GAH Ézéchiel
	 * @created 2024-07-10
	 */
	function __500(array|string $message = null): Response|ResponseFactory
	{
		$content = [
			'status' => 'failed',
			'code' => ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
		];

		if (!$message) {
			$content['message'] = 'Oups, une erreur inattendue est survenue';
		} else {
			$content['message'] = is_array($message) ? Arr::first($message) : $message;
		}
		return response($content, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
	}
}

if (!function_exists('getPublicImagePath')) {

	/**
	 * Returns a string corresponding to the url of an image on the public disk
	 *
	 * @param string $path
	 * @return string
	 * @package IAI-SITE
	 * @author SOSSOU-GAH Ézéchiel
	 * @created 2024-07-10
	 */
	function getPublicImagePath(string $path): string
	{
		return Storage::disk('public')->url($path);
	}
}

if (!function_exists('getFileDownloadableUrl')) {

	/**
	 * Returns a string corresponding to the url of a file on the public disk
	 *
	 * @param string $path
	 * @return string
	 * @package IAI-SITE
	 * @author SOSSOU-GAH Ézéchiel
	 * @created 2024-07-10
	 */
	function getFileDownloadableUrl(string $path): string
	{
		return 'storage/' . $path;
	}
}
