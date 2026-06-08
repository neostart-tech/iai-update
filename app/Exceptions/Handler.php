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
            $message = "Vos fichiers sont trop volumineux pour être envoyés. Réduisez la taille ou le nombre de fichiers.";
            
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => ['upload' => [$message]]
                ], 413);
            }

            // Retourne vers le formulaire avec un message clair et conserve les données saisies
            return back()
                ->withInput()
                ->withErrors([
                    'upload' => $message,
                ]);
        });
    }
}
