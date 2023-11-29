
<?php


require __DIR__.'/vendor/autoload.php';

use OpenAI\Laravel\Facades\OpenAI;

// Access the API key from the configuration
$apiKey = config('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');

// Rest of your PHP code here

$result = OpenAI::chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!'],
    ],
]);

echo $result->choices[0]->message->content; // Hello! How can I assist you today?
