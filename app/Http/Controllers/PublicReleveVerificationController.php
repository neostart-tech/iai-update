<?php

namespace App\Http\Controllers;

use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PublicReleveVerificationController extends Controller
{
    private $qrCodeService;

    public function __construct()
    {
        $this->qrCodeService = new QRCodeService();
    }

    /**
     * Affiche la page de vérification des relevés de notes
     */
    public function index()
    {
        return view('public.releve-verification.index');
    }

    /**
     * Vérifie un hash QR Code et affiche les résultats
     */
    public function verify(Request $request, $hash = null)
    {
        // Si le hash est fourni via URL (scan direct du QR)
        if ($hash) {
            $verificationData = $this->qrCodeService->verifyReleveQRHash($hash);
            
            if ($verificationData) {
                return view('public.releve-verification.result', [
                    'success' => true,
                    'data' => $verificationData
                ]);
            } else {
                return view('public.releve-verification.result', [
                    'success' => false,
                    'error' => 'Code QR invalide ou relevé non trouvé.'
                ]);
            }
        }

        // Si le hash est fourni via formulaire
        $validator = Validator::make($request->all(), [
            'qr_hash' => 'required|string|min:10'
        ], [
            'qr_hash.required' => 'Le code de vérification est requis.',
            'qr_hash.min' => 'Le code de vérification doit contenir au moins 10 caractères.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $verificationData = $this->qrCodeService->verifyReleveQRHash($request->qr_hash);

        if ($verificationData) {
            return view('public.releve-verification.result', [
                'success' => true,
                'data' => $verificationData
            ]);
        } else {
            return redirect()->back()
                ->withErrors(['qr_hash' => 'Code de vérification invalide ou relevé non trouvé.'])
                ->withInput();
        }
    }

    /**
     * API endpoint pour vérifier un QR code (pour applications mobiles)
     */
    public function verifyApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_hash' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Code de vérification requis.',
                'errors' => $validator->errors()
            ], 400);
        }

        $verificationData = $this->qrCodeService->verifyReleveQRHash($request->qr_hash);

        if ($verificationData) {
            return response()->json([
                'success' => true,
                'message' => 'Relevé de notes vérifié avec succès.',
                'data' => $verificationData
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Code de vérification invalide ou relevé non trouvé.'
            ], 404);
        }
    }
}