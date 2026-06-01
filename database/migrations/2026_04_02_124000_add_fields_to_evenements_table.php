<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('evenements', function (Blueprint $table) {
            $table->string('image')->nullable()->after('details');
            $table->string('type')->default('internal')->after('image'); // internal, public
            $table->string('destination')->default('all')->after('type'); // intranet, website, all
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evenements', function (Blueprint $table) {
            $table->dropColumn(['image', 'type', 'destination']);
        });
    }
};
