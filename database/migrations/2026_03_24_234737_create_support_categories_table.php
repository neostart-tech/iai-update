<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('color')->default('#6366f1');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Insertion des catégories par défaut
        DB::table('support_categories')->insert([
            [
                'name' => 'Technique',
                'slug' => 'technique',
                'icon' => 'computer',
                'color' => '#3b82f6',
                'description' => 'Problèmes techniques, bugs, logiciels',
                'order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Matériel',
                'slug' => 'materiel',
                'icon' => 'printer',
                'color' => '#10b981',
                'description' => 'Pannes matérielles, équipements',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Réseau',
                'slug' => 'reseau',
                'icon' => 'wifi',
                'color' => '#8b5cf6',
                'description' => 'Problèmes de connexion, internet',
                'order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Administratif',
                'slug' => 'administratif',
                'icon' => 'document',
                'color' => '#f59e0b',
                'description' => 'Documents administratifs, inscriptions',
                'order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Autre',
                'slug' => 'autre',
                'icon' => 'question',
                'color' => '#6b7280',
                'description' => 'Autres demandes',
                'order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('support_categories');
    }
};