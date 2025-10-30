<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat de Vérification - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .result-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .success-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        
        .error-header {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        
        .status-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .info-row {
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-size: 1.1rem;
            color: #212529;
        }
        
        .grade-badge {
            font-size: 1.5rem;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
        }
        
        .credits-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        
        .btn-return {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        
        .verification-badge {
            background: rgba(40, 167, 69, 0.1);
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="result-card">
                    @if($success)
                        <!-- Header Success -->
                        <div class="success-header">
                            <div class="status-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h1 class="h3 mb-2">Relevé de Notes Vérifié</h1>
                            <p class="mb-0 opacity-75">Ce document est authentique et officiel</p>
                        </div>
                        
                        <!-- Content -->
                        <div class="p-4">
                            <!-- Verification Badge -->
                            <div class="verification-badge text-center">
                                <i class="fas fa-certificate text-success me-2"></i>
                                <strong class="text-success">Document Officiel Authentifié</strong>
                                <br>
                                <small class="text-muted">Vérifié le {{ now()->format('d/m/Y à H:i') }}</small>
                            </div>
                            
                            <!-- Student Information -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fas fa-user me-2 text-primary"></i>
                                            Nom complet
                                        </div>
                                        <div class="info-value">
                                            {{ $data['etudiant']['nom'] }} {{ $data['etudiant']['prenom'] }}
                                        </div>
                                    </div>
                                    
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fas fa-venus-mars me-2 text-primary"></i>
                                            Sexe
                                        </div>
                                        <div class="info-value">{{ $data['etudiant']['genre'] }}</div>
                                    </div>
                                    
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fas fa-graduation-cap me-2 text-primary"></i>
                                            Filière
                                        </div>
                                        <div class="info-value">{{ $data['filiere'] }}</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                            Année académique
                                        </div>
                                        <div class="info-value">{{ $data['anne_academique'] }}</div>
                                    </div>
                                    
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fas fa-clock me-2 text-primary"></i>
                                            Période
                                        </div>
                                        <div class="info-value">{{ $data['periode'] }}</div>
                                    </div>
                                    
                                    <div class="info-row">
                                        <div class="info-label">
                                            <i class="fas fa-file-alt me-2 text-primary"></i>
                                            Date de publication
                                        </div>
                                        <div class="info-value">{{ $data['date_publication'] }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Academic Results -->
                            <div class="credits-section">
                                <h5 class="mb-3">
                                    <i class="fas fa-chart-line text-success me-2"></i>
                                    Résultats Académiques
                                </h5>
                                
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="p-3">
                                            <div class="grade-badge badge bg-primary">
                                                {{ $data['moyenne_generale'] }}/20
                                            </div>
                                            <div class="mt-2 fw-semibold">Moyenne Générale</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="p-3">
                                            <div class="grade-badge badge bg-success">
                                                {{ $data['total_credits_valides'] }}
                                            </div>
                                            <div class="mt-2 fw-semibold">Crédits Validés</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="p-3">
                                            <div class="grade-badge badge bg-warning">
                                                {{ $data['total_credits_non_valides'] }}
                                            </div>
                                            <div class="mt-2 fw-semibold">Crédits Non Validés</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Appreciation -->
                                <div class="text-center mt-3">
                                    @php
                                        $moyenne = (float) $data['moyenne_generale'];
                                        if ($moyenne >= 16) {
                                            $appreciation = 'Excellent';
                                            $badgeClass = 'bg-success';
                                        } elseif ($moyenne >= 14) {
                                            $appreciation = 'Très Bien';
                                            $badgeClass = 'bg-info';
                                        } elseif ($moyenne >= 12) {
                                            $appreciation = 'Bien';
                                            $badgeClass = 'bg-primary';
                                        } elseif ($moyenne >= 10) {
                                            $appreciation = 'Assez Bien';
                                            $badgeClass = 'bg-warning';
                                        } else {
                                            $appreciation = 'Insuffisant';
                                            $badgeClass = 'bg-danger';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} px-3 py-2 fs-6">
                                        <i class="fas fa-star me-2"></i>
                                        Mention : {{ $appreciation }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Security Information -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Information de sécurité :</strong>
                                Ce relevé a été généré le {{ $data['date_generation'] }} et est protégé par un système de vérification cryptographique.
                            </div>
                        </div>
                        
                    @else
                        <!-- Header Error -->
                        <div class="error-header">
                            <div class="status-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <h1 class="h3 mb-2">Vérification Échouée</h1>
                            <p class="mb-0 opacity-75">Le code de vérification n'est pas valide</p>
                        </div>
                        
                        <!-- Error Content -->
                        <div class="p-4">
                            <div class="alert alert-danger text-center">
                                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                <h5>{{ $error }}</h5>
                                <p class="mb-0">
                                    Veuillez vérifier le code de vérification et réessayer.
                                </p>
                            </div>
                            
                            <div class="bg-light rounded p-3">
                                <h6><i class="fas fa-lightbulb text-warning me-2"></i>Conseils :</h6>
                                <ul class="mb-0">
                                    <li>Vérifiez que le QR code est bien lisible</li>
                                    <li>Assurez-vous de saisir le code complet</li>
                                    <li>Le relevé doit être officiel et publié</li>
                                    <li>Contactez l'administration en cas de problème</li>
                                </ul>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Action Buttons -->
                    <div class="p-4 pt-0">
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="{{ route('public.releve.verify') }}" class="btn btn-return btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Nouvelle Vérification
                            </a>
                            
                            @if($success)
                            <button onclick="window.print()" class="btn btn-outline-secondary">
                                <i class="fas fa-print me-2"></i>
                                Imprimer
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-4">
                    <small class="text-white opacity-75">
                        <i class="fas fa-university me-2"></i>
                        Système de vérification sécurisé - {{ config('app.name') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>