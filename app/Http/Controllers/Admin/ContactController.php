<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Http\Resources\ContactResource;
use App\Mail\Contacts\ContactReplyMail;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
	public function index()
	{

		return ContactResource::collection(Contact::query()->orderByDesc('created_at')->get());
		// return view('admin.contacts.index')->with([
		// 	'contacts' => Contact::query()->orderByDesc('created_at')->get()
		// 		->each(function (Contact $evenement) {
		// 			$evenement->setAttribute('createdAt', $evenement->getAttribute('created_at')->translatedFormat('d F Y'));
		// 		})
		// ]);
	}

	public function show(Contact $contact)
	{
		return new ContactResource($contact);
	}

	public function countEnreadMessage()
	{
		return response()->json(['count' => Contact::query()->where("status", false)->count()]);
	}

	public function store(ContactRequest $request): RedirectResponse
	{
		Contact::query()->create([
			...$request->validated(),
			'status' => 0
		]);
		return back()->with('success', 'Message enregistré avec succès');
	}

	public function read(Contact $contact)
	{
		$contact->update(['status' => 1]);
		return new ContactResource($contact);
		// return back()->with(successMsg('Message lu avec succès'));
	}

	public function destroy(Contact $contact)
	{
		$contact->delete();
		return new ContactResource($contact);
		return back()->with('success', 'Message supprimé avec succès');
	}

	public function reply(Request $request, Contact $contact)
	{
		if (!$contact->email) {
			return response()->json(['message' => "Ce message n'a pas d'adresse email associée."], 422);
		}

		$data = $request->validate([
			'message' => ['required', 'string', 'max:2000'],
		]);

		Mail::to($contact->email)->send(
			new ContactReplyMail($contact->nom, $data['message'], $contact->message ?? '')
		);

		$contact->update(['status' => 1]);

		return new ContactResource($contact);
	}
}
