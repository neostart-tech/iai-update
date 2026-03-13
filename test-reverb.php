<?php
// test-reverb.php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\User;
use App\Events\MessageSent;
use App\Models\Message;

// Prenez un utilisateur
$user = User::find(1);
auth()->login($user);

// Créez un message de test
$message = new \App\Models\Message();
$message->body = "Test Reverb " . date('H:i:s');
$message->conversation_id = 1;
$message->sender_id = $user->id;
$message->sender_type = get_class($user);
$message->save();

// Déclenchez l'événement
event(new MessageSent($message));

echo "✅ Événement déclenché pour le message ID: {$message->id}\n";