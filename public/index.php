<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Polyfill for PHP < 8.4 request_parse_body()
if (!class_exists('RequestParseBodyException')) {
    class RequestParseBodyException extends \Exception {}
}

if (!function_exists('request_parse_body')) {
    function request_parse_body(): array
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array(strtoupper($method), ['PUT', 'DELETE', 'PATCH', 'QUERY'])) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
            if (str_contains(strtolower($contentType), 'application/json')) {
                $content = file_get_contents('php://input');
                $data = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [is_array($data) ? $data : [], []];
                }
            } elseif (str_contains(strtolower($contentType), 'application/x-www-form-urlencoded')) {
                $content = file_get_contents('php://input');
                parse_str($content, $data);
                return [$data, []];
            }
        }
        return [$_POST, $_FILES];
    }
}


// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
