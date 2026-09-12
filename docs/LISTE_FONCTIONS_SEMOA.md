# Liste des fonctions SEMOA — de l'authentification à la récupération des commandes

Toutes ces fonctions vivent dans `App\Services\SemoaService`. Ordre d'appel réel du flux de paiement : **1 → 2 → 4** (authentification puis création de commande). La fonction **5** (récupération) est indépendante, utilisable à tout moment avec une référence de commande déjà connue.

## 1. Chargement de la configuration

```php
private static function loadStaticData(): void
{
    self::$url = trim(config('semoa.url'), '/') . '/';
    self::$userName = trim(config('semoa.username'));
    self::$password = trim(config('semoa.password'));
    self::$clientSecret = trim(config('semoa.client_secret'));
    self::$clientId = trim(config('semoa.client_id'));
    self::$apiKey = trim(config('semoa.api_key'));
    self::$apiReference = trim(config('semoa.api_reference'));
    self::$gatewayReference = trim(config('semoa.gateway_reference'));
}
```
Appelée automatiquement dans le constructeur. Remplit les propriétés statiques utilisées par toutes les autres fonctions.

---

## 2. Authentification

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
`POST {SEMOA_URL}/auth` avec grant OAuth2 "password". Le token obtenu est mis en cache 30 minutes (`Cache::remember`) pour éviter de se réauthentifier à chaque appel. Appelée en interne par les fonctions 4 et 5, jamais directement de l'extérieur.

---

## 3. Construction des en-têtes signés

```php
private function getHeaders(string $token): array
{
    $salt = (string) random_int(0, 999999);
    // Signature basée sur le Username (demo_escen) + Api Key + Salt, en MINUSCULES
    $signature = hash('sha256', self::$userName . self::$apiKey . $salt);

    $headers = [
        "Authorization" => "Bearer $token",
        "login" => self::$userName, // demo_escen
        "apisecure" => $signature,
        "apireference" => self::$apiReference, // 20
        "api-key" => self::$apiKey,
        "salt" => $salt,
        "Content-Type" => "application/json",
        "Accept" => "application/json"
    ];

    \Log::debug('SEMOA Headers Debug (USERNAME-LOWER-SIG):', [
        'login' => $headers['login'],
        'apireference' => $headers['apireference'],
        'salt' => $headers['salt'],
        'apisecure' => $headers['apisecure']
    ]);

    return $headers;
}
```
Construit la signature applicative maison (`sha256(username + api_key + salt)`) exigée par SEMOA en plus du Bearer token. **Utilisée uniquement par la fonction 5** (`checkPaymentStatus`) — la création de commande (fonction 4) envoie ses propres en-têtes simplifiés (Bearer token seul, sans signature).

---

## 4. Création d'une commande de paiement

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

    $baseUrl = trim(env('APP_URL'), '/');
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
`POST {SEMOA_URL}/orders`. Appelle d'abord la fonction 2 pour obtenir le token, construit le payload (montant, téléphone, passerelle, URL de callback), et gère un retry automatique si le token a expiré (401). C'est **la fonction appelée par `SemoaPaymentController::initiate()`** — le vrai point d'entrée du paiement.

---

## 5. Récupération d'une commande (vérification de statut)

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
`GET {SEMOA_URL}/orders/{reference}`. Appelle la fonction 2 (authentification) puis la fonction 3 (en-têtes signés), et interroge SEMOA sur l'état d'une commande déjà créée à partir de sa référence (`order_reference`, obtenue en sortie de la fonction 4).

⚠️ **Aucune route ni aucun code n'appelle actuellement cette fonction** — elle est prête à l'emploi mais pas encore branchée (utile pour un futur job de vérification périodique des paiements restés `en_attente`, ou pour un usage manuel via `php artisan tinker` : `app(App\Services\SemoaService::class)->checkPaymentStatus('ORDER_REF')`).

---

## Résumé de l'ordre d'appel réel

```
initiate() [SemoaPaymentController]
   └─► initializePayment()          [4. Création de commande]
          └─► getToken()            [2. Authentification]

checkPaymentStatus()                [5. Récupération de commande — pas encore appelée dans le code]
   ├─► getToken()                   [2. Authentification]
   └─► getHeaders()                 [3. En-têtes signés]
```
