<?php

// Vercel Serverless Entrypoint for NusaGlow ERP (Laravel 12 + SQLite)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try {
    $tmpStorage = '/tmp/storage';
    $tmpBootstrapCache = '/tmp/bootstrap-cache';
    $dirs = [
        $tmpStorage . '/app/public',
        $tmpStorage . '/framework/cache/data',
        $tmpStorage . '/framework/sessions',
        $tmpStorage . '/framework/testing',
        $tmpStorage . '/framework/views',
        $tmpStorage . '/logs',
        $tmpBootstrapCache,
    ];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    // Copy pre-seeded SQLite database to writable /tmp/database.sqlite on cold start
    $sourceDb = __DIR__ . '/../database/database.sqlite';
    $tmpDb = '/tmp/database.sqlite';
    if (!file_exists($tmpDb)) {
        if (file_exists($sourceDb)) {
            @copy($sourceDb, $tmpDb);
        } else {
            @touch($tmpDb);
        }
        @chmod($tmpDb, 0666);
    }

    // Force serverless-compatible environment variables (redirect bootstrap caches to writable /tmp)
    $envOverrides = [
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
        'APP_PACKAGES_CACHE' => $tmpBootstrapCache . '/packages.php',
        'APP_SERVICES_CACHE' => $tmpBootstrapCache . '/services.php',
        'APP_CONFIG_CACHE' => $tmpBootstrapCache . '/config.php',
        'APP_ROUTES_CACHE' => $tmpBootstrapCache . '/routes.php',
        'APP_EVENTS_CACHE' => $tmpBootstrapCache . '/events.php',
    ];
    foreach ($envOverrides as $k => $v) {
        putenv("{$k}={$v}");
        $_ENV[$k] = $v;
        $_SERVER[$k] = $v;
    }

    define('LARAVEL_START', microtime(true));

    require __DIR__ . '/../vendor/autoload.php';

    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath($tmpStorage);

    $app->handleRequest(\Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "NusaGlow ERP Serverless Startup Error:\n";
    echo $e->getMessage() . "\n\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo $e->getTraceAsString();
}
