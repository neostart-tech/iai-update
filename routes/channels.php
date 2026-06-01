<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{id}', function ($user,$id) {

    return DB::table('conversation_users')
        ->where('conversation_id',$id)
        ->where('participant_id',$user->id)
        ->where('participant_type',get_class($user))
        ->exists();
});

// Canal pour les notifications utilisateur
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canaux de support
Broadcast::channel('support.ticket.{id}', function ($user, $id) {
    return true; // Tout le monde peut écouter
});

Broadcast::channel('support.informaticiens', function ($user) {
    return true;
});
