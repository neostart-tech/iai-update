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
        .card-size { width: 86mm; height: 54mm; }
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
            <!-- RECTO ( EXACT COPY OF SCREENSHOTS) -->
            <div class="card-size relative overflow-hidden rounded-[4mm] shadow-2xl bg-white flex flex-col border border-slate-200">
                <!-- Header -->
                <div class="h-16 bg-[#26215c] relative flex items-center px-4 gap-4">
                    <div class="w-12 h-12 bg-black/30 rounded-xl p-1.5 border border-white/10 flex items-center justify-center shrink-0">
                        @if($logo)
                            <img src="{{ $logo }}" class="w-full h-full object-contain" alt="Logo">
                        @else
                            <div class="text-[8px] text-white font-bold">LOGO</div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <h1 class="text-[10px] font-extrabold text-white uppercase leading-[1.1] pr-16">
                            ÉCOLE SUPÉRIEURE DE COMMERCE ET D'ÉCONOMIE NUMÉRIQUE (ESCEN)
                        </h1>
                        <p class="text-[8px] text-indigo-300 font-bold uppercase tracking-[0.2em] mt-0.5">Carte d'étudiant</p>
                    </div>
                    <!-- Year Badge (Pill) -->
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 border border-white/30 bg-white/10 rounded-full px-4 py-1.5">
                        <span class="text-[9px] font-extrabold text-white tracking-wider">{{ $card['promotion'] }}</span>
                    </div>
                </div>

                <!-- Body (Dotted) -->
                <div class="flex-1 pattern-dots relative flex items-center px-6 py-4 gap-6">
                    <!-- Photo Capsule (Vertical Pill) -->
                    <div class="photo-pill flex items-center justify-center shrink-0">
                        @if($card['image_url'])
                            <img src="{{ $card['image_url'] }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-[#8a82d4] font-bold text-2xl tracking-tighter opacity-80">
                                {{ substr($card['nom_complet'], 0, 2) }}
                            </span>
                        @endif
                    </div>

                    <!-- Infos -->
                    <div class="flex-1 flex flex-col justify-center space-y-3">
                        <div>
                            <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest">Nom & Prénoms</p>
                            <p class="text-[13px] font-black text-[#1e1b4b] uppercase leading-tight">{{ $card['nom_complet'] }}</p>
                        </div>
                        <div>
                            <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest">Matricule</p>
                            <p class="text-[13px] font-extrabold text-[#534ab7] font-mono">{{ $card['matricule'] }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest">Filière</p>
                                <p class="text-[10px] font-black text-slate-800 truncate">{{ $card['filiere'] }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest">Niveau</p>
                                <p class="text-[10px] font-black text-slate-800">{{ $card['niveau'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer (Gray/White Bar) -->
                <div class="h-12 bg-slate-50 border-t border-slate-100 flex items-center px-5 justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 bg-white p-1 rounded-lg border border-slate-200 shadow-sm">
                            <img src="data:image/svg+xml;base64, {!! base64_encode(QrCode::format('svg')->size(100)->margin(0)->generate($card['qr_data'])) !!}" class="w-full h-full">
                        </div>
                        <p class="text-[8px] font-extrabold text-slate-400 uppercase tracking-widest">
                            Valide pour l'année scolaire {{ $card['promotion'] }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <div class="w-2.5 h-2.5 bg-indigo-200 rounded-full border border-indigo-300"></div>
                        <div class="w-2.5 h-2.5 bg-indigo-500 rounded-full border border-indigo-600"></div>
                    </div>
                </div>
            </div>

            <!-- VERSO (EXACT COPY) -->
            <div class="card-size relative overflow-hidden rounded-[4mm] shadow-2xl bg-[#1e1b4b] text-white flex flex-col border border-indigo-900">
                <!-- Background Pattern -->
                <div class="absolute inset-0 pattern-circles opacity-60"></div>
                
                <div class="relative z-10 flex-1 p-8 flex flex-col items-center justify-center">
                    <!-- Title -->
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-2 h-2 bg-indigo-500 rounded-full shadow-[0_0_8px_rgba(99,102,241,0.8)]"></div>
                        <h2 class="text-[11px] font-black tracking-[0.3em] uppercase text-indigo-100">Conditions d'utilisation</h2>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full shadow-[0_0_8px_rgba(99,102,241,0.8)]"></div>
                    </div>

                    <!-- List -->
                    <ul class="space-y-4 text-[9.5px] text-indigo-50 font-semibold max-w-[90%]">
                        <li class="flex items-start gap-4">
                            <span class="w-2 h-2 bg-indigo-600 rounded-full mt-1.5 shrink-0 shadow-lg"></span>
                            <span>Cette carte est strictement personnelle et incessible.</span>
                        </li>
                        <li class="flex items-start gap-4">
                            <span class="w-2 h-2 bg-indigo-600 rounded-full mt-1.5 shrink-0 shadow-lg"></span>
                            <span>Elle doit être présentée lors de tout contrôle administratif ou académique.</span>
                        </li>
                        <li class="flex items-start gap-4">
                            <span class="w-2 h-2 bg-indigo-600 rounded-full mt-1.5 shrink-0 shadow-lg"></span>
                            <span>En cas de perte, l'étudiant doit informer l'administration sans délai.</span>
                        </li>
                        <li class="flex items-start gap-4">
                            <span class="w-2 h-2 bg-indigo-600 rounded-full mt-1.5 shrink-0 shadow-lg"></span>
                            <span>Toute falsification expose son auteur à des sanctions disciplinaires.</span>
                        </li>
                    </ul>
                </div>

                <!-- Footer -->
                <div class="relative z-10 px-8 py-6 pt-0 flex justify-between items-end">
                    <div class="text-left">
                        <p class="text-[9px] font-black text-indigo-200 uppercase mb-2 tracking-widest">Le Directeur Général</p>
                        <div class="w-32 h-1 bg-gradient-to-right from-indigo-500 to-transparent rounded-full opacity-40"></div>
                    </div>
                    <div class="text-right space-y-1">
                        <p class="text-[7px] text-indigo-300 font-bold opacity-80 uppercase tracking-widest">BP 12471 Lomé - Togo</p>
                        <p class="text-[7px] text-indigo-300 font-bold opacity-80">+228 22 20 47 00 • www.iai-togo.tg</p>
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
