<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de Paiement</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8fafc;
        }
        .heading-font {
            font-family: 'Playfair Display', serif;
        }
        .receipt-container {
            max-width: 800px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-top: 4px solid #2563eb;
        }
        .divider {
            border-top: 1px dashed #cbd5e1;
            margin: 1.5rem 0;
        }
        .signature-line {
            border-top: 1px solid #94a3b8;
            display: inline-block;
            width: 200px;
        }
        .watermark {
            background-color: rgba(255,255,255,0.8);
        }
    </style>
</head>
<body class="bg-gray-50 py-12 px-4">
    <div class="mx-auto receipt-container bg-white rounded-lg overflow-hidden watermark">
        <!-- En-tête -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                  @php
                  $logoPath = storage_path('app/public/' . AppGetters::getAppLogo());
$logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
@endphp
                    <img src="{{ $logoBase64 }}" 
                         alt="Logo de l'université" 
                         class="h-16 w-16 mr-4 rounded-full border-2 border-white shadow-sm">
                    <div>
                        <h1 class="heading-font text-2xl font-bold text-gray-800">{{ AppGetters::getAppName() }}</h1>
                        <p class="text-sm text-gray-600">Enseignement Supérieur - Recherche - Innovation</p>
                    </div>
                </div>
                <div class="bg-white px-3 py-2 rounded shadow-sm text-center border">
                    <p class="text-xs font-semibold text-gray-500">N° Reçu</p>
                    <p class="text-blue-600 font-bold">REC-{{ $paiement->id }}</p>
                </div>
            </div>
        </div>

        <!-- Corps -->
        <div class="px-8 py-6">
            <h2 class="heading-font text-xl font-bold text-center mb-6 text-gray-800 border-b pb-2">REÇU DE PAIEMENT DE FRAIS DE SCOLARITÉ</h2>

            <!-- Infos Étudiant & Paiement (en ligne) -->
            <div class="flex flex-row justify-between gap-8 mb-6">
                <div class="w-1/2">
                    <h3 class="font-bold text-blue-700 mb-2">INFORMATIONS ÉTUDIANT</h3>
                    <table class="text-sm">
                        <tr><td class="py-1 pr-4 font-semibold text-gray-600">Nom & Prénom</td><td class="py-1">: {{ $etudiant->nom }} {{ $etudiant->prenom }}</td></tr>
                        <tr><td class="py-1 pr-4 font-semibold text-gray-600">Matricule</td><td class="py-1">: {{ $etudiant->matricule }}</td></tr>
                        <tr><td class="py-1 pr-4 font-semibold text-gray-600">Filière</td><td class="py-1">: {{ $candidature->filiere->code ?? '-' }}</td></tr>
                        <tr><td class="py-1 pr-4 font-semibold text-gray-600">Niveau</td><td class="py-1">: {{ $candidature->niveau->libelle ?? '-' }}</td></tr>
                    </table>
                </div>
                <div class="w-1/2">
                    <h3 class="font-bold text-blue-700 mb-2">DÉTAILS DU PAIEMENT</h3>
                    <table class="text-sm">
                        <tr><td class="py-1 pr-4 font-semibold text-gray-600">Date Paiement</td><td class="py-1">: {{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y H:i') }}</td></tr>
                        <tr><td class="py-1 pr-4 font-semibold text-gray-600">Mode Paiement</td><td class="py-1">: {{ ucfirst($paiement->mode_paiement) }}</td></tr>
                        <tr><td class="py-1 pr-4 font-semibold text-gray-600">Référence</td><td class="py-1">: {{ $paiement->reference ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>

            <!-- Détails du paiement -->
            <div class="mb-6">
                <h3 class="font-bold text-blue-700 mb-3">DÉTAIL DES FRAIS PAYÉS CE JOUR</h3>
                <table class="min-w-full bg-white text-sm">
                    <thead class="bg-gray-100 text-left text-gray-700">
                        <tr>
                            <th class="py-2 px-4 border">Tranche</th>
                            <th class="py-2 px-4 border text-right">Montant payé</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recap as $ligne)
                            <tr>
                                <td class="py-2 px-4 border">{{ $ligne['tranche'] }}</td>
                                <td class="py-2 px-4 border text-right text-green-600">
                                    {{ number_format($ligne['montant'], 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td class="py-2 px-4 border">TOTAL</td>
                            <td class="py-2 px-4 border text-right text-green-600">
                                {{ number_format(collect($recap)->sum('montant'), 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Récap global -->
            <div class="mb-6">
                <h3 class="font-bold text-blue-700 mb-3">SITUATION GLOBALE</h3>
                <table class="min-w-full bg-white text-sm">
                    <tbody>
                        <tr>
                            <td class="py-2 px-4 border font-semibold text-gray-700">Montant total de la scolarité</td>
                            <td class="py-2 px-4 border text-right text-gray-900">
                                {{ number_format($montantTotalScolarite, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 px-4 border font-semibold text-gray-700">Montant déjà payé</td>
                            <td class="py-2 px-4 border text-right text-green-700">
                                {{ number_format($montantDejaPaye, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                        <tr>
                            <td class="py-2 px-4 border font-semibold text-gray-700">Reste à payer</td>
                            <td class="py-2 px-4 border text-right text-red-600">
                                {{ number_format($resteGlobal, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Historique -->
            <div class="mb-6">
                <h3 class="font-bold text-blue-700 mb-3">HISTORIQUE DES PAIEMENTS</h3>
                <table class="min-w-full bg-white text-sm">
                    <thead class="bg-gray-100 text-left text-gray-700">
                        <tr>
                            <th class="py-2 px-4 border">Date</th>
                            <th class="py-2 px-4 border">Référence</th>
                            <th class="py-2 px-4 border text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($paiementsAnterieurs as $p)
                            <tr>
                                <td class="py-2 px-4 border">{{ \Carbon\Carbon::parse($p->date_paiement)->format('d/m/Y') }}</td>
                                <td class="py-2 px-4 border">{{ $p->reference ?? '-' }}</td>
                                <td class="py-2 px-4 border text-right">
                                    {{ number_format($p->montant, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
