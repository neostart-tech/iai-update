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
            // 1. Récupérer le contenu brut (le JWT)
            $token = $request->getContent();
            
            Log::info('SEMOA Webhook JWT Received', ['token' => substr($token, 0, 20) . '...']);

            if (!$token) {
                // Si pas de raw content, vérifier si c'est dans un champ 'token'
                $token = $request->input('token');
            }

            if (!$token) {
                Log::warning('SEMOA Webhook: Aucun jeton reçu.');
                return response(['message' => 'Aucun jeton reçu'], 400);
            }

            // 2. Décoder le JWT (Format SEMOA : [header].[payload].[signature])
            // On peut le décoder manuellement ou via une lib, mais ici on va extraire le payload (partie 2)
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                Log::error('SEMOA Webhook: Format JWT invalide');
                return response(['message' => 'Format invalide'], 400);
            }

            $payload = json_decode(base64_decode($parts[1]), true);
            Log::info('SEMOA Webhook Decoded Payload', $payload);

            // 3. Log dans la table de suivi
			$callBack = SemoaCallBack::create([
				'data' => $payload
			]);

			$reference = $payload['order_reference'] ?? null;
			$state = $payload['state'] ?? null;

			if (!$reference) {
				return response(['message' => 'Référence manquante'], 400);
			}

			// 4. Trouver le paiement correspondant
			$paiement = \App\Models\Paiement::where('reference', $reference)->first();

			if (!$paiement) {
				Log::warning("SEMOA Webhook: Paiement introuvable pour la référence $reference");
				return response(['message' => 'Paiement introuvable'], 404);
			}

            // 5. Vérification de sécurité sur le montant
            $receivedAmount = (float) ($payload['received_amount'] ?? 0);
            if (abs($receivedAmount - (float)$paiement->montant) > 1) { // Marge de 1 pour les arrondis
                Log::error("SEMOA Webhook: Écart de montant pour $reference. Attendu: {$paiement->montant}, Reçu: $receivedAmount");
                $paiement->update(['status' => 'rejete', 'commentaire' => 'Écart de montant détecté']);
                return response(['message' => 'Écart de montant'], 400);
            }

			// 6. Traiter selon l'état (Semoa utilise souvent 'Paid' ou 'SUCCESS')
			if ($state === 'SUCCESS' || $state === 'COMPLETED' || $state === 'Paid') {
				if ($paiement->status !== 'valide') {
                    // On enregistre le lien du reçu SEMOA dans la colonne recu
                    $paiement->recu = $payload['bill_url'] ?? $paiement->recu;
					$paiement->valider();
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
