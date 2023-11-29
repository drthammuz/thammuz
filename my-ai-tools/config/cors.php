'<?php
return [
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => ['https://paravel.panacean.it'],
'allowed_origins_patterns' => [],
'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'X-Socket-ID'],
'supports_credentials' => true,
'max_age' => 3600,
];
