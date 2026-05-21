<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use App\Database\Connection;
use RuntimeException;

class MigrationRunner
{
    public function __construct(
        protected Connection $connection,
        protected string $migrationsPath
    ) {}

    public function run(): array
    {
        $logs = [];
        /**
         * 1. We ensure the migrations table exists for ledger
         */
        $this->ensureLedgerTableExists();

        /**
         * 2. Scan Disk for available scripts
         */
        $filesOnDisk = $this->getMigrationFiles();

        /**
         * 3. Query ledger for already executed scripts
         */
        $executedFiles = $this->getExecutedMigrations();
        /**
         * 4. Isolate outstanding migrations
         */
        $pendingMigrations = array_diff($filesOnDisk, $executedFiles);

        if (empty($pendingMigrations)) {
            $logs[] = "No pending migrations found. Database schema is fully optimized";
            return $logs;
        }

        /**
         * 5. Execute each pending script sequentially
         */
        foreach ($pendingMigrations as $file) {
            $fullPath = $this->migrationsPath . "/" . $file;

            $migration = require $fullPath;
            if (!$migration instanceof MigrationContract) {
                throw new RuntimeException("Migration file [{$file}] must implement the MigrationContract");
            }

            $sql = $migration->up();

            /**
             * Execute the schema modification DDL script
             */
            $this->connection->rawExec($sql);

            $this->logInLedger($file);
            $logs[] = "Successfully Migrated: <span style='color: #0d9488;'>{$file}</span>";
        }

        return $logs;
    }

    /**
     * This method ensure the base migration table exists. 
     * The table serves as a ledger to track what has been executed and what has not
     * @return void
     */
    protected function ensureLedgerTableExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration_name VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;";

        $this->connection->rawExec($sql);
    }

    protected function getMigrationFiles(): array
    {
        if (!is_dir($this->migrationsPath)) {
            throw new RuntimeException("Migrations directory path does not exist");
        }

        $files = scandir($this->migrationsPath);
        return array_values(array_filter($files, fn(string $file) => str_ends_with($file, ".php")));
    }
    /**
     * This gets migrations that have been executed
     * @return array
     */
    public function getExecutedMigrations(): array
    {
        $executedMigrations = $this->connection->select("SELECT migration_name from migrations ORDER BY id ASC");
        return array_column($executedMigrations, 'migration_name');
    }

    /**
     * We record the migration file names that have been run
     * @param string $name
     * @return void
     */
    public function logInLedger(string $name): void
    {
        $this->connection->execute("INSERT INTO migrations (migration_name) VALUES (:name)", ['name' => $name]);
    }
}
