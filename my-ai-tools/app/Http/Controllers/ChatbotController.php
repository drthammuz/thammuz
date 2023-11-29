<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\ChatMessage;
use GuzzleHttp\Client;
use App\Events\PublicMessageSent;

class ChatbotController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client(['headers' => ['Authorization' => 'Bearer ' . env('OPENAI_API_KEY')]]);
    }

    public function chat(Request $request)
    {
        $userMessage = $request->input('message');

        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'json' => [
                'model' => 'gpt-4-1106-preview', // Use appropriate model
                'max_tokens' => 4096, // Set the maximum number of tokens for the response
                'messages' => [
                    ['role' => 'user', 'content' => $request->input('message')]
                ]
            ]
        ]);
    
        $responseBody = json_decode((string) $response->getBody(), true);
        $botResponse = $responseBody['choices'][0]['message']['content'] ?? '';

        // Save to database
        $chatMessage = new ChatMessage();
        $chatMessage->user_id = auth()->id(); // Ensure you have user authentication in place
        $chatMessage->user_message = $userMessage;
        $chatMessage->bot_response = $botResponse;
        $chatMessage->save();

        return response()->json($responseBody);
    }


    public function getChatHistory()

    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    // Assuming you have user authentication and each message is linked to a user_id
    $userId = auth()->id();

    // Fetch messages from the database
    $messages = ChatMessage::where('user_id', $userId)
                           ->orderBy('created_at', 'asc')
                           ->get(['user_message', 'bot_response', 'created_at']);

    return response()->json($messages);
}
public function sendPublicMessage(Request $request)
{

    $request->validate([
        'message' => 'required|string'
    ]);

    $message = new ChatMessage();
    $message->user_id = auth()->id();
    $message->user_message = $request->input('message');
    $message->chat_type = 'public';
    $message->save();

    $userId = auth()->id(); // Or use $request->input('userId')
    $userName = auth()->user()->name; // Or use $request->input('userName'

    broadcast(new PublicMessageSent($message, $userName))->toOthers();

    return response()->json($message);
}

public function getPublicChatHistory()
{
    $messages = ChatMessage::where('chat_type', 'public')
                           ->orderBy('created_at', 'asc')
                           ->get(['user_id', 'user_message', 'created_at']);

    return response()->json($messages);
}





    // Add additional methods for handling Vision and DALL-E requests
}

