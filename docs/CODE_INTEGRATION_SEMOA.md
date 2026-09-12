# Code : intégration SEMOA (CashPay)

## 1. `app/Services/SemoaService.php`

```php
private static function loadStaticData(): void
{
    self::$url = rtrim(config('semoa.url'), '/') . '/';
    self::$userName = trim(config('semoa.username'));
    self::$password = trim(config('semoa.password'));
    self::$clientSecret = trim(config('semoa.client_secret'));
    self::$clientId = trim(config('semoa.client_id'));
    self::$apiKey = trim(config('semoa.api_key'));
    self::$apiReference = trim(config('semoa.api_reference'));
    self::$gatewayReference = trim(config('semoa.gateway_reference'));
}
```

```php
private function getToken(): string
{
    return Cache::remember('semoa_access_token', 30 * 60, function () {
        $response = Http::post(self::$url . 'auth', [
            "grant_type" => "password",
            "username" => self::$userName,
            "password" => self::$password,
            "client_id" => self::$clientId,
            "client_secret" => self::$clientSecret,
        ]);

        if ($response->failed()) {
            \Log::error('SEMOA Auth Failed:', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception("Échec d'authentification SEMOA : " . $response->body());
        }

        $data = $response->json();
        \Log::debug('SEMOA Auth Success:', ['data' => array_keys($data)]);

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (!isset($data['access_token'])) {
            throw new \Exception("Token absent de la réponse SEMOA : " . $response->body());
        }

        return $data['access_token'];
    });
}
```

```php
private function getHeaders(string $token): array
{
    $salt = (string) random_int(0, 999999);
    $signature = hash('sha256', self::$userName . self::$apiKey . $salt);

    $headers = [
        "Authorization" => "Bearer $token",
        "login" => self::$userName,
        "apisecure" => $signature,
        "apireference" => self::$apiReference,
        "api-key" => self::$apiKey,
        "salt" => $salt,
        "Content-Type" => "application/json",
        "Accept" => "application/json"
    ];

    return $headers;
}
```

```php
public function initializePayment(array $data, bool $isRetry = false): array
{
    $token = $this->getToken();

    $gatewayRef = $data['gateway_reference'] ?? self::$gatewayReference;

    $payload = [
        "amount" => $data['amount'],
        "client" => [
            "phone" => $data['phone']
        ],
        "gateway" => [
            "reference" => $gatewayRef
        ]
    ];

    if (isset($data['description'])) $payload["description"] = $data['description'];

    $baseUrl = rtrim(env('APP_URL'), '/');
    $payload["callback_url"] = $data['callback_url'] ?? ($baseUrl . '/api/semoa-callback-url');

    Log::info('SEMOA Payment Initialization Payload', [
        'url' => self::$url . 'orders',
        'payload' => $payload
    ]);

    $response = Http::withHeaders([
        "Authorization" => "Bearer $token",
        "Content-Type" => "application/json",
        "Accept" => "application/json"
    ])->post(self::$url . 'orders', $payload);

    if ($response->failed()) {
        if ($response->status() === 401 && !$isRetry) {
            Cache::forget('semoa_access_token');
            return $this->initializePayment($data, true);
        }
        throw new \Exception("Erreur SEMOA (Initialisation) : " . $response->body());
    }

    return $response->json();
}
```

```php
public function checkPaymentStatus(string $reference): array
{
    $token = $this->getToken();

    $response = Http::withHeaders($this->getHeaders($token))
        ->get(self::$url . "orders/{$reference}");

    if ($response->failed()) {
        throw new \Exception("Erreur SEMOA (Vérification) : " . $response->body());
    }

    return $response->json();
}
```

---

## 2. `app/Http/Controllers/SemoaPaymentController.php`

```php
public function initiate(Request $request)
{
    $validator = Validator::make($request->all(), [
        'etudiant_id' => 'required|exists:etudiants,id',
        'montant' => 'required|numeric|min:100',
        'lastname' => 'required|string',
        'firstname' => 'required|string',
        'phone' => 'required|string',
        'nature_paiement' => 'nullable|string|in:scolarite,inscription',
        'payable_id' => 'nullable|integer',
        'payable_type' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    try {
        $paiement = $this->paiementService->creerPaiementEnAttente(
            $request->etudiant_id,
            $request->montant,
            'semoa',
            $request->get('nature_paiement', 'scolarite'),
            $request->payable_id,
            $request->payable_type,
            "Initialisation paiement SEMOA"
        );

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $semoaResponse = $this->semoaService->initializePayment([
            'amount' => (float) $request->montant,
            'description' => "Paiement Scolarité - " . $request->lastname . " " . $request->firstname,
            'lastname' => $request->lastname,
            'firstname' => $request->firstname,
            'phone' => $request->phone,
            'gateway_reference' => $request->payment_method,
            'success_url' => $frontendUrl . '/etudiant/mes-paiements?status=success',
            'cancel_url' => $frontendUrl . '/etudiant/mes-paiements?status=cancel',
        ]);

        $orderReference = $semoaResponse['order_reference'] ?? null;
        if ($orderReference) {
            $paiement->update(['reference' => $orderReference]);
        }

        return response()->json([
            'success' => true,
            'payment_url' => $semoaResponse['long_bill_url'] ?? $semoaResponse['bill_url'] ?? null,
            'order_reference' => $orderReference
        ]);

    } catch (Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
    }
}
```

---

## 3. `app/Http/Controllers/Api/SemoaCallBackController.php`

```php
public function __invoke(Request $request)
{
    try {
        $token = $request->getContent();

        if (!$token) {
            $token = $request->input('token');
        }

        if (!$token) {
            Log::warning('SEMOA Webhook: Aucun jeton reçu.');
            return response(['message' => 'Aucun jeton reçu'], 400);
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            Log::error('SEMOA Webhook: Format JWT invalide');
            return response(['message' => 'Format invalide'], 400);
        }

        $payload = json_decode(base64_decode($parts[1]), true);

        $callBack = SemoaCallBack::create(['data' => $payload]);

        $reference = $payload['order_reference'] ?? null;
        $state = $payload['state'] ?? null;

        if (!$reference) {
            return response(['message' => 'Référence manquante'], 400);
        }

        $paiement = \App\Models\Paiement::where('reference', $reference)->first();

        if (!$paiement) {
            Log::warning("SEMOA Webhook: Paiement introuvable pour la référence $reference");
            return response(['message' => 'Paiement introuvable'], 404);
        }

        $receivedAmount = (float) ($payload['received_amount'] ?? 0);
        if (abs($receivedAmount - (float)$paiement->montant) > 1) {
            Log::error("SEMOA Webhook: Écart de montant pour $reference. Attendu: {$paiement->montant}, Reçu: $receivedAmount");
            $paiement->update(['status' => 'rejete', 'commentaire' => 'Écart de montant détecté']);
            return response(['message' => 'Écart de montant'], 400);
        }

        if ($state === 'SUCCESS' || $state === 'COMPLETED' || $state === 'Paid') {
            if ($paiement->status !== 'valide') {
                $paiement->recu = $payload['bill_url'] ?? $paiement->recu;
                $paiement->valider();
                Log::info("SEMOA Webhook: Paiement $reference validé avec succès.");
            }
        } elseif ($state === 'FAILED' || $state === 'CANCELLED') {
            $paiement->update(['status' => 'rejete']);
            Log::info("SEMOA Webhook: Paiement $reference marqué comme rejeté.");
        }

        return response(['message' => 'Notification traitée avec succès', 'row_id' => $callBack->id]);

    } catch (Throwable $e) {
        Log::error('SEMOA Webhook Error: ' . $e->getMessage());
        return response(['message' => 'Erreur lors du traitement', 'error' => $e->getMessage()], 500);
    }
}
```

---

## 4. `app/Services/PaiementEtudiantService.php`

```php
public function creerPaiementEnAttente($etudiantId, $montant, $modePaiement, $naturePaiement = 'scolarite', $payableId = null, $payableType = null, $commentaire = null)
{
    $anneeScolaireId = getAnneeScolaireId();
    $etudiant = Etudiant::findOrFail($etudiantId);
    $payable = $this->determinerPayable($etudiantId, $payableId, $payableType, $anneeScolaireId, $naturePaiement);

    if (!$payable) {
        throw new Exception("Impossible de déterminer l'élément à payer.");
    }

    $paiement = new Paiement();
    $paiement->etudiant_id = $etudiantId;
    $paiement->montant = $montant;
    $paiement->mode_paiement = $modePaiement;
    $paiement->nature_paiement = $naturePaiement;
    $paiement->commentaire = $commentaire;
    $paiement->status = 'en_attente';
    $paiement->date_paiement = now();
    $paiement->payable_type = get_class($payable);
    $paiement->payable_id = $payable->id;
    $paiement->reference = 'PEND-' . strtoupper(uniqid());
    $paiement->save();

    return $paiement;
}
```

```php
private function determinerPayable($etudiantId, $payableId, $payableType, $anneeScolaireId, $naturePaiement = 'scolarite')
{
    if ($naturePaiement === 'inscription') {
        if ($payableId && $payableType !== 'frais_inscription') {
            return null;
        }

        $fraisInsc = FraisInscription::where('annee_scolaire_id', $anneeScolaireId)
            ->where('active', true)
            ->first();

        if ($fraisInsc) {
            if ($payableId && $payableId != $fraisInsc->id) return null;

            $reste = $this->calculerResteAPayer($fraisInsc, $etudiantId);
            if ($reste > 0) return $fraisInsc;

            return null;
        }
    }

    if ($payableId && $payableType) {
        if ($payableType === 'echeance') return \App\Models\Echeance::find($payableId);
        if ($payableType === 'tranche') return \App\Models\TranchePaiement::find($payableId);
        if ($payableType === 'frais_inscription') return \App\Models\FraisInscription::find($payableId);
    }

    // ... sinon, résout automatiquement la première échéance/tranche non payée
}
```

---

## 5. `app/Models/Paiement.php`

```php
public function valider()
{
    $this->status = 'valide';
    $this->save();

    if ($this->payable instanceof Echeance) {
        $this->payable->updateMontantPaye();
    } elseif ($this->payable instanceof FraisEtudiant) {
        $this->payable->updateStatut();
    } elseif ($this->payable instanceof TranchePaiement || $this->payable instanceof FraisInscription) {
        if (method_exists($this->payable, 'updateStatut')) {
            $this->payable->updateStatut();
        }
    }

    return $this;
}
```

```php
public function annuler($motif, $userId)
{
    $this->annule = true;
    $this->motif_annulation = $motif;
    $this->date_annulation = now();
    $this->annule_par = $userId;
    $this->status = 'rejete';
    $this->save();

    if ($this->payable instanceof Echeance) {
        $this->payable->updateMontantPaye();
    }

    return $this;
}
```

---

## 6. `config/semoa.php`

```php
return [
    'url' => env('CASHPAY_URL', env('SEMOA_URL', 'https://api.semoa-payments.ovh/sandbox-v3')),
    'client_id' => env('CASHPAY_CLIENT_ID', env('SEMOA_CLIENT_ID')),
    'client_secret' => env('CASHPAY_CLIENT_SECRET', env('SEMOA_CLIENT_SECRET')),
    'username' => env('CASHPAY_USERNAME', env('SEMOA_USERNAME')),
    'password' => env('CASHPAY_PASSWORD', env('SEMOA_PASSWORD')),
    'api_key' => env('CASHPAY_API_KEY', env('SEMOA_API_KEY')),
    'api_reference' => env('CASHPAY_API_REFERENCE', env('SEMOA_API_REFERENCE', '20')),
    'gateway_reference' => env('SEMOA_GATEWAY_REFERENCE'),
    'in_sandbox' => env('SEMOA_IN_SANDBOX', true),
];
```

---

## 7. `app/Http/Controllers/SemoaCallBackController.php` (legacy)

```php
private function generateApiSecure(): string
{
    $login = env('SEMOA_API_REFERENCE', '20');
    $apiKey = env('SEMOA_API_KEY');
    $concatenatedString = $login . $apiKey . $this->generateSalt();
    return hash('sha256', $concatenatedString);
}
```

```php
private function generateSalt(): int
{
    return random_int(0, 999999);
}
```

```php
private function getApiBaseUrl(): string
{
    return env('SEMOA_URL', "https://api.semoa-payments.ovh/sandbox-v3");
}
```

```php
private function getHeaders(?string $token = null): array
{
    $salt = $this->generateSalt();

    $login = env('SEMOA_API_REFERENCE', '20');
    $apiKey = env('SEMOA_API_KEY');
    $headers = [
        "login" => $login,
        "apisecure" => hash('sha256', $login . $apiKey . $salt),
        "apireference" => env('SEMOA_API_REFERENCE', '20'),
        "salt" => $salt,
        "Content-Type" => "application/json",
    ];

    if ($token) {
        $headers["Authorization"] = "Bearer $token";
    }

    return $headers;
}
```

```php
private function getToken(): string
{
    try {
        return Cache::remember(self::TOKEN_CACHE_KEY, self::TOKEN_EXPIRATION_MINUTES * 60, function () {
            $response = $this->client->post($this->getApiBaseUrl() . "/auth", [
                'json' => [
                    "grant_type" => "password",
                    "username" => env('SEMOA_USERNAME'),
                    "password" => env('SEMOA_PASSWORD'),
                    "client_id" => env('SEMOA_CLIENT_ID'),
                    "client_secret" => env('SEMOA_CLIENT_SECRET'),
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);

            $body = (string) $response->getBody();
            $data = json_decode($body, true);
            if (!isset($data['access_token'])) {
                \Log::error('Token response missing access_token', [
                    'raw_body' => $body,
                    'decoded'  => $data,
                    'type'     => gettype($data)
                ]);

                throw new \RuntimeException('Invalid token response format');
            }

            return $data['access_token'];
        });
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        \Log::error('Authentication failed', [
            'error' => $e->getMessage(),
            'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null
        ]);
        throw new \RuntimeException('Authentication failed: ' . $e->getMessage());
    }
}
```

```php
private function invalidateToken(): void
{
    Cache::forget(self::TOKEN_CACHE_KEY);
}
```

```php
public function authentification()
{
    try {
        $token = $this->getToken();
        return response()->json(['token' => $token]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur d\'authentification: ' . $e->getMessage()], 500);
    }
}
```

```php
public function ping()
{
    try {
        $token = $this->getToken();

        $response = $this->client->request('POST', $this->getApiBaseUrl() . "/ping", [
            'headers' => $this->getHeaders($token),
            'json' => ["action" => "ping"]
        ]);

        return response()->json(json_decode($response->getBody(), true));
    } catch (\Exception $e) {
        $this->invalidateToken();
        return response()->json(['error' => 'Erreur de ping: ' . $e->getMessage()], 500);
    }
}
```

```php
public function paymentStatus($reference)
{
    $response = $this->getOrder($reference);
    $data = json_decode($response->getContent(), true);

    \Log::debug('Full API Response', $data);

    if (isset($data['items'][0])) {
        $orderData = $data['items'][0];
    } else {
        $orderData = $data;
    }

    return view('regions.status', [
        'status' => $orderData['state'] ?? 'UNKNOWN',
        'reference' => $orderData['order_reference'],
        'amount' => $orderData['amount'],
        'date' => $orderData['date_create'],
        'client' => [
            'phone' => $orderData['client']['phone']
        ]
    ]);
}
```

```php
public function createOrder(Request $request)
{
    try {
        $token = $this->getToken();

        $response = $this->client->request('POST', $this->getApiBaseUrl() . "/orders", [
            'headers' => $this->getHeaders($token),
            'json' => [
                "amount" => $request->amount,
                "description" => "Paiement des frais de scolarité",
                "client" => [
                    "lastname" => $request->lastname,
                    "firstname" => $request->firstname,
                    "phone" => $request->phone,
                ],
                "payment_method" => $request->payment_method,
            ]
        ]);

        return response()->json(json_decode($response->getBody(), true));
    } catch (\Exception $e) {
        $this->invalidateToken();
        return response()->json(['error' => 'Erreur lors de la création de la commande: ' . $e->getMessage()], 500);
    }
}
```

```php
public function getOrder($reference)
{
    try {
        $token = $this->getToken();

        $response = $this->client->get($this->getApiBaseUrl() . "/orders/{$reference}", [
            'headers' => $this->getHeaders($token)
        ]);

        return response()->json(
            json_decode($response->getBody(), true),
            $response->getStatusCode()
        );
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
```

```php
public function orderList()
{
    try {
        $token = $this->getToken();

        $response = $this->client->request('GET', $this->getApiBaseUrl() . "/orders", [
            'headers' => $this->getHeaders($token)
        ]);

        return response()->json(json_decode($response->getBody(), true));
    } catch (\Exception $e) {
        $this->invalidateToken();
        return response()->json(['error' => 'Erreur lors de la récupération des commandes: ' . $e->getMessage()], 500);
    }
}
```

```php
public function processPayment(Request $request)
{
    $validator = Validator::make($request->all(), [
        'lastname' => 'required|string|max:255',
        'firstname' => 'required|string|max:255',
        'phone' => 'required|string|regex:/^\+228\d{8}$/',
        'amount' => 'required|numeric|min:100|max:1000000',
    ]);

    if ($validator->fails()) {
        \Log::error('Validation failed', $validator->errors()->toArray());
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        $etudiant = auth()->user();
        $token = $this->getToken();
        if (!$etudiant) {
            return response()->json(['success' => false, 'message' => 'Étudiant non trouvé'], 404);
        }
        $annee = AnneeScolaire::where('active', true)->first();

        $etudiant = Candidature::where('etudiant_id', $etudiant->id)->where('annee_scolaire_id', $annee->id)->latest()->first();
        $niveau = $etudiant->niveau;
        $frais =  FraisScolarite::where('annee_scolaire_id', $annee->id)
            ->where('niveau_id', $niveau->id)
            ->first();

        $tranches = TranchePaiement::where('frais_scolarite_id', $frais->id)
            ->get();

        $trancheNonPaye = null;
        $montantRestant = $request->amount;
        $montantPaye = 0;
        // while ($montantRestant > 0) {
        //     foreach ($tranches as $tranche) {
        //         $montantTranchePaye = Paiement::where('etudiant_id', $etudiant->etudiant_id)
        //             ->where('tranche_paiement_id', $tranche->id)
        //             ->where('annule', false)
        //             ->sum('montant');
        //
        //         $resteTranche = $tranche->montant - $montantTranchePaye;
        //
        //         if ($resteTranche > 0) {
        //             $montantPaye = min($montantRestant, $resteTranche);
        //
        //             Paiement::create([
        //                 'etudiant_id' => $etudiant->etudiant_id,
        //                 'tranche_paiement_id' => $tranche->id,
        //                 'montant' => $montantPaye,
        //                 'mode_paiement' => 'semoa',
        //                 'reference' => $request->input('reference', 'REF-' . uniqid()),
        //                 'status' => 'en_attente',
        //                 'recu' => false,
        //                 'date_paiement' => now(),
        //                 'annule' => false,
        //                 'motif_annulation' => null,
        //                 'date_annulation' => null,
        //                 'annule_par' => null,
        //             ]);
        //
        //             $montantRestant -= $montantPaye;
        //         }
        //         if ($montantRestant <= 0) {
        //             break;
        //         }
        //     }
        //     break;
        // }

        $response = $this->client->post($this->getApiBaseUrl() . "/orders", [
            'headers' => $this->getHeaders($token),
            'json' => [
                'amount' => (float) $request->input('amount'),
                'description' => 'Paiement via ' . config('app.name'),
                'client' => [
                    'lastname' => $request->input('lastname'),
                    'firstname' => $request->input('firstname'),
                    'phone' => $request->input('phone'),
                ],
                "gateway" => [
                    "reference" => $request->input("payment_method"),
                ],
                "currency" => "XOF",
                "callback_url" => "http://localhost:8000/espace-etudiant/mes-payements"
            ]
        ]);

        \Log::info('API Response', ['response' => (string) $response->getBody()]);

        $data = json_decode($response->getBody(), true);
        \Log::info('Data after API call', $data);

        $gatewayConfigs = [
            '14f4597d-ef96-4263-8107-1e1970959133' => [
                'id' => 11,
                'type' => 'recap-sandbox',
            ],
            '016eb63c-f29d-4384-92e4-b1bd37ef69f8' => [
                'id' => 1,
                'type' => 'recap',
            ],
            'a2c87957-1033-46e9-8706-056e45737de1' => [
                'id' => 27,
                'type' => 'recap',
            ],
            '52bfd484-13ef-44f3-b128-adf7187779b0' => [
                'id' => 6,
                'type' => 'recap',
            ],
            'f7bbfaef-eba3-4b82-ac31-61eb2b772289' => [
                'type' => 'external',
            ],
        ];

        $gatewayRef = $request->input('payment_method');
        $config = $gatewayConfigs[$gatewayRef] ?? null;

        if (!$config) {
            return response()->json([
                'success' => false,
                'error' => 'Méthode de paiement inconnue.'
            ], 400);
        }

        $orderReference = $data['order_reference'] ?? null;

        if (!$orderReference) {
            return response()->json([
                'success' => false,
                'error' => 'Référence de commande manquante.'
            ], 500);
        }

        if ($config['type'] === 'recap') {
            $redirectUrl = "https://sandbox.cashpay.tg/facture/recap/{$orderReference}/{$config['id']}";
        } elseif ($config['type'] === 'recap-sandbox') {
            $redirectUrl = "https://sandbox.cashpay.tg/facture/recap-sandbox/{$orderReference}/{$config['id']}";
        } elseif ($config['type'] === 'external') {
            $redirectUrl = $data['redirect_url'] ?? $data['long_bill_url'] ?? null;

            if (!$redirectUrl) {
                return response()->json([
                    'success' => false,
                    'error' => 'URL de redirection externe manquante'
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Type de redirection inconnu.'
            ], 500);
        }

        return redirect()->away($redirectUrl);
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        \Log::error('API Request failed', [
            'message' => $e->getMessage(),
            'response' => $e->hasResponse() ? (string) $e->getResponse()->getBody() : null
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Payment processing failed',
            'details' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}
```

---

## 8. `routes/api.php`

```php
Route::any('semoa-callback-url', SemoaCallBackController::class)->name('api.semoa.callback');
Route::post('semoa/initiate', [\App\Http\Controllers\SemoaPaymentController::class, 'initiate'])->middleware('auth:sanctum');
```
