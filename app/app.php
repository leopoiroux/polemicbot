<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Noweh\TwitterApi\Client as TwitterClient;

// Only allowed for cli
if (PHP_SAPI !== 'cli') {
    die('Not allowed');
}

// Load .env data
$dotenv = Dotenv::createUnsafeImmutable(__DIR__.'/config', '.env');
$dotenv->safeLoad();

$start = microtime(true);

echo 'Hello Polemic';

echo "\n" . 'Execution time ' . round(microtime(true) - $start, 2) . ' seconds';
