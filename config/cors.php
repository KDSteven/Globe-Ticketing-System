<?php
/**
 * CORS Middleware
 * Restricts cross-origin requests to known allowed origins only.
 * Handles OPTIONS preflight requests.
 */
function apply_cors(): void
{
    $allowed = [
        'http://localhost',
        'http://globe-ticketing-system.test',
        'http://localhost:80',
    ];

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

    if ($origin !== '' && in_array($origin, $allowed, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Vary: Origin');
    }

    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    header('Access-Control-Max-Age: 86400');

    // Handle preflight — respond immediately and exit
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
