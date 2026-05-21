<?php

declare(strict_types=1);

namespace App\Core;

use App\Database\Connection;
use App\Database\Migrations\MigrationRunner;
use Exception;

class Kernel
{
    public function run(): void
    {
        try {
            $runner = new MigrationRunner(Connection::resolve(), __DIR__ . '/../../migrations/');
            $results = $runner->run();
            $this->renderDashboard($results);
        } catch (Exception $e) {
            $this->renderFatalError($e);
        }
    }

    protected function renderDashboard(array $results): void
    {
        echo "<body style='background: #0f172a; color: #e2e8f0; font-family: monospace; padding: 40px;'>";
        echo "<h2>Master Migration Runner Engine Dashboard</h2><hr style='border-color: #334155;'><pre>";
        foreach ($results as $log) {
            echo "» {$log}\n";
        }
        echo "</pre></body>";
    }

    protected function renderFatalError(Exception $e): void
    {
        echo "<body style='background: #7f1d1d; color: #fef2f2; font-family: monospace; padding: 40px;'>";
        echo "<h2>Fatal Engine Error Caught By Kernel</h2><hr><pre>" . $e->getMessage() . "</pre></body>";
    }
}
