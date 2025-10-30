<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
	public function index(): View
	{
		return view('admin.contacts.index')->with([
			'contacts' => Contact::query()->orderByDesc('created_at')->get()
				->each(function (Contact $evenement) {
					$evenement->setAttribute('createdAt', $evenement->getAttribute('created_at')->translatedFormat('d F Y'));
				})
		]);
	}

	public function store(ContactRequest $request): RedirectResponse
	{
		Contact::query()->create([
			...$request->validated(),
			'status' => 0
		]);
		return back()->with('success', 'Message enregistré avec succès');
	}

	public function read(Contact $contact): RedirectResponse
	{
		$contact->update(['status' => 1]);
		return back()->with(successMsg('Message lu avec succès'));
	}

	public function destroy(Contact $contact): RedirectResponse
	{
		$contact->delete();
		return back()->with('success', 'Message supprimé avec succès');
	}
}
