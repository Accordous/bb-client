<?php

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// Load the proper .env file
if (file_exists(__DIR__ . '/../.env.testing')) {
    Dotenv::createImmutable(__DIR__ . '/../', '.env.testing')->load();
} 