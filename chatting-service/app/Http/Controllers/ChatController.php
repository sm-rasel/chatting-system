<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function searchUsers(Request $request): JsonResponse
    {
        $query = $request->query('q');
        $user = User::where('id', '!=', Auth::id())
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query} %")
                    ->orWhere('email', 'LIKE', "%{$query}%");
                })->get();
        return response()->json($user);
    }


    public function getConversations(): JsonResponse
    {
        $conversations = Auth::user()->conversations()
            ->with(['userOne', 'userTwo', 'messages'])
            ->get()
            ->map(function ($conversation) {
                $otherUser = $conversation->user_one_id === Auth::id()
                    ? $conversation->userTwo
                    : $conversation->userOne;
                return [
                    'id' => $conversation->id,
                    'other_user' => $otherUser,
                    'last_message' => $conversation->messages->last(),
                ];
            });

        return response()->json($conversations);
    }

    public function startConversation(Request $request): JsonResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $existingConversation = Conversation::where(function ($query) use ($request) {
            $query->where('user_one_id', Auth::id())
                ->where('user_two_id', $request->user_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('user_one_id', $request->user_id)
                ->where('user_two_id', Auth::id());
        })->first();

        if ($existingConversation) {
            return response()->json($existingConversation);
        }

        $conversation = Conversation::create([
            'user_one_id' => Auth::id(),
            'user_two_id' => $request->user_id,
        ]);

        return response()->json($conversation);
    }

    public function getMessages($conversationId): JsonResponse
    {
        $conversation = Conversation::findOrFail($conversationId);

        if (!$this->canAccessConversation($conversation)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->with(['sender', 'receiver'])
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request, $conversationId): JsonResponse
    {
        $request->validate(['message' => 'required|string']);

        $conversation = Conversation::findOrFail($conversationId);

        if (!$this->canAccessConversation($conversation)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chat = Chat::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $this->getReceiverId($conversation),
            'conversation_id' => $conversationId,
            'message' => $request->message,
        ]);

        broadcast(new MessageSent($chat))->toOthers();

        return response()->json($chat);
    }

    private function canAccessConversation(Conversation $conversation): bool
    {
        return $conversation->user_one_id === Auth::id() || $conversation->user_two_id === Auth::id();
    }

    private function getReceiverId(Conversation $conversation)
    {
        return $conversation->user_one_id === Auth::id() ? $conversation->user_two_id : $conversation->user_one_id;
    }

}
