<?php

use Illuminate\Support\Facades\Broadcast;

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

// Canal public pour les salles de visioconférence
Broadcast::channel('room.{room}', function ($user, $room) {
    // Retourner true pour permettre à tous d'accéder au canal public
    return true;
});
