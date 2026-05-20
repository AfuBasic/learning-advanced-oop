<?php

declare(strict_types=1);

use App\Database\Migrations\MigrationContract;

return new class implements MigrationContract
{
    public function up(): string
    {
        return "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;";
    }

    public function down(): string
    {
        return "DROP TABLE IF EXISTS users;";
    }
};
