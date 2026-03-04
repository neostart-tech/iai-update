<?php

namespace App\Http\Controllers;

use App\Enums\TypeAnnonceEnum;
use App\Http\Requests\AdvertiserRequest;
use App\Http\Resources\AdvertiserResource;
use App\Models\Advertiser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log; // Ajoutez ceci pour les logs

class AdvertiserController extends Controller
{
	public function index()
	{
		return AdvertiserResource::collection(Advertiser::all());
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
		try {
			$data = $request->validated();

			if ($request->hasFile('logo')) {
				Log::info('Upload de logo - store', [
					'name' => $request->file('logo')->getClientOriginalName(),
					'size' => $request->file('logo')->getSize(),
					'tmp_path' => $request->file('logo')->getPathname()
				]);
				
				$path = $request->file('logo')->store('logos', 'public');
				$data['logo'] = $path;
				
				Log::info('Logo stocké', ['path' => $path]);
			}
			
			$ad = Advertiser::query()->create($data);
			return new AdvertiserResource($ad);
			
		} catch (\Exception $e) {
			Log::error('Erreur store advertiser', [
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function show(Advertiser $advertiser)
	{
		return new AdvertiserResource($advertiser);
	}

	public function edit(Advertiser $advertiser): View
	{
		return view('advertisers.edit', compact('advertiser'))->with([
			'contracts' => TypeAnnonceEnum::values()
		]);
	}

	public function update(AdvertiserRequest $request, Advertiser $advertiser)
	{
		try {
			$data = $request->validated();
			
			Log::info('Début update advertiser', [
				'id' => $advertiser->id,
				'has_logo' => $request->hasFile('logo')
			]);
			
			// Gérer l'upload du logo
			if ($request->hasFile('logo')) {
				Log::info('Upload de logo - update', [
					'name' => $request->file('logo')->getClientOriginalName(),
					'size' => $request->file('logo')->getSize(),
					'tmp_path' => $request->file('logo')->getPathname()
				]);
				
				// Supprimer l'ancien logo si existant
				if ($advertiser->logo) {
					Storage::disk('public')->delete($advertiser->logo);
					Log::info('Ancien logo supprimé', ['old_logo' => $advertiser->logo]);
				}
				
				$path = $request->file('logo')->store('logos', 'public');
				$data['logo'] = $path;
				
				Log::info('Nouveau logo stocké', ['path' => $path]);
			}
			
			$advertiser->update($data);
			Log::info('Advertiser mis à jour avec succès');
			
			return new AdvertiserResource($advertiser);
			
		} catch (\Exception $e) {
			Log::error('Erreur update advertiser', [
				'message' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}

	public function destroy(Advertiser $advertiser)
	{
		if (!$advertiser) {
			return response()->json(['error' => 'Partenaire non trouvé'], 404);
		}
		
		try {
			// Supprimer le logo si existant
			if ($advertiser->logo) {
				Storage::disk('public')->delete($advertiser->logo);
			}
			
			$advertiser->delete();
			return new AdvertiserResource($advertiser);
			
		} catch (\Exception $e) {
			Log::error('Erreur delete advertiser', [
				'message' => $e->getMessage()
			]);
			return response()->json(['error' => $e->getMessage()], 500);
		}
	}
}