<?php

namespace App\Providers;

use App\Facades\Semoa;
use App\Services\SemoaService;
use Illuminate\Support\ServiceProvider;
use Schema;

class AppServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		//
	}

	/**
	 * Bootstrap any application services.
	 */
	public function boot(): void
	{
		$this->app->singleton(Semoa::class, fn() => new SemoaService());
		Schema::defaultStringLength(191);
	}
}
