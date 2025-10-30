<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		foreach (glob(app_path('Helpers/*.php')) as $helper) {
			require_once $helper;
		}
	}
}
