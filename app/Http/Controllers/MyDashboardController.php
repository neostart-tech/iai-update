<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MyDashboardController extends Controller
{
	public function __invoke(Request $request): View
	{
		return view('dashboard')->with([
			'breadCrumbs' => ['Administration', 'Mon dashboard']
		]);
	}
}
