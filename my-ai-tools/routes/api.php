<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redis;

use App\Http\Controllers\FlowchartController;
use App\Http\Controllers\ChatbotController;
use App\Events\PublicMessageSent;


use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;


Route::post('/generate-narrative', function (Request $request) {
    $prompt = $request->input('prompt');
    
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('OPENAI_API_KEY')
    ])->post('https://api.openai.com/v1/chat/completions', [
        'model' => 'gpt-4-1106-preview',
        'messages' => [
            ['role' => 'user', 'content' => $prompt],
            // Add more messages as needed
        ],
        'max_tokens' => 2000
    ]);

    if (isset($response->json()['error'])) {
        logger()->error('Error from GPT-4 API:', ['error' => $response->json()['error']]);
        return response()->json(['error' => 'Error processing the narrative'], 500);
    }

    return ['narrative' => $response->json()['choices'][0]['message']['content']];
});

Route::post('/public-chat', [ChatbotController::class, 'sendPublicMessage']);
Route::get('/public-chat-history', [ChatbotController::class, 'getPublicChatHistory']);

Route::post('/chatbot', 'App\Http\Controllers\ChatbotController@chat');
Route::get('/chat-history', [ChatbotController::class, 'getChatHistory']);


Route::post('login', [AuthenticatedSessionController::class, 'store']);
Route::post('logout', [AuthenticatedSessionController::class, 'destroy']);
Route::post('register', [RegisteredUserController::class, 'store']);
Route::post('generate-flowchart', [FlowchartController::class, 'generate']);

// Password reset routes
Route::post('forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('reset-password', [NewPasswordController::class, 'store']);

Route::get('/user', function (Request $request) {
    if (Auth::check()) {
        return Response::json($request->user());
    } else {
        return response()->json(['message' => 'User not authenticated'], 401);
    }
});

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
     ->middleware(['auth:sanctum', 'throttle:6,1'])
     ->name('verification.send');

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
     ->middleware(['auth:sanctum', 'signed', 'throttle:6,1'])
     ->name('verification.verify');