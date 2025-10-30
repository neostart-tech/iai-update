<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Apply changes in a single ALTER to avoid default constraint errors on TIMESTAMP columns
        DB::statement("ALTER TABLE evaluations 
            MODIFY COLUMN debut DATETIME NOT NULL,
            MODIFY COLUMN fin DATETIME NOT NULL,
            MODIFY COLUMN type ENUM('Devoir','Examen','Interrogation','TP','Exposé') NOT NULL");
    }

    public function down(): void
    {
        // Revert enum to original three values
        DB::statement("ALTER TABLE evaluations 
            MODIFY COLUMN type ENUM('Devoir','Examen','Interrogation') NOT NULL,
            MODIFY COLUMN debut TIMESTAMP NOT NULL,
            MODIFY COLUMN fin TIMESTAMP NOT NULL");
    }
};
