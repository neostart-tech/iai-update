<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        .page {
            padding: 15mm 0;
            page-break-after: always;
            text-align: center;
        }
        .card {
            width: 86mm;
            height: 54mm;
            margin: 0 auto;
            border: 0.3mm solid #1e293b;
            border-radius: 5mm;
            overflow: hidden;
            background: #ffffff;
            position: relative;
        }
        /* Bandeau Bleu Supérieur */
        .header-bar {
            background-color: #1e293b;
            height: 14mm;
            color: #ffffff;
            width: 100%;
        }
        .header-table {
            width: 100%;
            border-spacing: 0;
            padding: 2mm 3mm;
        }
        .logo-td {
            width: 12mm;
            vertical-align: middle;
        }
        .logo-img {
            width: 10mm;
            height: 10mm;
            background-color: #ffffff;
            border-radius: 2mm;
        }
        .school-info-td {
            padding-left: 3mm;
            vertical-align: middle;
            text-align: left;
        }
        .school-name {
            font-size: 3.5mm;
            font-weight: bold;
            text-transform: uppercase;
        }
        .card-type {
            font-size: 2mm;
            color: #94a3b8;
            letter-spacing: 1mm;
        }

        /* Corps de la carte */
        .content-table {
            width: 100%;
            border-spacing: 0;
            padding: 3mm;
        }
        .photo-td {
            width: 24mm;
            vertical-align: top;
        }
        .photo-box {
            width: 22mm;
            height: 28mm;
            border: 0.5mm solid #1e293b;
            border-radius: 2mm;
            overflow: hidden;
        }
        .info-td {
            padding-left: 4mm;
            vertical-align: top;
            text-align: left;
        }
        .info-row {
            margin-bottom: 2mm;
        }
        .label {
            font-size: 1.8mm;
            color: #64748b;
            font-weight: bold;
            display: block;
        }
        .value {
            font-size: 3mm;
            font-weight: bold;
            color: #0f172a;
            display: block;
            text-transform: uppercase;
        }
        .matricule-value {
            color: #d97706; /* Couleur ambre pour le matricule */
            font-size: 3.5mm;
        }

        /* QR Code */
        .qr-td {
            width: 15mm;
            vertical-align: bottom;
            text-align: right;
        }
        .qr-box {
            width: 14mm;
            height: 14mm;
            border: 0.1mm solid #ddd;
            padding: 1mm;
        }

        /* Pied de page */
        .footer-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #1e293b;
            color: #ffffff;
            font-size: 2mm;
            font-weight: bold;
            padding: 1.5mm 0;
            text-align: center;
        }

        /* VERSO */
        .verso-card {
            margin-top: 10mm;
            border-color: #e2e8f0;
        }
        .verso-header {
            background-color: #1e293b;
            color: #ffffff;
            padding: 2.5mm;
            font-weight: bold;
            font-size: 3mm;
            text-align: center;
        }
        .verso-body {
            padding: 4mm;
            text-align: left;
        }
        .verso-body ul {
            margin: 0;
            padding-left: 5mm;
            font-size: 2.2mm;
            color: #334155;
        }
        .verso-body li {
            margin-bottom: 1.5mm;
        }
        .signature-section {
            margin-top: 4mm;
            text-align: right;
            padding-right: 5mm;
        }
        .sig-title {
            font-size: 2.2mm;
            font-weight: bold;
            margin-bottom: 7mm;
        }
        .sig-line {
            width: 35mm;
            border-top: 0.2mm solid #1e293b;
            margin-left: auto;
        }
        .verso-footer {
            position: absolute;
            bottom: 3mm;
            width: 100%;
            font-size: 1.8mm;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    @foreach($etudiants as $card)
    <div class="page">
        <!-- RECTO -->
        <div class="card">
            <div class="header-bar">
                <table class="header-table">
                    <tr>
                        <td class="logo-td">
                            @if($logo)
                                <img src="{{ $logo }}" class="logo-img" />
                            @else
                                <div class="logo-img" style="background:#eee; text-align:center; padding-top:4mm; font-size:1.5mm; color:#666;">LOGO</div>
                            @endif
                        </td>
                        <td class="school-info-td">
                            <div class="school-name">{{ $appName }}</div>
                            <div class="card-type">CARTE D'ÉTUDIANT</div>
                        </td>
                    </tr>
                </table>
            </div>

            <table class="content-table">
                <tr>
                    <td class="photo-td">
                        <div class="photo-box">
                            @if($card['image_url'])
                                <img src="{{ $card['image_url'] }}" style="width:100%; height:100%;" />
                            @else
                                <div style="padding-top:12mm; font-size:2mm; color:#ccc; text-align:center;">PHOTO</div>
                            @endif
                        </div>
                    </td>
                    <td class="info-td">
                        <div class="info-row">
                            <span class="label">NOM & PRÉNOMS</span>
                            <span class="value">{{ $card['nom_complet'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">MATRICULE</span>
                            <span class="value matricule-value">{{ $card['matricule'] }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">FILIÈRE</span>
                            <span class="value" style="font-size:2.5mm;">{{ $card['filiere'] }}</span>
                        </div>
                    </td>
                    <td class="qr-td">
                        <div class="qr-box">
                            <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(100)->margin(0)->generate($card['qr_data'])) !!}" style="width:100%; height:100%;">
                        </div>
                    </td>
                </tr>
            </table>

            <div class="footer-bar">
                ANNÉE ACADÉMIQUE : {{ $card['promotion'] }}
            </div>
        </div>

        <!-- VERSO -->
        <div class="card verso-card">
            <div class="verso-header">CONDITIONS D'UTILISATION</div>
            <div class="verso-body">
                <ul>
                    <li>Cette carte est strictement personnelle et incessible.</li>
                    <li>Elle doit être présentée lors de tout contrôle administratif.</li>
                    <li>En cas de perte, veuillez informer l'administration.</li>
                    <li>Toute falsification expose à des sanctions disciplinaires.</li>
                </ul>
                
                <div class="signature-section">
                    <div class="sig-title">Le Directeur Général</div>
                    <div class="sig-line"></div>
                </div>
            </div>
            <div class="verso-footer">
                BP 12471 Lomé - Togo | Tél: +228 22 20 47 00<br/>www.iai-togo.tg
            </div>
        </div>
    </div>
    @endforeach
</body>
</html>
