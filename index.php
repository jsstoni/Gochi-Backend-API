<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/config');
$dotenv->load();
echo $_ENV['APP_BASE_PATH'];