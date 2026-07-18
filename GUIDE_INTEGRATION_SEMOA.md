# Guide : intégration SEMOA (CashPay) — code et explications

Ce document reprend le code réel de l'intégration SEMOA, fonction par fonction, avec une explication de ce que fait chaque bloc. Tous les extraits ci-dessous sont copiés tels quels des fichiers du projet (pas paraphrasés).

## 1. Le client HTTP — `app/Services/SemoaService.php`

C'est la classe qui parle réellement à l'API SEMOA dans le **flux moderne**.

### 1.1 Chargement de la config

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
**Explication** : appelée dans le constructeur (`__construct()`). Toutes les valeurs viennent de `config('semoa.*')` (voir §5), donc de `CASHPAY_*`/`SEMOA_*` dans le `.env`. `rtrim(..., '/') . '/'` garantit qu'il y a toujours exactement un `/` à la fin de l'URL de base, pour pouvoir faire `self::$url . 'auth'` sans double-slash.

### 1.2 Récupération du token (authentification)

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
**Explication** :
- `Cache::remember('semoa_access_token', 30 * 60, ...)` : le token est mis en cache **30 minutes**. Tant que le cache est valide, aucun nouvel appel `/auth` n'est fait — la fonction retourne directement le token caché.
- L'authentification utilise le grant OAuth2 **"password"** : `username`/`password` (un compte de service SEMOA) + `client_id`/`client_secret` (identifiants de l'application).
- Si la requête échoue (`$response->failed()`), une exception est levée avec le corps de la réponse SEMOA dans le message — utile pour le débogage mais **attention à ne pas exposer ce message brut dans une réponse API destinée au frontend** en production (il peut contenir des détails internes de SEMOA).
- `is_string($data)` : garde-fou si l'API renvoie parfois une chaîne JSON au lieu d'un objet déjà décodé.

### 1.3 Construction des en-têtes signés (utilisée pour la vérification de statut)

```php
private function getHeaders(string $token): array
{
    $salt = (string) random_int(0, 999999);
    // Signature basée sur le Username (demo_escen) + Api Key + Salt, en MINUSCULES
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
**Explication** : SEMOA exige, en plus du Bearer token OAuth, une signature applicative maison : `sha256(username + api_key + salt)`, où `salt` est un nombre aléatoire renvoyé aussi en clair dans l'en-tête `salt` (pour que SEMOA recalcule la même empreinte côté serveur). Ce header set n'est utilisé que par `checkPaymentStatus()` — `initializePayment()` (ci-dessous) n'envoie **que** le Bearer token, sans cette signature.

### 1.4 Création d'une commande de paiement

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
**Explication** :
- Construit un payload minimal : montant, téléphone du client, référence de la passerelle (`gateway.reference` — un UUID identifiant Flooz/T-money/Ecobank/etc., voir §3), et une `callback_url` (par défaut `{APP_URL}/api/semoa-callback-url`, mais **surchargeable** via `$data['callback_url']`).
- **Logique de retry sur 401** : si le token a expiré côté SEMOA avant l'expiration du cache local (30 min), le premier essai échoue avec 401 → `Cache::forget()` vide le cache, puis **un seul retry récursif** (`$isRetry = true` empêche une boucle infinie) est tenté avec un nouveau token.
- Retourne directement le JSON de la réponse SEMOA (contient `order_reference`, `bill_url`/`long_bill_url`, etc.) — pas de mapping vers un DTO, le contrôleur appelant lit les clés brutes.

### 1.5 Vérification manuelle du statut d'une commande

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
**Explication** : `GET /orders/{reference}`, avec les en-têtes signés du §1.3. **Rien dans le code n'appelle automatiquement cette méthode** — elle existe pour un usage manuel/futur (ex. un job de vérification périodique qui n'a pas encore été écrit).

---

## 2. Le point d'entrée du paiement — `app/Http/Controllers/SemoaPaymentController.php`

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
        // 1. Créer le paiement en attente dans notre base
        $paiement = $this->paiementService->creerPaiementEnAttente(
            $request->etudiant_id,
            $request->montant,
            'semoa',
            $request->get('nature_paiement', 'scolarite'),
            $request->payable_id,
            $request->payable_type,
            "Initialisation paiement SEMOA"
        );

        // 2. Appeler le service SEMOA
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

        // 3. Mettre à jour le paiement avec la référence SEMOA
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
**Explication ligne par ligne** :
1. Validation classique Laravel — `payment_method` (la passerelle choisie, ex. Flooz/T-money) **n'est pas validé ici** alors qu'il est utilisé juste après (`$request->payment_method`) : si absent, `SemoaService::initializePayment()` retombe sur `self::$gatewayReference` (la passerelle par défaut définie dans `config('semoa.gateway_reference')`).
2. Le paiement est créé **avant** l'appel à SEMOA, avec une référence temporaire `PEND-...` (voir §4.1) — c'est ce qui permet au webhook (§3) de retrouver la ligne plus tard.
3. `initializePayment()` reçoit `success_url`/`cancel_url`, mais **`SemoaService::initializePayment()` ne les lit jamais** (relire le code du §1.4 : seuls `amount`, `client.phone`, `gateway.reference`, `description`, `callback_url` sont envoyés à SEMOA) — ces deux clés sont donc actuellement **ignorées silencieusement**, du code mort côté payload.
4. Dès que `order_reference` est connu, il remplace la référence temporaire du `Paiement` — c'est la jointure qui permettra au webhook de retrouver la bonne ligne.
5. La réponse renvoyée au frontend contient `payment_url` (l'URL de paiement hébergée par SEMOA vers laquelle rediriger l'utilisateur) et `order_reference`.

---

## 3. Le webhook de confirmation — `app/Http/Controllers/Api/SemoaCallBackController.php`

```php
public function __invoke(Request $request)
{
    try {
        // 1. Récupérer le contenu brut (le JWT)
        $token = $request->getContent();

        if (!$token) {
            $token = $request->input('token');
        }

        if (!$token) {
            Log::warning('SEMOA Webhook: Aucun jeton reçu.');
            return response(['message' => 'Aucun jeton reçu'], 400);
        }

        // 2. Décoder le JWT (Format SEMOA : [header].[payload].[signature])
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            Log::error('SEMOA Webhook: Format JWT invalide');
            return response(['message' => 'Format invalide'], 400);
        }

        $payload = json_decode(base64_decode($parts[1]), true);

        // 3. Log dans la table de suivi
        $callBack = SemoaCallBack::create(['data' => $payload]);

        $reference = $payload['order_reference'] ?? null;
        $state = $payload['state'] ?? null;

        if (!$reference) {
            return response(['message' => 'Référence manquante'], 400);
        }

        // 4. Trouver le paiement correspondant
        $paiement = \App\Models\Paiement::where('reference', $reference)->first();

        if (!$paiement) {
            Log::warning("SEMOA Webhook: Paiement introuvable pour la référence $reference");
            return response(['message' => 'Paiement introuvable'], 404);
        }

        // 5. Vérification de sécurité sur le montant
        $receivedAmount = (float) ($payload['received_amount'] ?? 0);
        if (abs($receivedAmount - (float)$paiement->montant) > 1) {
            Log::error("SEMOA Webhook: Écart de montant pour $reference. Attendu: {$paiement->montant}, Reçu: $receivedAmount");
            $paiement->update(['status' => 'rejete', 'commentaire' => 'Écart de montant détecté']);
            return response(['message' => 'Écart de montant'], 400);
        }

        // 6. Traiter selon l'état
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
**Explication, étape par étape** :
1. **Aucune authentification/signature vérifiée.** Le "JWT" reçu (`header.payload.signature`) n'est décodé **que sur sa partie centrale** (`$parts[1]`, en `base64_decode` + `json_decode`) — la partie `signature` (`$parts[2]`) n'est jamais lue ni vérifiée avec une clé secrète. **N'importe qui connaissant/devinant un `order_reference` peut forger ce JSON, l'encoder en 3 segments séparés par des points, et POSTer sur cette route pour marquer un paiement comme validé.**
2. `count($parts) !== 3` : seul le format à 3 segments est vérifié, pas leur contenu cryptographique.
3. Chaque appel est journalisé dans `semoa_call_backs`, **avant même** de savoir si le paiement existe — bon réflexe pour ne perdre aucune trace, même en cas d'échec plus loin.
4. Recherche par `reference` exacte : si le `Paiement` n'a pas été créé au préalable (cas du flux legacy, voir §6), retour 404 sans rien modifier.
5. **Seule protection réelle contre la fraude** : le montant reçu doit correspondre au montant attendu à ±1 près (marge d'arrondi). Un écart → `status = 'rejete'` direct.
6. `$paiement->status !== 'valide'` avant d'appeler `valider()` : évite de re-déclencher la cascade de mise à jour (§4.3) si le webhook est reçu plusieurs fois pour le même paiement (SEMOA peut renvoyer le même événement).

---

## 4. La création du paiement local

### 4.1 `PaiementEtudiantService::creerPaiementEnAttente()`

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
**Explication** : `determinerPayable()` (ci-dessous) résout **à quoi** ce paiement correspond concrètement (une échéance de scolarité, une tranche, ou des frais d'inscription) — si rien ne correspond (par exemple l'étudiant est déjà à jour), une exception arrête tout **avant** que quoi que ce soit ne soit envoyé à SEMOA. Notez que **`montant` n'est jamais comparé au reste à payer ici**, contrairement à `traiterPaiement()` (le chemin non-SEMOA, paiement cash/banque) qui lui vérifie `$montant > $resteAPayer` — c'est un contrôle qui manque dans le chemin SEMOA.

### 4.2 `determinerPayable()` (extrait)

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

            return null; // déjà payé
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
**Explication** : deux modes d'utilisation — soit l'appelant impose explicitement `payable_id`/`payable_type`, soit (si absents) la méthode déduit elle-même l'élément à payer en fonction de la nature (`inscription` → frais d'inscription actifs de l'année en cours ; `scolarite` → première échéance/tranche non soldée).

### 4.3 `Paiement::valider()` — le modèle

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
**Explication** : c'est le point unique qui fait passer `status` à `valide` **et** répercute l'information sur l'objet lié (`payable`, via la relation polymorphe) — c'est ce qui met à jour, par exemple, le montant déjà payé d'une échéance, pour que le reste-à-payer affiché ailleurs dans l'app (page comptabilité, tableau de bord étudiant) reflète le nouveau paiement.

---

## 5. Configuration — `config/semoa.php`

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
**Explication** : chaque clé essaie d'abord `CASHPAY_*`, puis retombe sur `SEMOA_*` si absent (double nommage historique — CashPay est la marque commerciale togolaise de SEMOA). **Seul `SemoaService` (flux moderne) lit cette config.** Le contrôleur legacy (§6) lit `env('SEMOA_*')` **directement**, sans le fallback `CASHPAY_*` — donc si votre `.env` ne définit que des `CASHPAY_*`, le flux legacy tourne avec des identifiants `null`.

---

## 6. Le flux legacy — `app/Http/Controllers/SemoaCallBackController.php`

Contrôleur séparé, avec sa **propre** logique d'authentification et de signature (dupliquée, légèrement différente du §1) :

```php
private function generateApiSecure(): string
{
    $login = env('SEMOA_API_REFERENCE', '20');
    $apiKey = env('SEMOA_API_KEY');
    $concatenatedString = $login . $apiKey . $this->generateSalt();
    return hash('sha256', $concatenatedString);
}

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
**Différence notable avec `SemoaService::getHeaders()`** : ici la signature est `sha256(login + apiKey + salt)` où **`login` = `SEMOA_API_REFERENCE`** (ex. `"20"`), alors que dans `SemoaService` la signature est `sha256(username + apiKey + salt)`. Ce n'est **pas la même formule** — les deux contrôleurs ne sont pas interchangeables, et un bug dans l'un ne se reproduit pas forcément dans l'autre.

### Le cœur du flux : `processPayment()`

Point d'entrée appelé depuis la modale de paiement de la page Blade étudiant.

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
        return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    }

    try {
        $etudiant = auth()->user();
        $token = $this->getToken();
        // ... résolution de l'étudiant, ses tranches de paiement (voir bloc commenté ci-dessous) ...

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
                "gateway" => ["reference" => $request->input("payment_method")],
                "currency" => "XOF",
                "callback_url" => "http://localhost:8000/espace-etudiant/mes-payements"
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        // Mapping gateway UUID -> URL de redirection CashPay
        $gatewayConfigs = [
            '14f4597d-ef96-4263-8107-1e1970959133' => ['id' => 11, 'type' => 'recap-sandbox'], // Sandbox
            '016eb63c-f29d-4384-92e4-b1bd37ef69f8' => ['id' => 1,  'type' => 'recap'],          // FloozTG
            'a2c87957-1033-46e9-8706-056e45737de1' => ['id' => 27, 'type' => 'recap'],          // Tmoney
            '52bfd484-13ef-44f3-b128-adf7187779b0' => ['id' => 6,  'type' => 'recap'],          // Ecobank
            'f7bbfaef-eba3-4b82-ac31-61eb2b772289' => ['type' => 'external'],                    // Orabank
        ];

        $gatewayRef = $request->input('payment_method');
        $config = $gatewayConfigs[$gatewayRef] ?? null;

        if (!$config) {
            return response()->json(['success' => false, 'error' => 'Méthode de paiement inconnue.'], 400);
        }

        $orderReference = $data['order_reference'] ?? null;
        if (!$orderReference) {
            return response()->json(['success' => false, 'error' => 'Référence de commande manquante.'], 500);
        }

        if ($config['type'] === 'recap') {
            $redirectUrl = "https://sandbox.cashpay.tg/facture/recap/{$orderReference}/{$config['id']}";
        } elseif ($config['type'] === 'recap-sandbox') {
            $redirectUrl = "https://sandbox.cashpay.tg/facture/recap-sandbox/{$orderReference}/{$config['id']}";
        } elseif ($config['type'] === 'external') {
            $redirectUrl = $data['redirect_url'] ?? $data['long_bill_url'] ?? null;
            if (!$redirectUrl) {
                return response()->json(['success' => false, 'error' => 'URL de redirection externe manquante'], 500);
            }
        }

        return redirect()->away($redirectUrl);
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // ...
    }
}
```

**Ce qui manque, et pourquoi c'est important** : entre la validation et l'appel à SEMOA, le code original contient ce bloc — **entièrement commenté** :

```php
// while ($montantRestant > 0) {
//     foreach ($tranches as $tranche) {
//         ...
//         Paiement::create([
//             'etudiant_id' => $etudiant->etudiant_id,
//             'tranche_paiement_id' => $tranche->id,
//             'montant' => $montantPaye,
//             'mode_paiement' => 'semoa',
//             'reference' => $request->input('reference', 'REF-' . uniqid()),
//             'status' => 'en_attente',
//             ...
//         ]);
//         $montantRestant -= $montantPaye;
//     }
//     break;
// }
```

**Conséquence concrète** : ce flux appelle bien SEMOA et redirige l'étudiant vers la page de paiement, mais **ne crée aucune ligne `Paiement`**. Quand SEMOA rappelle ensuite le webhook (§3) avec l'`order_reference` généré ici, `Paiement::where('reference', $reference)->first()` ne trouve rien → réponse 404, **le paiement n'est jamais validé en base**, même si l'argent a bien été prélevé côté opérateur mobile. Ce flux est donc actuellement **cassé de bout en bout** tant que ce bloc reste commenté.

**Autre problème visible dans ce même extrait** : `"callback_url" => "http://localhost:8000/espace-etudiant/mes-payements"` est codée en dur — en production, SEMOA rappellerait donc `localhost:8000`, une adresse qui n'existe pas côté serveur de prod. Il faut remplacer par `config('app.url') . '/api/semoa-callback-url'`, comme le fait déjà `SemoaService::initializePayment()`.

---

## 7. Les routes

```php
// routes/api.php
Route::any('semoa-callback-url', SemoaCallBackController::class)->name('api.semoa.callback');
Route::post('semoa/initiate', [\App\Http\Controllers\SemoaPaymentController::class, 'initiate'])->middleware('auth:sanctum');
```
- `Route::any(...)` : accepte **n'importe quel verbe HTTP** (GET, POST, PUT...) sur `/api/semoa-callback-url` — volontaire, car on ne maîtrise pas forcément la méthode que SEMOA utilisera pour son webhook, mais ça signifie aussi qu'un simple `GET` sur cette URL déclenche le même traitement.
- `semoa/initiate` est protégée par `auth:sanctum` (il faut être connecté) ; le webhook `semoa-callback-url`, lui, **n'a aucun middleware d'authentification** — normal pour un webhook externe, mais c'est précisément pour ça que l'absence de vérification de signature (§3) est le point le plus sensible de toute l'intégration.

Les routes du flux legacy (`routes/etudiant.php` et `routes/api_etudiant.php`) déclarent un groupe `semoa` quasi identique, l'un sous le guard session `auth:etudiants`, l'autre sous `auth:sanctum` — dupliqué, probablement un reste de migration en cours vers l'auth par token.

---

## 8. Résumé des points faibles identifiés dans ce code

| # | Problème | Où |
|---|---|---|
| 1 | Signature JWT du webhook jamais vérifiée | `Api\SemoaCallBackController::__invoke()` |
| 2 | Flux legacy ne crée jamais de `Paiement` (bloc commenté) | `SemoaCallBackController::processPayment()` |
| 3 | `callback_url` codée en `localhost` | `SemoaCallBackController::processPayment()` |
| 4 | Deux formules de signature différentes pour le même concept | `SemoaService::getHeaders()` vs `SemoaCallBackController::getHeaders()` |
| 5 | `success_url`/`cancel_url` envoyés mais jamais lus par `initializePayment()` | `SemoaPaymentController::initiate()` |
| 6 | Pas de vérification `montant > reste à payer` avant appel SEMOA | `PaiementEtudiantService::creerPaiementEnAttente()` |
| 7 | Lecture `env('SEMOA_*')` directe, sans les fallbacks `CASHPAY_*` | Contrôleur legacy + `semoaHelper.php` |
| 8 | Identifiants sandbox en dur, committés dans git | `scratch/test_semoa_final.php` |
