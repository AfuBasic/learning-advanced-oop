<?php

declare(strict_types=1);

namespace App\Database\Migrations;

interface MigrationContract
{
    public function up(): string;
    public function down(): string;
}
