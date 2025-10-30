<?php

namespace App\Facades;

use App\Services\SemoaService;
use Illuminate\Contracts\Container\BindingResolutionException;

class Semoa
{

	/**
	 * @throws BindingResolutionException
	 */
	public static function init(): SemoaService
	{
		return self::resolve();
	}

	/**
	 * @throws BindingResolutionException
	 */
	public function __invoke(): SemoaService
	{
		return static::resolve();
	}

	/**
	 * @throws BindingResolutionException
	 */
	private static function resolve(): SemoaService
	{
		return app()->make(SemoaService::class);
	}
}
