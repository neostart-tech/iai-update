<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class NotificationReadingController extends Controller
{
	public function clean(): RedirectResponse
	{
		request()->user()->notifications()->delete();
		return back()->with(successMsg('Notifications effacées avec succès'));
	}

	public function readAll(): RedirectResponse
	{
		request()->user()->unreadNotifications->markAsRead();

		return back()->with(successMsg('Notifications marquées comme lues avec succès'));
	}
}
