<?php

// Vercel Serverless Entrypoint for NusaGlow ERP (Laravel 12 + SQLite)
$tmpStorage = '/tmp/storage';
$dirs = [
    $tmpStorage . '/app/public',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/framework/testing',
    $tmpStorage . '/framework/views',
    $tmpStorage . '/logs',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// Copy pre-seeded SQLite database to writable /tmp/database.sqlite on cold start
$sourceDb = __DIR__ . '/../database/database.sqlite';
$tmpDb = '/tmp/database.sqlite';
if (!file_exists($tmpDb) && file_exists($sourceDb)) {
    @copy($sourceDb, $tmpDb);
    @chmod($tmpDb, 0666);
}

// Set default serverless environment variables if not explicitly configured in Vercel Dashboard
$defaults = [
    'APP_NAME' => 'NusaGlow ERP',
    'APP_ENV' => 'local',
    'APP_DEBUG' => 'true',
    'APP_KEY' => 'base64:I+rxJ4CHqCutNaTw6MrQBjANw/2o1q1rHDq8jleL+AQ=',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => $tmpDb,
    'SESSION_DRIVER' => 'cookie',
    'CACHE_STORE' => 'array',
    'LOG_CHANNEL' => 'stderr',
    'VIEW_COMPILED_PATH' => $tmpStorage . '/framework/views',
];
foreach ($defaults as $k => $v) {
    if (!getenv($k)) {
        putenv("{$k}={$v}");
        $_ENV[$k] = $v;
        $_SERVER[$k] = $v;
    }
}

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath($tmpStorage);

$app->handleRequest(\Illuminate\Http\Request::capture());

