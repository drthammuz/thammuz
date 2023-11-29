<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FlowchartController extends Controller
{
    public function generate(Request $request)
    {
	// Substitute with your actual GPT-4 API endpoint and API key
        $gptApiEndpoint = 'https://api.openai.com/v1/engines/gpt-4-1106-preview/completions';
        $gptApiKey = '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';

        // Sending prompt to GPT-4 API
        $gptResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $gptApiKey
        ])->post($gptApiEndpoint, [
            'prompt' => $request->input('prompt'),
            'max_tokens' => 150 // Adjust as needed
        ]);

        // Check if GPT-4 API request was successful
        if ($gptResponse->successful()) {
            $mermaidSyntax = $gptResponse->json()['choices'][0]['text'];

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
            // Handle GPT-4 API error
            return response()->json(['error' => 'GPT-4 API Error'], 500);
        }
    }
}
