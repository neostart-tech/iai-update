<?php

namespace App\Services;

use Firebase\JWT\JWT;

class LiveKitService
{
    /**
     * Génère un Token JWT pour rejoindre une salle virtuelle LiveKit
     */
    public function generateToken(string $roomName, string $identity, string $name, bool $isTeacher = false): string
    {
        $apiKey = (string) (config('services.livekit.key') ?: 'APIUcSGqPHgvYsk');
        $apiSecret = (string) (config('services.livekit.secret') ?: 'xMtyARLU6Rf1LlsyWddaS5EGXzBGKOw4XJgYDPClJhd');

        $now = time();
        $expiration = $now + (60 * 60 * 6); // Valide 6 heures

        $payload = [
            'iss' => $apiKey,
            'sub' => $identity,
            'name' => $name,
            'nbf' => $now - 10, // Marge de 10s pour décalage d'horloge
            'exp' => $expiration,
            'video' => [
                'room' => $roomName,
                'roomJoin' => true,
                'canPublish' => true,
                'canSubscribe' => true,
                'canPublishData' => true,
                'roomAdmin' => $isTeacher,
            ]
        ];

        return JWT::encode($payload, $apiSecret, 'HS256', $apiKey);
    }
}
