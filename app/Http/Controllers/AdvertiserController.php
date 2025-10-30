<?php

namespace App\Http\Controllers;

use App\Enums\TypeAnnonceEnum;
use App\Http\Requests\AdvertiserRequest;
use App\Models\Advertiser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvertiserController extends Controller
{
	public function index(): View
	{
		return view('advertisers.index')->with([
			'advertisers' => Advertiser::all()
		]);
	}

	public function create(): View
	{
		return view('advertisers.create')->with([
			'advertiser' => new Advertiser(),
			'contracts' => TypeAnnonceEnum::cases()
		]);
	}

	public function store(AdvertiserRequest $request): RedirectResponse
	{
		Advertiser::query()->create($request->validated());
		successMsg('Partenaire ajouté avec succès');
		return to_route('admin.advertisers.index');
	}

	public function show(Advertiser $advertiser): View
	{
		return view('advertisers.show', compact('advertiser'));
	}

	public function edit(Advertiser $advertiser): View
	{
		return view('advertisers.edit', compact('advertiser'))->with([
			'contracts' => TypeAnnonceEnum::values()
		]);
	}

	public function update(AdvertiserRequest $request, Advertiser $advertiser): RedirectResponse
	{
		$advertiser->update($request->validated());
		successMsg('Partenaire ajouté avec succès');
		return to_route('admin.advertisers.index');
	}

	public function destroy(Request $request): RedirectResponse
	{
		$partenaireId= (int) $request->partenaireId;
		Advertiser::query()->find($partenaireId)->delete();
		successMsg('Partenaire supprimé avec succès');
		return to_route('admin.advertisers.index');
	}
}
