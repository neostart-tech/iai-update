<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Génération des Cartes d'Étudiants</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .card-size { width: 96mm; height: 62mm; }
        .page-break { page-break-after: always; }
        
        .pattern-dots {
            background-color: #ffffff;
            background-image: radial-gradient(#cbd5e1 0.6px, transparent 0.6px);
            background-size: 10px 10px;
        }

        .pattern-circles {
            background-color: #1e1b4b;
            background-image: 
                radial-gradient(circle at 2px 2px, rgba(255,255,255,0.05) 1px, transparent 0),
                repeating-linear-gradient(45deg, rgba(255,255,255,0.03) 0, rgba(255,255,255,0.03) 0.5px, transparent 0, transparent 50%);
            background-size: 20px 20px, 15px 15px;
        }

        /* Pill shape for the photo */
        .photo-pill {
            width: 18mm;
            height: 32mm;
            border-radius: 9999px;
            overflow: hidden;
            background: #eef2ff;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-8">

    <div class="fixed top-4 right-4 z-50 no-print flex gap-2">
        <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg shadow font-semibold transition-all">
            Fermer
        </button>
        <button onclick="generatePDF()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg shadow-lg font-semibold transition-all">
            Télécharger le PDF
        </button>
    </div>

    <div id="rendering-zone" class="flex flex-col items-center gap-12">
        @foreach($etudiants as $card)
        <div class="flex flex-col gap-8 page-break">
            <!-- RECTO (EXACT COPY OF SCREENSHOTS WITH LARGER HEIGHT & ENHANCED SPACING) -->
            <div class="card-size relative overflow-hidden rounded-[4mm] shadow-2xl bg-white flex flex-col border border-slate-200">
                <!-- Header -->
                <div style="background-color: #26215c; height: 17mm; display: flex; align-items: center; padding: 0 5mm; color: #ffffff; position: relative;">
                    <div style="width: 10mm; height: 10mm; background-color: #1a1640; border-radius: 5px; display: flex; align-items: center; justify-content: center; margin-right: 4mm; overflow: hidden; flex-shrink: 0;">
                        @if($logo)
                            <img src="{{ $logo }}" class="w-full h-full object-contain" alt="Logo">
                        @else
                            <div class="text-[8px] text-white font-bold">LOGO</div>
                        @endif
                    </div>
                    <div class="flex-1" style="flex: 1; display: flex; flex-direction: column; justify-content: center; min-width: 0; gap: 3px;">
                        <h1 style="font-size: 8pt; font-weight: 800; color: #ffffff; text-transform: uppercase; line-height: 1.15; padding-right: 26mm; margin: 0;">
                            ÉCOLE SUPÉRIEURE DE COMMERCE ET D'ÉCONOMIE NUMÉRIQUE (ESCEN)
                        </h1>
                        <p style="font-size: 6pt; color: #a5b4fc; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin: 0;">Carte d'étudiant</p>
                    </div>
                    <!-- Year Badge (Pill) -->
                    <div style="position: absolute; right: 5mm; top: 50%; transform: translateY(-50%); border: 1px solid rgba(255,255,255,0.3); background-color: rgba(255,255,255,0.15); border-radius: 14px; height: 7mm; display: inline-flex; align-items: center; justify-content: center; padding: 0 10px; box-sizing: border-box;">
                        <span style="font-size: 7.5pt; font-weight: 800; color: #ffffff; letter-spacing: 0.5px; line-height: 1; display: inline-block; text-align: center;">{{ $card['promotion'] }}</span>
                    </div>
                </div>

                <!-- Body (Dotted) -->
                <div class="flex-1 pattern-dots relative" style="flex: 1; padding: 3mm 4mm; display: flex; align-items: center; gap: 5mm; background-color: #ffffff;">
                    <!-- Photo Capsule -->
                    <div style="width: 20mm; height: 25mm; background-color: #f8fafc; border-radius: 6px; overflow: hidden; border: 1px solid #eee; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        @if($card['image_url'])
                            <img src="{{ $card['image_url'] }}" class="w-full h-full object-cover">
                        @else
                            <span style="color: #8a82d4; font-weight: bold; font-size: 16pt;">
                                {{ substr($card['nom_complet'], 0, 1) }}
                            </span>
                        @endif
                    </div>

                    <!-- Infos -->
                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between; height: 25mm; py: 1px; line-height: 1.2;">
                        <div>
                            <p style="font-size: 5.5pt; font-weight: 700; color: #94a3b8; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Nom & Prénoms</p>
                            <p style="font-size: 9.5pt; font-weight: 800; color: #1e1b4b; text-transform: uppercase; margin: 0; margin-top: 1px;">{{ $card['nom_complet'] }}</p>
                        </div>
                        <div>
                            <p style="font-size: 5.5pt; font-weight: 700; color: #94a3b8; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Matricule</p>
                            <p style="font-size: 9.5pt; font-weight: 800; color: #534ab7; font-family: monospace; margin: 0; margin-top: 1px;">{{ $card['matricule'] }}</p>
                        </div>
                        <div>
                            <p style="font-size: 5.5pt; font-weight: 700; color: #94a3b8; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Filière / Niveau</p>
                            <p style="font-size: 8.5pt; font-weight: 800; color: #1e1b4b; margin: 0; margin-top: 1px;">{{ $card['filiere'] }} ({{ $card['niveau'] }})</p>
                        </div>
                    </div>
                </div>

                <!-- Footer (Gray/White Bar) -->
                <div style="height: 10mm; background-color: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; align-items: center; padding: 0 4mm;">
                    <div style="display: flex; align-items: center; gap: 3mm; width: 100%;">
                        <div style="width: 8mm; height: 8mm; background-color: #ffffff; padding: 1px; border: 1px solid #e2e8f0; border-radius: 2px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(100)->margin(0)->generate($card['qr_data'])) !!}" style="width: 100%; height: 100%;">
                        </div>
                        <p style="font-size: 7pt; color: #94a3b8; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: 0.3px;">
                            Valide pour l'année scolaire {{ $card['promotion'] }}
                        </p>
                        <div style="margin-left: auto; display: flex; gap: 4px; flex-shrink: 0;">
                            <div style="width: 5px; height: 5px; background-color: #e0e7ff; border-radius: 50%;"></div>
                            <div style="width: 5px; height: 5px; background-color: #818cf8; border-radius: 50%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VERSO -->
            <div class="card-size relative overflow-hidden rounded-[4mm] shadow-2xl bg-[#1e1b4b] text-white flex flex-col border border-indigo-900">
                <!-- Background Pattern -->
                <div class="absolute inset-0 pattern-circles opacity-60"></div>
                
                <div class="relative z-10 flex-1 flex flex-col items-center animate-fade-in" style="padding: 5mm 6mm 2mm;">
                    <!-- Title -->
                    <div class="flex items-center gap-4" style="display: flex; align-items: center; gap: 4px; margin-bottom: 2mm;">
                        <div class="w-2 h-2 bg-indigo-500 rounded-full shadow-[0_0_8px_rgba(99,102,241,0.8)]"></div>
                        <h2 class="text-[10px] font-black tracking-[0.3em] uppercase text-indigo-100" style="margin: 0;">Conditions d'utilisation</h2>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full shadow-[0_0_8px_rgba(99,102,241,0.8)]"></div>
                    </div>
                    <div style="width: 25mm; height: 1px; background-color: #3d3694; margin-bottom: 3mm;"></div>
 
                    <!-- List -->
                    <ul class="text-[9px] text-indigo-50 font-semibold max-w-[95%]" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 5px; line-height: 1.4;">
                        <li class="flex items-start" style="position: relative; padding-left: 4mm;">
                            <span style="position: absolute; left: 0; top: 1px; color: #818cf8; font-size: 10pt; line-height: 1;">•</span>
                            <span>Cette carte est strictement personnelle et incessible.</span>
                        </li>
                        <li class="flex items-start" style="position: relative; padding-left: 4mm;">
                            <span style="position: absolute; left: 0; top: 1px; color: #818cf8; font-size: 10pt; line-height: 1;">•</span>
                            <span>Elle doit être présentée lors de tout contrôle administratif ou académique.</span>
                        </li>
                        <li class="flex items-start" style="position: relative; padding-left: 4mm;">
                            <span style="position: absolute; left: 0; top: 1px; color: #818cf8; font-size: 10pt; line-height: 1;">•</span>
                            <span>En cas de perte, l'étudiant doit informer l'administration sans délai.</span>
                        </li>
                        <li class="flex items-start" style="position: relative; padding-left: 4mm;">
                            <span style="position: absolute; left: 0; top: 1px; color: #818cf8; font-size: 10pt; line-height: 1;">•</span>
                            <span>Toute falsification expose son auteur à des sanctions disciplinaires.</span>
                        </li>
                    </ul>
                </div>
 
                <!-- Footer -->
                <div class="relative z-10 flex justify-between items-end" style="padding: 0 6mm 4mm; display: flex; justify-content: space-between; align-items: flex-end;">
                    <div class="text-left" style="text-align: left;">
                        <p class="text-[8.5px] font-black text-indigo-200 uppercase tracking-widest" style="margin: 0; margin-bottom: 2px;">Le Directeur Général</p>
                        <div style="width: 20mm; height: 1px; background-color: #3d3694;"></div>
                    </div>
                    <div class="text-right" style="text-align: right;">
                        <p class="text-[7px] text-indigo-300 font-bold opacity-80 uppercase tracking-widest" style="margin: 0;">BP 12471 Lomé - Togo</p>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <script>
        function generatePDF() {
            const element = document.getElementById('rendering-zone');
            const opt = {
                margin: [10, 0, 10, 0],
                filename: 'cartes_etudiants_' + Date.now() + '.pdf',
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { 
                    scale: 3, 
                    useCORS: true, 
                    letterRendering: true,
                    backgroundColor: '#f3f4f6'
                },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save();
        }

        window.onload = function() {
            setTimeout(generatePDF, 2500);
        };
    </script>
</body>
</html>
