<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function (User $user, $conversationId) {
    $conversation = Conversation::findOrFail($conversationId);
    return $user->id === $conversation->user_one_id || $user->id === $conversation->user_two_id;
//    return $user->conversations()->where('id', $conversationId)->exists();
});

