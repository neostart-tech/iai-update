<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}

// Ce n'est pas une erreur de code PHP mais de configuration serveur/Laravel.

// Pour corriger l'erreur 413 (Content Too Large) :
// 1. Augmente la taille maximale des fichiers uploadés dans ton fichier php.ini :
/*
    upload_max_filesize = 20M
    post_max_size = 20M
*/
// 2. Si tu utilises Laravel, tu peux aussi augmenter la limite dans config/filesystems.php ou config/upload.php si tu en as un.

// 3. Si tu utilises Nginx ou Apache, augmente aussi la limite côté serveur web :
// Pour Nginx : ajoute dans la config du site
/*
    client_max_body_size 20M;
*/
// Pour Apache : ajoute dans .htaccess ou la config du site
/*
    LimitRequestBody 20971520
*/

// 4. Redémarre ton serveur web et PHP après modification.

// 5. (Optionnel) Tu peux aussi limiter côté JS la taille du fichier avant l'envoi pour prévenir l'utilisateur si le fichier est trop gros.
