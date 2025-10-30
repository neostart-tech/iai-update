<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
	public function create(): View
	{
		return view('admin.contacts.create')->with([
			'contact' => new Contact()
		]);
	}

	public function store(ContactRequest $request): RedirectResponse
	{
		// Basic anti-bot: optional honeypot field '_hp' and a minimal form lifetime '_ts'
		if ($request->filled('_hp')) {
			return back()->with('warning', 'Soumission détectée comme spam.');
		}
		if ($request->filled('_ts')) {
			$submittedAt = \Carbon\Carbon::parse((string) $request->input('_ts'));
			$delta = now()->diffInSeconds($submittedAt, false);
			if ($delta < 3) {
				return back()->with('warning', 'Soumission trop rapide, veuillez réessayer.');
			}
		}
		Contact::query()->create([
			...$request->validated(),
			'status' => 0
		]);
		return back()->with('success', 'Message enregistré avec succès');
	}
}
