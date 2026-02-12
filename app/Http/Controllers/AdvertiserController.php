<?php

namespace App\Http\Controllers;

use App\Enums\TypeAnnonceEnum;
use App\Http\Requests\AdvertiserRequest;
use App\Http\Resources\AdvertiserResource;
use App\Models\Advertiser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvertiserController extends Controller
{
	public function index()
	{
		return AdvertiserResource::collection(Advertiser::all());
		// return view('advertisers.index')->with([
		// 	'advertisers' => Advertiser::all()
		// ]);
	}

	public function create(): View
	{
		return view('advertisers.create')->with([
			'advertiser' => new Advertiser(),
			'contracts' => TypeAnnonceEnum::cases()
		]);
	}

	public function store(AdvertiserRequest $request)
	{
		$ad = Advertiser::query()->create($request->validated());
		return new AdvertiserResource($ad);
		// successMsg('Partenaire ajouté avec succès');
		// return to_route('admin.advertisers.index');
	}

	public function show(Advertiser $advertiser)
	{
		return new AdvertiserResource($advertiser);
		// return view('advertisers.show', compact('advertiser'));
	}

	public function edit(Advertiser $advertiser): View
	{
		return view('advertisers.edit', compact('advertiser'))->with([
			'contracts' => TypeAnnonceEnum::values()
		]);
	}

	public function update(AdvertiserRequest $request, Advertiser $advertiser)
	{
		$advertiser->update($request->validated());
		return new AdvertiserResource($advertiser);
		// successMsg('Partenaire ajouté avec succès');
		// return to_route('admin.advertisers.index');
	}

	// public function destroy(Request $request): RedirectResponse
	// {
	// 	$partenaireId = (int) $request->partenaireId;
	// 	Advertiser::query()->find($partenaireId)->delete();
	// 	successMsg('Partenaire supprimé avec succès');
	// 	return to_route('admin.advertisers.index');
	// }

	public function destroy(Advertiser $advertiser)
	{
		if (!$advertiser) {
			return __404('Partenaire non trouvé');
		}
		$advertiser->delete();
		// successMsg('Partenaire supprimé avec succès');
		// return to_route('admin.advertisers.index');
		return new AdvertiserResource($advertiser);
	}
}
