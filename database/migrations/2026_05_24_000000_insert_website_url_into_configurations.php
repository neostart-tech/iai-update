<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Configuration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Configuration::updateOrCreate(
            ['key' => 'URL du site web'],
            ['value' => 'http://localhost:3000', 'type' => 'text']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Configuration::where('key', 'URL du site web')->delete();
    }
};
