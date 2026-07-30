<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertion des paramètres par défaut pour éviter tout souci au déploiement
        DB::table('configurations')->updateOrInsert(
            ['key' => 'matricule_prefix'],
            ['value' => 'ESC', 'created_at' => now(), 'updated_at' => now()]
        );
        
        DB::table('configurations')->updateOrInsert(
            ['key' => 'email_domain'],
            ['value' => 'escen.university', 'created_at' => now(), 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('configurations')->whereIn('key', ['matricule_prefix', 'email_domain'])->delete();
    }
};
