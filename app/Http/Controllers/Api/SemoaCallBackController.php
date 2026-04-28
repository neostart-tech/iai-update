<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SemoaCallBack;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;


class SemoaCallBackController extends Controller
{
	public function __invoke(Request $request)
	{
		try {
			Log::info('SEMOA Webhook Received', $request->all());

			// 1. Log dans la table de suivi
			$callBack = SemoaCallBack::create([
				'data' => $request->all()
			]);

			$data = $request->all();
			$reference = $data['order_reference'] ?? null;
			$state = $data['state'] ?? null;

			if (!$reference) {
				return response(['message' => 'Référence manquante'], 400);
			}

			// 2. Trouver le paiement correspondant
			$paiement = \App\Models\Paiement::where('reference', $reference)->first();

			if (!$paiement) {
				Log::warning("SEMOA Webhook: Paiement introuvable pour la référence $reference");
				return response(['message' => 'Paiement introuvable'], 404);
			}

			// 3. Traiter selon l'état
			if ($state === 'SUCCESS') {
				if ($paiement->status !== 'valide') {
					$paiement->valider(); // Utilise la méthode valider() du modèle Paiement
					Log::info("SEMOA Webhook: Paiement $reference validé avec succès.");
				}
			} elseif ($state === 'FAILED' || $state === 'CANCELLED') {
				$paiement->update(['status' => 'rejete']);
				Log::info("SEMOA Webhook: Paiement $reference marqué comme rejeté.");
			}

			return response([
				'message' => 'Notification traitée avec succès',
				'row_id' => $callBack->id
			]);

		} catch (Throwable $e) {
			Log::error('SEMOA Webhook Error: ' . $e->getMessage());
			return response([
				'message' => 'Erreur lors du traitement',
				'error' => $e->getMessage()
			], 500);
		}
	}
}
