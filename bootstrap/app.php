<?php

declare(strict_types=1);

use App\Core\Kernel;
use App\Database\Connection;

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';
    if (str_starts_with($class, $prefix)) {
        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace("\\", "/", $relativeClass) . ".php";
        if (file_exists($file)) {
            require $file;
        }
    }
});

$dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8mb4", $_ENV['DB_HOST'], $_ENV['DB_DATABASE']);
Connection::boot(
    $dsn,
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD'],
);

return new Kernel();
