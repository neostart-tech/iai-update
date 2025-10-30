<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>Relevé de Notes</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 12px;
                color: #000;
            }

            .page {
                page-break-after: always;
            }

            .page:last-child {
                page-break-after: auto;
            }

            .student-info,
            .ue-section {
                page-break-inside: avoid;
            }
        }

        /* Général */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #212121;
            background: #f9f9f9;
            margin: 10px;
        }

        .page {
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            min-height: 1050px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(26, 35, 126, 0.15);
            padding: 20px 25px;
            box-sizing: border-box;
        }

        /* Header */
        .header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            /* background-color: #0d153d; */
            /* color: white; */
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 3px 8px rgba(13, 21, 61, 0.5);
            flex-shrink: 0;
        }

        .logo-ecole {
            height: 70px;
            margin-bottom: 8px;
            filter: drop-shadow(1px 1px 1px rgba(0, 0, 0, 0.15));
        }

        .header-texts h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 1.5px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .header-texts .devise {
            font-size: 14px;
            font-style: italic;
            opacity: 0.85;
            margin-top: 4px;
            font-weight: 600;
        }

        .header-texts .contact {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 3px;
            font-weight: 500;
            letter-spacing: 0.4px;
        }

        /* Infos étudiant */
        .student-info {
            background-color: #0d153d;
            color: white;
            padding: 14px 25px;
            font-size: 13px;
            border-radius: 0 0 8px 8px;
            flex-shrink: 0;
            margin-bottom: 15px;
            box-shadow: inset 0 -3px 8px rgba(25, 26, 73, 0.7);
        }

        .student-info table {
            width: 100%;
            border-spacing: 0 6px;
            border-collapse: separate;
        }

        .student-info td {
            vertical-align: middle;
            white-space: nowrap;
            font-weight: 600;
        }

        /* UE Section */
        .ue-section {
            border: 2px solid #0d153d;
            border-top: none;
            padding: 12px 8px;
            background: #fff;
            margin-bottom: 14px;
            flex-grow: 1;
            overflow-x: auto;
            box-shadow: 0 1px 8px rgba(13, 21, 61, 0.08);
            border-radius: 6px;
        }

        .ue-header {
            background-color: #0d153d;
            color: #fff;
            font-weight: 700;
            text-align: center;
            padding: 10px 0;
            margin-bottom: 12px;
            font-size: 15px;
            border-radius: 4px;
            letter-spacing: 0.7px;
            user-select: none;
            box-shadow: inset 0 -3px 5px rgba(0, 0, 0, 0.15);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            color: #222;
        }

        th,
        td {
            border: 1px solid #a9b7c4;
            padding: 7px 10px;
            user-select: none;
        }

        th:nth-child(1),
        td:nth-child(1) {
            text-align: left;
        }

        th:not(:first-child),
        td:not(:first-child) {
            text-align: right;
        }

        th {
            background-color: #d9e8ff;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: #1a237e;
        }

        .text-valid {
            color: #2c7a7b;
            font-weight: 700;
        }

        .text-non-valid {
            color: #bf3e3e;
            font-weight: 700;
        }

        /* Résumé Simplifié */
        .summary-section {
            background: #e7f0f4;
            border: 2px solid #89a5ad;
            border-radius: 8px;
            width: 40%;
            margin: 25px auto 0 auto;
            padding: 20px 25px;
            font-size: 13px;
            color: #22333b;
            box-shadow: 0 4px 12px rgba(67, 97, 113, 0.15);
            font-weight: 600;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            letter-spacing: 0.02em;
            user-select: none;
        }

        .summary-section h6 {
            margin: 0 0 15px 0;
            font-weight: 900;
            font-size: 16px;
            text-align: center;
            color: #0f1a26cc;
            text-shadow: 0 1px 2px rgba(255 255 255 / 0.6);
            user-select: none;
        }

        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 7px;
            font-size: 13.5px;
            color: #37474f;
        }

        .summary-table th,
        .summary-table td {
            padding: 10px 14px;
            border-radius: 6px;
            user-select: none;
        }

        .summary-table th {
            text-align: left;
            background-color: #f0f5f7;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #557a89;
            box-shadow: inset 1px 1px 4px #d2e0e7;
            transition: background-color 0.3s ease;
        }

        .summary-table td {
            text-align: right;
            background-color: #e7f0f4;
            font-weight: 700;
            box-shadow: inset 1px 1px 3px #c6d3d9;
            color: #2f4251;
            transition: background-color 0.3s ease;
        }

        .summary-table tr:hover td {
            background-color: #d3e2e8;
        }

        /* Signature - stacked vertically, left aligned */
        .signature-name {
            font-weight: 800;
            letter-spacing: 0.05em;
        }

        .signature-person {
            font-weight: 600;
            font-size: 12px;
            color: #455a64;
        }

        /* Responsive adjustments */
        @media (max-width: 720px) {

            .summary-section,
            .signature-section {
                width: 90%;
                margin-left: auto;
                margin-right: auto;
                text-align: left;
            }
        }
    </style>
</head>

<body>
   
        <div class="page">
            <div class="header">
                @php
                    $logoPath = public_path('storage/' . AppGetters::getAppLogo());
                @endphp

                <img src="{{ $logoPath }}" alt="Logo École"
                    style="width: 100px; margin-right: 20px;margin-bottom:10px;" class="logo-ecole" />
                <div class="header-texts">
                    <div class="devise">Excellence, Rigueur, Innovation</div>
                    <div class="contact">Tél : +228 90 00 00 00 | Email : contact@ispl.tg</div>
                </div>
            </div>

            <!-- INFOS ÉTUDIANT -->
            <div class="student-info" style="margin-top: 20px;">
                <table cellpadding="6" cellspacing="0" style="color: white; width: 100%;">
                    <tr>
                        <td><strong>Nom:</strong> {{ strtoupper($user['nom']) }}</td>
                        <td><strong>Prénom:</strong> {{ $user['prenom'] }}</td>
                        <td><strong>Sexe:</strong> {{ $user['genre'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Période:</strong> {{ $releves['periode_nom']}}</td>
                        <td><strong>Année académique:</strong> {{ $releves['anne'] }}</td>
                        <td><strong>Filière:</strong> {{$filiere}}</td>
                    </tr>
                </table>
            </div>

            <!-- TITRE CENTRAL ENCADRÉ (version compacte) -->
            <div style="margin: 30px auto 20px auto; text-align: center; width: 70%;">
                <div
                    style="
        border: 1.5px solid #000;
        padding: 12px;
        border-radius: 6px;
        background-color: #f4f4f4;
    ">
                    <h3
                        style="
            font-size: 18px;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 1px;
            color: #000;
        ">
                        <u><strong>RELEVÉ DE NOTES</strong></u>
                    </h3>

                    @if (isset($releves['semestre']))
                        <p style="font-size: 14px; margin-top: 6px; color: #444;">
                            Semestre {{ $releves['semestre'] }}
                        </p>
                    @endif

                    @if (isset($releves['anne']))
                        <p style="font-size: 13px; margin-top: 3px; color: #555;">
                            Année académique : {{ $releves['anne'] }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- {{dd($releves)}} --}}
            <!-- NOTES -->
            @foreach ($releves['releve_grouped'] as $ueNom => $uvs)
                <div class="ue-section">
                    <div class="ue-header">{{ $ueNom }} ({{ $uvs[0]['credit'] ?? 0 }} crédits) — Moy. UE: {{ $uvs[0]['moyenne_ue'] ?? '0.00' }}</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Devoir</th>
                                <th>Interro</th>
                                <th>Examen</th>
                                <th>TP</th>
                                <th>Exposé</th>
                                <th>Pondérations</th>
                                <th>Moy. UV</th>
                                <th>Coef.</th>
                                <th>Validation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($uvs as $uv)
                                <tr>
                                   <td>{{ $uv['uv'] }}</td>
                                     <td>{{ $uv['devoir'] }}</td>
                                    <td>{{ $uv['interrogation'] }}</td>
                                    <td>{{ $uv['examen'] }}</td>
                                    <td>{{ $uv['tp'] ?? '0.00' }}</td>
                                    <td>{{ $uv['expose'] ?? '0.00' }}</td>
                                    <td>{{ $uv['weights_label'] ?? '30/10/60' }}</td>
                                    <td>{{ $uv['moyenne_uv'] }}</td>
                                    <td>{{ $uv['coefficient'] }}</td>
                                    <td>
                                        @if ($uv['validation'] === 'Validé')
                                            <span class="text-valid">Validé</span>
                                        @else
                                            <span class="text-non-valid">Non Validé</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

          
            <div class="summary-section" aria-label="Résumé du semestre">
                <h6>Résumé du semestre</h6>
                <table class="summary-table" role="table" aria-describedby="resume-summary-left">
                    <tbody>
                        <tr>
                            <th scope="row">Moyenne Générale</th>
                            <td>{{ $releves['moyenne_generale'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Total Crédits Validés</th>
                            <td>{{ $releves['total_credits_valides'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Total Crédits Non Validés</th>
                            <td>{{$releves['total_credits_non_valides'] }}</td>
                        </tr>
                        <tr>
                            <th scope="row">Heures d'absence</th>
                            <td>15 heures</td>
                        </tr>
                        <tr>
                            <th scope="row">Appréciation</th>
                            <td>
                                @if ($releves['moyenne_generale'] >= 16)
                                    Excellent
                                @elseif ($releves['moyenne_generale'] >= 14)
                                    Très Bien
                                @elseif ($releves['moyenne_generale'] >= 12)
                                    Bien
                                @elseif ($releves['moyenne_generale'] >= 10)
                                    Assez Bien
                                @else
                                    Insuffisant
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

           
            <div style="width: 100%; margin-top: 80px;">
                <div style="float: right; text-align: right;">
                    <p style="margin: 0; font-size: 14px;"><strong>Fait à Lomé, le <?php echo date('d/m/Y'); ?></strong></p>
                    <p style="margin: 30px 0 0 0; font-size: 14px;"><strong>{{AppGetters::getAppTitreDe()}}</strong></p>
                    <p style="margin: 2px 0 0 0; font-size: 14px;"><strong><u>{{AppGetters::getAppDe()}}</u></strong></p>
                </div>
            </div>
            <div style="clear: both;"></div>
          
        </div>
   
    

</body>

</html>
