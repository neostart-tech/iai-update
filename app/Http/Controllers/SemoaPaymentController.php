<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Services\PaiementEtudiantService;
use App\Services\SemoaService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SemoaPaymentController extends Controller
{
    protected $paiementService;
    protected $semoaService;

    public function __construct(PaiementEtudiantService $paiementService, SemoaService $semoaService)
    {
        $this->paiementService = $paiementService;
        $this->semoaService = $semoaService;
    }

    /**
     * Initier un paiement via SEMOA
     */
    public function initiate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'etudiant_id' => 'required|exists:etudiants,id',
            'montant' => 'required|numeric|min:100',
            'lastname' => 'required|string',
            'firstname' => 'required|string',
            'phone' => 'required|string',
            'nature_paiement' => 'nullable|string|in:scolarite,inscription',
            'payable_id' => 'nullable|integer',
            'payable_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // 1. Créer le paiement en attente dans notre base
            $paiement = $this->paiementService->creerPaiementEnAttente(
                $request->etudiant_id,
                $request->montant,
                'semoa',
                $request->get('nature_paiement', 'scolarite'),
                $request->payable_id,
                $request->payable_type,
                "Initialisation paiement SEMOA"
            );

            // 2. Appeler le service SEMOA
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $semoaResponse = $this->semoaService->initializePayment([
                'amount' => (float) $request->montant,
                'description' => "Paiement Scolarité - " . $request->lastname . " " . $request->firstname,
                'lastname' => $request->lastname,
                'firstname' => $request->firstname,
                'phone' => $request->phone,
                'gateway_reference' => $request->payment_method, // On passe le mode de paiement choisi
                'success_url' => $frontendUrl . '/etudiant/mes-paiements?status=success',
                'cancel_url' => $frontendUrl . '/etudiant/mes-paiements?status=cancel',
            ]);

            // 3. Mettre à jour le paiement avec la référence SEMOA
            $orderReference = $semoaResponse['order_reference'] ?? null;
            if ($orderReference) {
                $paiement->update(['reference' => $orderReference]);
            }

            return response()->json([
                'success' => true,
                'payment_url' => $semoaResponse['long_bill_url'] ?? $semoaResponse['bill_url'] ?? null,
                'order_reference' => $orderReference
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
