<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat,{conversationId}', function (User $user, $conversationId) {
    return $user->conversations()->where('id', $conversationId)->exists();
});

