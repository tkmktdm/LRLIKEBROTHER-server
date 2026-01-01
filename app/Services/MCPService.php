<?php

use Illuminate\Support\Facades\Http;

$response = Http::post(
    'http://localhost:3333/tools/create_task',
    [
        'title' => $toolArgs['title'],
    ]
);

$result = $response->json()['result'];
