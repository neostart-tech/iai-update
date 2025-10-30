<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (PostTooLargeException $e, $request) {
            // Retourne vers le formulaire avec un message clair et conserve les données saisies
            return back()
                ->withInput()
                ->withErrors([
                    'upload' => "Vos fichiers sont trop volumineux pour être envoyés. Réduisez la taille ou le nombre de fichiers, ou réessayez après l'augmentation des limites du serveur (post_max_size / upload_max_filesize).",
                ]);
        });
    }
}
