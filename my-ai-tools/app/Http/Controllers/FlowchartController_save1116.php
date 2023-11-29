<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FlowchartController extends Controller
{
    public function generate(Request $request)
    {
        // Substitute with your actual GPT-4 Turbo API endpoint and API key
        $gptApiEndpoint = 'https://api.openai.com/v1/chat/completions';
        $gptApiKey = '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'; // Replace with your actual API key

        // Sending prompt to GPT-4 Turbo API
        $gptResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $gptApiKey,
            'Content-Type' => 'application/json'
        ])->post($gptApiEndpoint, [
            'model' => 'gpt-4-1106-preview', // Specify the GPT-4 Turbo model
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => $request->input('prompt')]
            ]
        ]);

        \Log::info('GPT-4 Turbo API Response:', $gptResponse->json());

        // Check if GPT-4 Turbo API request was successful
        if ($gptResponse->successful()) {
            $responseContent = $gptResponse->json();
            $mermaidSyntax = end($responseContent['choices'])['message']['content'];

            // Now send this syntax to the Mermaid CLI API
            $mermaidApiEndpoint = 'http://135.181.158.154:3000/generate-mermaid';
            $mermaidResponse = Http::post($mermaidApiEndpoint, [
                'syntax' => $mermaidSyntax,
            ]);

            // Check if Mermaid CLI API request was successful
            if ($mermaidResponse->successful()) {
                // Return the URL of the generated SVG
                return response()->json(['svg_url' => $mermaidResponse->body()]);
            } else {
                // Handle Mermaid CLI API error
                return response()->json(['error' => 'Mermaid CLI API Error'], 500);
            }
        } else {
            // Handle GPT-4 Turbo API error
            return response()->json(['error' => 'GPT-4 Turbo API Error'], 500);
        }
    }
}
