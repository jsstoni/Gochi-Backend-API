<?php
return [
    'server' => $_ENV['SERVER'] ?? 'localhost',
    'user' => $_ENV['USER'] ?? 'root',
    'password' => $_ENV['PASSWORD'] ?? '',
    'database' => $_ENV['DATABASE'] ?? 'finanzas'
];
