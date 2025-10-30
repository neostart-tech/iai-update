<?php

namespace App\Http\Controllers;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MyAccountController extends Controller
{
	public function show(): View
	{
		return view('etudiants.my-space.my-account');
	}

	public function updatePassword(Request $request): RedirectResponse
	{
		$request->validate([
			'old_password' => ['required'],
			'new_password' => ['required', 'min:8', 'confirmed'],
		]);

		if (!Hash::check($password = $request->input('old_password'), ($user = $request->user())->password)) {
			return back()->withErrors(['old_password' => 'L\'ancien mot de passe fourni n\'est pas valable']);
		}

		$user->update(['password' => Hash::make($password)]);
		successMsg('Mot de passe modifié avec succès');
		return back();
	}
}
