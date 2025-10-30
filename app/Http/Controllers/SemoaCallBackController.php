<?php

namespace App\Http\Controllers;

use App\Models\SemoaCallBack;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\Candidature;
use App\Models\Etudiant;
use App\Models\TranchePaiement;
use App\Models\Paiement;
use App\Models\FraisScolarite;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\AnneeScolaire;


class SemoaCallBackController extends Controller
{
    const SEMOA_USERNAME = "demo";
    const SEMOA_PASSWORD = "1TFzh8<3KJQr";
    const SEMOA_CLIENT_ID = "cashpay";
    const SEMOA_API_REFERENCE = 20;
    const SEMOA_LOGIN = "20";
    const SEMOA_API_KEY = "TXpFE54mlXkFozpg5SdMC6kNy7jTuNCMcetP";
    const SEMOA_CLIENT_SECRET = "HpuNOm3sDOkAvd8v3UCIxiBu68634BBs";
    const TOKEN_CACHE_KEY = 'semoa_api_token';
    const TOKEN_EXPIRATION_MINUTES = 30;

    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

     private function generateApiSecure(): string
    {
        $concatenatedString = self::SEMOA_LOGIN . self::SEMOA_API_KEY . $this->generateSalt();
        return hash('sha256', $concatenatedString);
    }

     private function generateSalt(): int
    {
        return random_int(0, 999999);
    }

     private function getApiBaseUrl(): string
    {
        return env('SEMOA_IN_SANDBOX', true)
            ? "https://api.semoa-payments.ovh/sandbox"
            : "https://api.semoa-payments.ovh/prod";
    }

    
     private function getHeaders(?string $token = null): array
    {
        $salt = $this->generateSalt();

        $headers = [
            "login" => self::SEMOA_LOGIN,
            "apisecure" => hash('sha256', self::SEMOA_LOGIN . self::SEMOA_API_KEY . $salt),
            "apireference" => self::SEMOA_API_REFERENCE,
            "salt" => $salt,
            "Content-Type" => "application/json",
        ];

        if ($token) {
            $headers["Authorization"] = "Bearer $token";
        }

        return $headers;
    }

     private function getToken(): string
    {
        try {
            return Cache::remember(self::TOKEN_CACHE_KEY, self::TOKEN_EXPIRATION_MINUTES * 60, function () {
                $response = $this->client->post($this->getApiBaseUrl() . "/auth", [
                    'json' => [
                        "grant_type" => "password",
                        "username" => self::SEMOA_USERNAME,
                        "password" => self::SEMOA_PASSWORD,
                        "client_id" => self::SEMOA_CLIENT_ID,
                        "client_secret" => self::SEMOA_CLIENT_SECRET,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ]
                ]);

                $data = json_decode($response->getBody(), true);

                if (!isset($data['access_token'])) {
                    \Log::error('Token response missing access_token', $data);
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

      private function invalidateToken(): void
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
    }

     public function authentification()
    {
        try {
            $token = $this->getToken();
            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur d\'authentification: ' . $e->getMessage()], 500);
        }
    }

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


    
   

   


   


  
     public function createOrder(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'lastname' => 'required|string',
        //     'firstname' => 'required|string',
        //     'phone' => 'required|string',
        //     'amount' => 'required|numeric|min:100',
        //     'payment_method' => 'required|in:TMONEY,FLOOZ',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }

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

   

    public function processPayment(Request $request)
    {
        // \Log::info('Payment request received', $request->all());

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
        $annee=AnneeScolaire::where('active', true)->first();

        $etudiant =Candidature::where('etudiant_id', $etudiant->id)->where('annee_scolaire_id',$annee->id)->latest()->first();
        $niveau = $etudiant->niveau;
        $frais =  FraisScolarite::where('annee_scolaire_id', $annee->id)
            ->where('niveau_id', $niveau->id)
            ->first();
            
        $tranches=TranchePaiement::where('frais_scolarite_id', $frais->id)
            ->get();
            
        //On boucle sur les tranches pour trouver la première tranche non payée
        $trancheNonPaye = null;
        // On récupère la première tranche non payée et on répartit le paiement sur les tranches
        $montantRestant = $request->amount;
        $montantPaye = 0;
        // while ($montantRestant > 0) {
        //     foreach ($tranches as $tranche) {
        //         $montantTranchePaye = Paiement::where('etudiant_id', $etudiant->etudiant_id)
        //             ->where('tranche_paiement_id', $tranche->id)
        //             ->where('annule', false)
        //             ->sum('montant');

        //         $resteTranche = $tranche->montant - $montantTranchePaye;

        //         if ($resteTranche > 0) {
        //             $montantPaye = min($montantRestant, $resteTranche);

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

        //             $montantRestant -= $montantPaye;
        //         }
        //         if ($montantRestant <= 0) {
        //             break;
        //         }
        //     }
        //     // Si aucun reste à payer, on sort de la boucle
        //     break;
        // }
      
      

        // Enregistrement du paiement


     
            // \Log::debug('Token obtained', ['token' => substr($token, 0, 10) . '...']);

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
                     "gateway"=> [
                      "reference"=> $request->input("payment_method"),
                     ],
                     "currency"=> "XOF",
                     "callback_url" => "http://localhost:8000/espace-etudiant/mes-payements"
       
                    

                ]
            ]);


            \Log::info('API Response', ['response' => (string) $response->getBody()]);


            $data = json_decode($response->getBody(), true);
            \Log::info('Data after API call', $data);
            // return response()->json([
            //     'success' => true,
            //     'redirect' => "http://sandbox.cashpay.tg/facture/" . $data['order_reference'],
            //     'data' => $data
            // ]);
            
//             $gatewayId = $data['payments_method'][0]["id"] ; 
// $orderReference = $data['order_reference']; 

// $redirectUrl = "https://sandbox.cashpay.tg/facture/recap/{$orderReference}/{$gatewayId}";


// return redirect()->away($redirectUrl);

// Mapping des gateways avec leurs types d’URL
$gatewayConfigs = [
    '14f4597d-ef96-4263-8107-1e1970959133' => [ // SandboxSemoa
        'id' => 11,
        'type' => 'recap-sandbox',
    ],
    '016eb63c-f29d-4384-92e4-b1bd37ef69f8' => [ // FloozTG
        'id' => 1,
        'type' => 'recap',
    ],
    'a2c87957-1033-46e9-8706-056e45737de1' => [ // Tmoney
        'id' => 27,
        'type' => 'recap',
    ],
    '52bfd484-13ef-44f3-b128-adf7187779b0' => [ // Ecobank
        'id' => 6,
        'type' => 'recap',
    ],
    'f7bbfaef-eba3-4b82-ac31-61eb2b772289' => [ // Orabank
        'type' => 'external', // vers une URL fournie par l'API
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

// Récupération de la référence de commande de l'API
$orderReference = $data['order_reference'] ?? null;

if (!$orderReference) {
    return response()->json([
        'success' => false,
        'error' => 'Référence de commande manquante.'
    ], 500);
}

// Construction de l’URL selon le type
if ($config['type'] === 'recap') {
    $redirectUrl = "https://sandbox.cashpay.tg/facture/recap/{$orderReference}/{$config['id']}";
} elseif ($config['type'] === 'recap-sandbox') {
    $redirectUrl = "https://sandbox.cashpay.tg/facture/recap-sandbox/{$orderReference}/{$config['id']}";
} elseif ($config['type'] === 'external') {
    // Pour les paiements par carte bancaire, Semoa te retourne l’URL directe
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

// Redirection immédiate
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
// public function processPayment(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'lastname' => 'required|string|max:255',
//         'firstname' => 'required|string|max:255',
//         'phone' => 'required|string',
//         'amount' => 'required|numeric|min:100|max:1000000',
//         'payment_method' => 'required|in:TMONEY,FLOOZ',
//     ]);

//     if ($validator->fails()) {
//         return redirect()->back()->withErrors($validator)->withInput();
//     }

//     try {
//         $token = $this->getToken();

//         $response = $this->client->post($this->getApiBaseUrl() . "/orders", [
//             'headers' => $this->getHeaders($token),
//             'json' => [
//                 'amount' => (float) $request->input('amount'),
//                 'description' => 'Paiement via ' . config('app.name'),
//                 'client' => [
//                     'lastname' => $request->input('lastname'),
//                     'firstname' => $request->input('firstname'),
//                     'phone' => $request->input('phone'),
//                 ],
//                 'payment_method' => $request->input('payment_method'),
//             ]
//         ]);

//         $data = json_decode($response->getBody(), true);

//         // Passe toutes les données à la vue pour affichage
//         return view('etudiants.my-space.my-payment', ['paymentData' => $data]);

//     } catch (\Exception $e) {
//         \Log::error('API Request failed', ['message' => $e->getMessage()]);
//         return redirect()->back()->with('error', 'Erreur lors du traitement du paiement : ' . $e->getMessage());
//     }
// }

}










   

   

  
   

   





    



