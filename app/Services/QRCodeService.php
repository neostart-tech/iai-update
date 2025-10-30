<?php

namespace App\Services;

use App\Models\Etudiant;
use App\Models\Releve;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

class QRCodeService
{
    /**
     * Génère un code QR pour un relevé de notes avec données de vérification sécurisées
     */
    public function generateReleveQRCode(Etudiant $etudiant, array $releveData, string $filiere): string
    {
        // Génération d'un hash unique pour ce relevé
        $uniqueHash = $this->generateUniqueHash($etudiant, $releveData);
        
        // Création des données à encoder dans le QR
        $qrData = [
            'type' => 'releve_notes',
            'etudiant_id' => $etudiant->id,
            'nom_complet' => $etudiant->nom . ' ' . $etudiant->prenom,
            'filiere' => $filiere,
            'anne_academique' => $releveData['anne'],
            'periode' => $releveData['periode_nom'],
            'moyenne_generale' => $releveData['moyenne_generale'],
            'total_credits_valides' => $releveData['total_credits_valides'],
            'date_generation' => now()->format('Y-m-d H:i:s'),
            'hash' => $uniqueHash,
            'verification_url' => route('public.releve.verify.hash', ['hash' => $uniqueHash])
        ];

        // Conversion en JSON pour le QR Code
        $qrContent = json_encode($qrData);
        
        // Génération du QR Code en base64 pour intégration dans le PDF
        $qrCodeBase64 = base64_encode(
            QrCode::format('png')
                ->size(120)
                ->margin(1)
                ->errorCorrection('M')
                ->generate($qrContent)
        );

        // Sauvegarde du hash dans la base de données pour vérification ultérieure
        $this->saveVerificationData($uniqueHash, $etudiant, $releveData, $filiere);

        return 'data:image/png;base64,' . $qrCodeBase64;
    }

    /**
     * Génère un hash unique basé sur les données du relevé
     */
    private function generateUniqueHash(Etudiant $etudiant, array $releveData): string
    {
        $dataString = implode('|', [
            $etudiant->id,
            $etudiant->nom,
            $etudiant->prenom,
            $releveData['anne'],
            $releveData['periode_nom'],
            $releveData['moyenne_generale'],
            $releveData['total_credits_valides'],
            now()->timestamp
        ]);

        return hash('sha256', $dataString . config('app.key'));
    }

    /**
     * Sauvegarde les données de vérification dans la base de données
     */
    private function saveVerificationData(string $hash, Etudiant $etudiant, array $releveData, string $filiere): void
    {
        // Créer un enregistrement dans la table releves si pas déjà existant
        $existingReleve = Releve::where('etudiant_id', $etudiant->id)
            ->whereHas('periode', function($query) use ($releveData) {
                $query->where('nom', $releveData['periode_nom']);
            })
            ->first();

        if (!$existingReleve) {
            // Récupérer l'année scolaire active et la période
            $anneeScolaire = \App\Models\AnneeScolaire::where('active', true)->first();
            $periode = \App\Models\Periode::where('nom', $releveData['periode_nom'])->first();

            Releve::create([
                'etudiant_id' => $etudiant->id,
                'annee_scolaire_id' => $anneeScolaire?->id,
                'periode_id' => $periode?->id,
                'chemin_pdf' => '', // Sera mis à jour lors de la génération PDF
                'est_publie' => true,
                'date_publication' => now(),
                'qr_hash' => $hash,
                'verification_data' => json_encode([
                    'filiere' => $filiere,
                    'anne_academique' => $releveData['anne'],
                    'moyenne_generale' => $releveData['moyenne_generale'],
                    'total_credits_valides' => $releveData['total_credits_valides'],
                    'total_credits_non_valides' => $releveData['total_credits_non_valides'],
                    'date_generation' => now()->format('Y-m-d H:i:s')
                ])
            ]);
        } else {
            // Mettre à jour l'enregistrement existant avec les nouvelles données QR
            $existingReleve->update([
                'qr_hash' => $hash,
                'verification_data' => json_encode([
                    'filiere' => $filiere,
                    'anne_academique' => $releveData['anne'],
                    'moyenne_generale' => $releveData['moyenne_generale'],
                    'total_credits_valides' => $releveData['total_credits_valides'],
                    'total_credits_non_valides' => $releveData['total_credits_non_valides'],
                    'date_generation' => now()->format('Y-m-d H:i:s')
                ])
            ]);
        }
    }

    /**
     * Vérifie un hash QR et retourne les données du relevé si valide
     */
    public function verifyReleveQRHash(string $hash): ?array
    {
        $releve = Releve::where('qr_hash', $hash)->with(['etudiant', 'periode'])->first();

        if (!$releve || !$releve->est_publie) {
            return null;
        }

        $verificationData = json_decode($releve->verification_data, true);

        return [
            'valid' => true,
            'etudiant' => [
                'nom' => $releve->etudiant->nom,
                'prenom' => $releve->etudiant->prenom,
                'genre' => $releve->etudiant->genre,
            ],
            'filiere' => $verificationData['filiere'] ?? 'Non spécifiée',
            'anne_academique' => $verificationData['anne_academique'] ?? 'Non spécifiée',
            'periode' => $releve->periode->nom ?? 'Non spécifiée',
            'moyenne_generale' => $verificationData['moyenne_generale'] ?? '0.00',
            'total_credits_valides' => $verificationData['total_credits_valides'] ?? 0,
            'total_credits_non_valides' => $verificationData['total_credits_non_valides'] ?? 0,
            'date_generation' => $verificationData['date_generation'] ?? 'Non spécifiée',
            'date_publication' => $releve->date_publication?->format('d/m/Y H:i') ?? 'Non spécifiée'
        ];
    }
}