<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>{{ $template->nom }}</title>
    <style>
        @media print {
            body { margin: 0; padding: 0; font-size: 12px; color: #000; }
            .page { page-break-after: always; }
            .page:last-child { page-break-after: auto; }
            .student-info, .ue-section, .summary-section, .signature-section { page-break-inside: avoid; }
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11px; color: #212121; background: #f9f9f9; margin: 10px; }
        .page { margin-bottom: 30px; display: flex; flex-direction: column; min-height: 1050px; background: white; border-radius: 8px; box-shadow: 0 4px 14px rgba(26, 35, 126, 0.15); padding: 20px 25px; box-sizing: border-box; }
        
        /* Header */
        .header { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 15px 20px; border-radius: 8px 8px 0 0; box-shadow: 0 3px 8px rgba(13, 21, 61, 0.5); flex-shrink: 0; margin-bottom: 20px;}
        .logo-ecole { height: 70px; margin-bottom: 8px; filter: drop-shadow(1px 1px 1px rgba(0, 0, 0, 0.15)); }
        .header-texts h1 { margin: 0; font-size: 26px; font-weight: 900; letter-spacing: 1.5px; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3); }
        .header-texts .devise { font-size: 14px; font-style: italic; opacity: 0.85; margin-top: 4px; font-weight: 600; }
        .header-texts .contact { font-size: 12px; opacity: 0.8; margin-top: 3px; font-weight: 500; letter-spacing: 0.4px; }
        .align-gauche { align-items: flex-start; text-align: left; flex-direction: row; }
        .align-gauche .logo-ecole { margin-right: 20px; margin-bottom: 0; }
        .align-droite { align-items: flex-end; text-align: right; flex-direction: row-reverse; }
        .align-droite .logo-ecole { margin-left: 20px; margin-bottom: 0; }

        /* Infos étudiant */
        .student-info { background-color: #0d153d; color: white; padding: 14px 25px; font-size: 13px; border-radius: 8px; flex-shrink: 0; margin-bottom: 25px; box-shadow: inset 0 -3px 8px rgba(25, 26, 73, 0.7); }
        .student-info table { width: 100%; border-spacing: 0 6px; border-collapse: separate; }
        .student-info td { vertical-align: middle; white-space: nowrap; font-weight: 600; }

        /* Document Title */
        .doc-title { margin: 0 auto 25px auto; text-align: center; width: 70%; border: 1.5px solid #000; padding: 12px; border-radius: 6px; background-color: #f4f4f4; }
        .doc-title h3 { font-size: 18px; text-transform: uppercase; margin: 0; letter-spacing: 1px; color: #000; }
        .doc-title p { font-size: 14px; margin-top: 6px; color: #444; margin-bottom: 0; }

        /* UE Section */
        .ue-section { border: 2px solid #0d153d; border-top: none; padding: 12px 8px; background: #fff; margin-bottom: 14px; flex-grow: 1; overflow-x: auto; box-shadow: 0 1px 8px rgba(13, 21, 61, 0.08); border-radius: 6px; }
        .ue-header { background-color: #0d153d; color: #fff; font-weight: 700; text-align: center; padding: 10px 0; margin-bottom: 12px; font-size: 15px; border-radius: 4px; letter-spacing: 0.7px; box-shadow: inset 0 -3px 5px rgba(0, 0, 0, 0.15); }
        
        table.grades-table { width: 100%; border-collapse: collapse; font-size: 11.5px; color: #222; }
        .grades-table th, .grades-table td { border: 1px solid #a9b7c4; padding: 7px 10px; }
        .grades-table th { background-color: #d9e8ff; text-transform: uppercase; font-weight: 700; letter-spacing: 0.1em; color: #1a237e; text-align: center;}
        .grades-table td { text-align: center; }
        .grades-table th:nth-child(1), .grades-table td:nth-child(1) { text-align: left; }
        .text-valid { color: #2c7a7b; font-weight: 700; }
        .text-non-valid { color: #bf3e3e; font-weight: 700; }

        /* Résumé Simplifié */
        .summary-section { background: #e7f0f4; border: 2px solid #89a5ad; border-radius: 8px; width: 50%; margin: 25px auto 0 auto; padding: 20px 25px; font-size: 13px; color: #22333b; box-shadow: 0 4px 12px rgba(67, 97, 113, 0.15); font-weight: 600; }
        .summary-section h6 { margin: 0 0 15px 0; font-weight: 900; font-size: 16px; text-align: center; color: #0f1a26cc; }
        .summary-table { width: 100%; border-collapse: separate; border-spacing: 0 7px; font-size: 13.5px; }
        .summary-table th, .summary-table td { padding: 10px 14px; border-radius: 6px; }
        .summary-table th { text-align: left; background-color: #f0f5f7; color: #557a89; box-shadow: inset 1px 1px 4px #d2e0e7; }
        .summary-table td { text-align: right; background-color: #e7f0f4; color: #2f4251; box-shadow: inset 1px 1px 3px #c6d3d9; }

        /* Signatures */
        .signature-section { margin-top: 40px; display: flex; justify-content: flex-end; }
        .signature-box { text-align: center; width: 250px; float: right; }
        .signature-box p { margin: 0 0 5px 0; font-size: 13px; }
        .signature-title { font-weight: bold; text-decoration: underline; margin-top: 20px !important; margin-bottom: 60px !important; }
        .signature-name { font-weight: 900; }
    </style>
</head>

<body>
    <div class="page">
        @foreach($layout as $block)
            
            {{-- HEADER --}}
            @if($block['type'] === 'header')
                @php
                    $alignClass = '';
                    if(isset($block['options']['logo_align'])) {
                        if($block['options']['logo_align'] === 'gauche') $alignClass = 'align-gauche';
                        if($block['options']['logo_align'] === 'droite') $alignClass = 'align-droite';
                    }
                @endphp
                <div class="header {{ $alignClass }}">
                    <div class="header-texts">
                        <h1>{{ strtoupper(\App\Helpers\ConfigHelper::getAppName() ?? 'INSTITUT') }}</h1>
                        @if(!isset($block['options']['show_slogan']) || $block['options']['show_slogan'])
                            <div class="devise">Excellence, Rigueur, Innovation</div>
                        @endif
                        <div class="contact">Tél : +228 90 00 00 00 | Email : contact@ispl.tg</div>
                    </div>
                </div>

                <div class="doc-title">
                    <h3><u><strong>{{ strtoupper($template->nom) }}</strong></u></h3>
                    @if (isset($releves['periode']))
                        <p>{{ $releves['periode'] }}</p>
                    @endif
                    @if (isset($releves['annee_scolaire']))
                        <p style="font-size: 13px; color: #555;">Année académique : {{ $releves['annee_scolaire'] }}</p>
                    @endif
                </div>
            @endif

            {{-- INFO ETUDIANT --}}
            @if($block['type'] === 'info_etudiant')
                <div class="student-info">
                    <table cellpadding="6" cellspacing="0" style="color: white; width: 100%;">
                        <tr>
                            <td><strong>Nom:</strong> {{ strtoupper($user['nom'] ?? '') }}</td>
                            <td><strong>Prénom:</strong> {{ $user['prenom'] ?? '' }}</td>
                            <td><strong>Sexe:</strong> {{ $user['genre'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Matricule:</strong> {{ $user['matricule'] ?? 'N/A' }}</td>
                            <td><strong>Filière:</strong> {{ $filiere ?? 'N/A' }}</td>
                            @if(isset($user['niveau']))
                                <td><strong>Niveau:</strong> {{ $user['niveau'] }}</td>
                            @endif
                        </tr>
                    </table>
                </div>
            @endif

            {{-- TABLEAU NOTES --}}
            @if($block['type'] === 'tableau_notes' || $block['type'] === 'tableau_notes_cumul')
                @php
                    $showCoef = $block['options']['show_coef'] ?? false;
                    $showAppreciation = $block['options']['show_appreciation'] ?? false;
                    $groupByUe = $block['options']['group_by_ue'] ?? true;
                @endphp

                @if($groupByUe && isset($releves['ues']))
                    @foreach ($releves['ues'] as $ueGroup)
                        <div class="ue-section">
                            <div class="ue-header">{{ $ueGroup['ue'] }} ({{ $ueGroup['credit'] ?? 0 }} crédits) — Moy. UE: {{ $ueGroup['moyenne_ue'] ?? '0.00' }}</div>
                            <table class="grades-table">
                                <thead>
                                    <tr>
                                        <th>Matière</th>
                                        <th>Devoir</th>
                                        <th>Examen</th>
                                        <th>Pondérations</th>
                                        <th>Moy. UV</th>
                                        @if($showCoef) <th>Coef.</th> @endif
                                        <th>Validation</th>
                                        @if($showAppreciation) <th>Appréciation</th> @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ueGroup['uvs'] as $uv)
                                        <tr>
                                            <td>{{ $uv['uv'] ?? $uv['nom'] ?? 'N/A' }}</td>
                                            <td>{{ $uv['devoir'] }}</td>
                                            <td>{{ $uv['examen'] }}</td>
                                            <td>{{ $uv['weights_label'] ?? ($uv['poids_devoir'] ?? 40).'/'.($uv['poids_examen'] ?? 60) }}</td>
                                            <td><strong>{{ $uv['moyenne_uv'] }}</strong></td>
                                            @if($showCoef) <td>{{ $uv['coefficient'] }}</td> @endif
                                            <td>
                                                @if (trim($uv['validation'] ?? '') === 'Validé')
                                                    <span class="text-valid">Validé</span>
                                                @else
                                                    <span class="text-non-valid">Non Validé</span>
                                                @endif
                                            </td>
                                            @if($showAppreciation) 
                                                <td>
                                                    @if(floatval($uv['moyenne_uv']) >= 16) Excellent
                                                    @elseif(floatval($uv['moyenne_uv']) >= 14) Très Bien
                                                    @elseif(floatval($uv['moyenne_uv']) >= 12) Bien
                                                    @elseif(floatval($uv['moyenne_uv']) >= 10) Passable
                                                    @else Insuffisant @endif
                                                </td> 
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                @else
                    {{-- Affichage simple (non groupé) --}}
                    <div class="ue-section">
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th>Matière</th>
                                    <th>Devoir</th>
                                    <th>Examen</th>
                                    <th>Moyenne</th>
                                    @if($showCoef) <th>Coef.</th> @endif
                                    <th>Validation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($releves['ues'] as $ueGroup)
                                    @foreach($ueGroup['uvs'] as $uv)
                                        <tr>
                                            <td>{{ $uv['uv'] ?? $uv['nom'] ?? 'N/A' }}</td>
                                            <td>{{ $uv['devoir'] }}</td>
                                            <td>{{ $uv['examen'] }}</td>
                                            <td><strong>{{ $uv['moyenne_uv'] }}</strong></td>
                                            @if($showCoef) <td>{{ $uv['coefficient'] }}</td> @endif
                                            <td>
                                                @if (trim($uv['validation'] ?? '') === 'Validé')
                                                    <span class="text-valid">Validé</span>
                                                @else
                                                    <span class="text-non-valid">Non Validé</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif

            {{-- BILAN --}}
            @if($block['type'] === 'bilan')
                <div class="summary-section">
                    <h6>Bilan Académique</h6>
                    <table class="summary-table">
                        <tbody>
                            <tr>
                                <th scope="row">Moyenne Générale</th>
                                <td><strong>{{ $releves['moyenne_generale'] ?? 'N/A' }} / 20</strong></td>
                            </tr>
                            <tr>
                                <th scope="row">Crédits Validés</th>
                                <td>{{ $releves['total_credits_valides'] ?? '0' }} / {{ ($releves['total_credits_valides'] ?? 0) + ($releves['total_credits_non_valides'] ?? 0) }}</td>
                            </tr>
                            @if(isset($block['options']['show_attendance']) && $block['options']['show_attendance'])
                                <tr>
                                    <th scope="row">Heures d'absence</th>
                                    <td>0 heures</td>
                                </tr>
                            @endif
                            <tr>
                                <th scope="row">Décision</th>
                                <td>
                                    @if (($releves['moyenne_generale'] ?? 0) >= 10)
                                        <span class="text-valid" style="font-size: 14px;">ADMIS</span>
                                    @else
                                        <span class="text-non-valid" style="font-size: 14px;">AJOURNÉ</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- SIGNATURES --}}
            @if($block['type'] === 'signatures')
                <div style="clear: both;"></div>
                <div class="signature-section">
                    <div class="signature-box">
                        <p>Fait à Lomé, le {{ date('d/m/Y') }}</p>
                        @if(isset($block['options']['sign_dir_etudes']) && $block['options']['sign_dir_etudes'])
                            <p class="signature-title">Le Directeur des Études</p>
                            <p class="signature-name">{{ \App\Helpers\ConfigHelper::getAppDe() ?? 'La Direction' }}</p>
                        @endif
                    </div>
                </div>
                <div style="clear: both;"></div>
            @endif

        @endforeach
    </div>
</body>

</html>
