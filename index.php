<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/config');
$dotenv->load();
use App\Router;
$router = new Router();
$router->basePath($_ENV['APP_BASE_PATH'] ?? '/');