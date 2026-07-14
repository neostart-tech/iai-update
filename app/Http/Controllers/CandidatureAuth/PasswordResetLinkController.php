<?php

namespace App\Http\Controllers\CandidatureAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
	public function store(Request $request)
	{
		$request->validate([
			'email' => ['required', 'email'],
		]);

		Password::setDefaultDriver('candidatures');
		$status = Password::sendResetLink($request->only('email'));

		if ($request->wantsJson() || $request->is('api/*')) {
			if ($status == Password::RESET_LINK_SENT) {
				return response()->json(['message' => __($status)]);
			}
			return response()->json(['message' => __($status)], 422);
		}

		return $status == Password::RESET_LINK_SENT
			? back()->with('status', __($status))
			: back()->withInput($request->only('email'))
				->withErrors(['email' => __($status)]);
	}
}
