<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FlowchartController extends Controller
{
public function generate(Request $request)
{
    //*log*
    $logDir = base_path('errorlogs');
    if (!is_dir($logDir) || !is_writable($logDir)) {
        // Handle the error appropriately, maybe create the directory or change permissions
    }

    //*log*
    $logId = date('YmdHis') . '-' . uniqid();
    $logFilePath = $logDir . '/' . $logId . '.log';

    try {
        //*log*
        $logData = ['user_input' => $request->all()];
        file_put_contents($logFilePath, json_encode($logData) . PHP_EOL, FILE_APPEND);
        
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
                ['role' => 'user', 'content' => 'Your job is to create a creative flowchart based on the user prompt with mermaid syntax, left to right, using different colors when it can enhance the visualization. Promote broad flowcharts over long linear ones, for better visualization in a browser. It is IMPERATIVE that the response only contains the syntax as it will be read as code. If you are using the word "end" in a Flowchart node, capitalize the entire word or any of the letters (e.g., "End" or "END"). Typing "end" in all lowercase letters will break the Flowchart. User prompt:'],
//(to add background colors to entities in the flowchart, use syntax like this example: style laravel_app fill:#9437)
                // , your response should be in the following format: flowchart TB;subgraph frontend [Frontend];user_prompt[User enters prompt] --> js_send_prompt[JS sends prompt to Laravel];get_svg_url[Frontend receives SVG URL] --> display_svg[Display SVG in browser];end;subgraph laravel_app [Laravel App];js_send_prompt --> laravel_receive[Laravel receives prompt];laravel_receive --> send_to_gpt_api[Laravel sends to GPT API];send_to_gpt_api --> receive_mermaid_syntax[Laravel receives Mermaid syntax];receive_mermaid_syntax --> send_to_mermaid_cli[Laravel sends to Mermaid CLI];laravel_receive_svg_url[Laravel receives SVG URL] --> return_svg_url[Laravel returns SVG URL to Frontend];end;subgraph mermaid_cli_app [Mermaid CLI App];send_to_mermaid_cli --> generate_mmd[CLI generates .mmd file];generate_mmd --> generate_svg[CLI generates .svg file];generate_svg --> laravel_receive_svg_url;end;return_svg_url --> get_svg_url (This is an example of the format you should answer in as well as an explanation of how this flowchart-project works). 
                ['role' => 'user', 'content' => $request->input('prompt')]
            ]
        ]);

        \Log::info('GPT-4 Turbo API Response:', $gptResponse->json());

        // Check if GPT-4 Turbo API request was successful
        if ($gptResponse->successful()) {
            $responseContent = $gptResponse->json();
            $mermaidSyntax = end($responseContent['choices'])['message']['content'];
        
            // New logic to isolate Mermaid syntax
            $pattern = '/```mermaid\s*(.*?)\s*```/s';
            preg_match($pattern, $mermaidSyntax, $matches);
            
            if (!empty($matches[1])) {
            $mermaidSyntax = $matches[1] ?? ''; // Only keep the Mermaid syntax, excluding the backticks and 'mermaid'
        } else {
            if (preg_match('/flowchart (TD|LR|TB|RL|BT|LR)\s*(.*)/s', $mermaidSyntax, $syntaxMatches)) {
                $mermaidSyntax = trim($syntaxMatches[0]);
            } else {
                $mermaidSyntax = ''; // Default to empty if no valid syntax is found
            }
            $mermaidSyntax = preg_replace('/\\\\n/', "\n", $mermaidSyntax);
        }
       


            //*log*
            $logData = ['gpt_response' => $gptResponse->json()];
            file_put_contents($logFilePath, json_encode($logData) . PHP_EOL, FILE_APPEND);
            $logData = ['mermaid_syntax' => $mermaidSyntax];
            file_put_contents($logFilePath, json_encode($logData) . PHP_EOL, FILE_APPEND);

            // Now send this syntax to the Mermaid CLI API
            $mermaidApiEndpoint = 'http://localhost:3000/generate-mermaid';
            $mermaidResponse = Http::withHeaders([
                'Content-Type' => 'text/plain'
            ])->post($mermaidApiEndpoint, $mermaidSyntax);

            // Check if Mermaid CLI API request was successful
            if ($mermaidResponse->successful()) {
    $svgUrl = $mermaidResponse->body();
    
    // Only log the SVG URL
    $logData = ['mermaid_cli_response' => $svgUrl];
    file_put_contents($logFilePath, json_encode($logData) . PHP_EOL, FILE_APPEND);

    // Return SVG URL as plain text
    return response($svgUrl, 200)->header('Content-Type', 'text/plain');
} else {
    // Handle Mermaid CLI API error and log
    $errorResponse = $mermaidResponse->body(); // Assuming the error message is plain text
    $logData = ['mermaid_cli_error' => $errorResponse];
    file_put_contents($logFilePath, json_encode($logData) . PHP_EOL, FILE_APPEND);

    return response('Mermaid CLI API Error', 500)->header('Content-Type', 'text/plain');
}
        }
    } catch (\Exception $e) {
        $logData = ['error' => $e->getMessage()];
        file_put_contents($logFilePath, json_encode($logData) . PHP_EOL, FILE_APPEND);
        return response()->json(['error' => $e->getMessage()], 500);

    }
    }
}