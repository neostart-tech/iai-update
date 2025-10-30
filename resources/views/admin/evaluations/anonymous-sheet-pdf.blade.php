<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiches Anonymes - {{ $evaluation->matiere->nom }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .header .info {
            margin-top: 8px;
            font-size: 11px;
            color: #666;
        }
        
        .salle-section {
            page-break-inside: avoid;
            margin-bottom: 30px;
        }
        
        .salle-title {
            background-color: #f0f0f0;
            padding: 10px;
            border: 1px solid #ccc;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .codes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .code-card {
            border: 2px solid #333;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            background-color: #fafafa;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .anonymous-code {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-bottom: 8px;
            letter-spacing: 2px;
            border: 1px dashed #666;
            padding: 8px;
            background-color: white;
        }
        
        .student-info {
            font-size: 10px;
            color: #555;
            margin-top: 5px;
        }
        
        .matricule {
            font-weight: bold;
            font-size: 11px;
        }
        
        .footer {
            position: fixed;
            bottom: 15px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        
        .summary-table th,
        .summary-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        
        .summary-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- En-tête principal -->
    <div class="header">
        <h1>FICHES D'ANONYMAT</h1>
        <div class="info">
            <strong>{{ $evaluation->matiere->nom ?? 'Matière non définie' }}</strong><br>
            {{ $evaluation->debut->format('d/m/Y à H:i') }} - 
            {{ $evaluation->fin->format('H:i') }}<br>
            Type: {{ $evaluation->type ?? 'N/A' }} | 
            Groupe: {{ $evaluation->group->nom ?? 'N/A' }}
        </div>
    </div>

    <!-- Résumé par salle -->
    <div style="margin-bottom: 30px;">
        <h3 style="margin-bottom: 10px;">Résumé de la répartition</h3>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Salle</th>
                    <th>Nombre d'étudiants</th>
                    <th>Codes générés</th>
                </tr>
            </thead>
            <tbody>
                @foreach($anonymousCodesBySalle as $salle => $codes)
                    <tr>
                        <td>{{ $salle }}</td>
                        <td>{{ $codes->count() }}</td>
                        <td>
                            @foreach($codes->take(5) as $code)
                                {{ $code->anonymous_code }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                            @if($codes->count() > 5)
                                ...
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f0f0f0;">
                    <td>TOTAL</td>
                    <td>{{ $anonymousCodesBySalle->flatten()->count() }}</td>
                    <td>{{ $anonymousCodesBySalle->keys()->count() }} salle(s)</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Page de détail par salle -->
    @foreach($anonymousCodesBySalle as $salle => $codes)
        @if(!$loop->first)
            <div class="page-break"></div>
        @endif
        
        <div class="salle-section">
            <div class="salle-title">
                📍 SALLE: {{ $salle }} - {{ $codes->count() }} étudiants
            </div>
            
            <div class="codes-grid">
                @foreach($codes as $code)
                    <div class="code-card">
                        <div class="anonymous-code">{{ $code->anonymous_code }}</div>
                        <div class="student-info">
                            <div class="matricule">Mat: {{ $code->etudiant->id ?? 'N/A' }}</div>
                            <div>{{ Str::limit(($code->etudiant->nom ?? '') . ' ' . ($code->etudiant->prenom ?? ''), 25) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <!-- Pied de page -->
    <div class="footer">
        Document généré le {{ $generatedAt->format('d/m/Y à H:i') }} | 
        {{ config('app.name', 'Système de Gestion Scolaire') }} | 
        Total: {{ $anonymousCodesBySalle->flatten()->count() }} codes anonymes
    </div>
</body>
</html>