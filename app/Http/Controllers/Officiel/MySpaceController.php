<?php

namespace App\Http\Controllers\Officiel;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MySpaceController extends Controller
{
	public function show(): View
	{

	

		return view('officiel.mySpace.show')->with([
			'candidature' => auth('web_candidatures')->user()
		]);
	}

	public function myFiles(): View
	{

	
		return view('officiel.mySpace.files')->with([
			'album' => auth('web_candidatures')->user()->album
		]);
	}

	public function constitution(): View
	{
		return view('officiel.mySpace.constitution')->with([
			'candidature' => auth('web_candidatures')->user()
		]);
	}

	public function myPayements(): View
	{
		return view('layouts.coming-soon');
	}
}
