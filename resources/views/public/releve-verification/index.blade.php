<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de Relevé de Notes - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .verification-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .header-section {
            background: #0d153d;
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        
        .qr-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-verify {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        .info-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .step-number {
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="verification-card">
                    <!-- Header -->
                    <div class="header-section">
                        <div class="qr-icon">
                            <i class="fas fa-qrcode"></i>
                        </div>
                        <h1 class="h3 mb-2">Vérification de Relevé de Notes</h1>
                        <p class="mb-0 opacity-75">Scannez le QR code ou saisissez le code de vérification</p>
                    </div>
                    
                    <!-- Form -->
                    <div class="p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        <form action="{{ route('public.releve.verify.form') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="mb-3">
                                <label for="qr_hash" class="form-label fw-semibold">
                                    <i class="fas fa-key me-2 text-primary"></i>
                                    Code de vérification
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg @error('qr_hash') is-invalid @enderror" 
                                       id="qr_hash" 
                                       name="qr_hash" 
                                       value="{{ old('qr_hash') }}"
                                       placeholder="Saisissez le code de vérification du QR code"
                                       required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Le code se trouve dans le QR code du relevé de notes
                                </div>
                                @error('qr_hash')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-verify btn-primary btn-lg">
                                    <i class="fas fa-search me-2"></i>
                                    Vérifier le Relevé
                                </button>
                            </div>
                        </form>
                        
                        <!-- Instructions -->
                        <div class="info-section">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-lightbulb text-warning me-2"></i>
                                Comment utiliser ce service ?
                            </h6>
                            
                            <div class="step">
                                <div class="step-number">1</div>
                                <div>
                                    <strong>Scannez le QR code</strong> présent sur votre relevé de notes avec votre smartphone
                                </div>
                            </div>
                            
                            <div class="step">
                                <div class="step-number">2</div>
                                <div>
                                    Ou <strong>saisissez manuellement</strong> le code de vérification dans le formulaire ci-dessus
                                </div>
                            </div>
                            
                            <div class="step">
                                <div class="step-number">3</div>
                                <div>
                                    <strong>Consultez les informations</strong> authentifiées de votre relevé de notes
                                </div>
                            </div>
                            
                            <div class="mt-3 p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded">
                                <small class="text-success">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <strong>Sécurisé :</strong> Ce système garantit l'authenticité de vos relevés de notes officiels.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-4">
                    <small class="text-white opacity-75">
                        <i class="fas fa-university me-2"></i>
                        Système de vérification - {{ config('app.name') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-focus sur le champ de saisie -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('qr_hash').focus();
        });
    </script>
</body>
</html>