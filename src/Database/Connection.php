<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;

class Connection
{
    protected static ?self $instance = null;

    protected PDO $pdo;

    private function __construct(string $dsn, string $username, string $password)
    {
        try {
            $this->pdo = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new RuntimeException("Database driver connection failure: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public static function boot(string $dsn, string $username, string $password): self
    {
        if (static::$instance === null) {
            static::$instance = new static($dsn, $username, $password);
        }

        return static::$instance;
    }

    public static function resolve(): self
    {
        if (static::$instance === null) {
            throw new RuntimeException("Database engine has not been booted. Call Connection::boot() first.");
        }
        return static::$instance;
    }

    public function select(string $query, array $bindings = []): array
    {
        try {
            $statement = $this->pdo->prepare($query);
            $statement->execute($bindings);
            return $statement->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException("Database Selection Failure: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function execute(string $query, array $bindings = []): int
    {
        try {
            $statement = $this->pdo->prepare($query);
            $statement->execute($bindings);
            return $statement->rowCount();
        } catch (PDOException $e) {
            throw new RuntimeException("Database Execution Failure: " . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function rawExec(string $query): int
    {
        return $this->pdo->exec($query);
    }
}
