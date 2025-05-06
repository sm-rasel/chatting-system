<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/user/search', [ChatController::class, 'searchUsers']);
    Route::get('/conversation' , [ChatController::class, 'getConversations']);
    Route::post('/conversation' , [ChatController::class, 'startConversation']);
    Route::get('/conversations/{conversationId}/messages' , [ChatController::class, 'getMessages']);
    Route::post('/conversations/{conversationId}/messages' , [ChatController::class, 'sendMessage']);
});
