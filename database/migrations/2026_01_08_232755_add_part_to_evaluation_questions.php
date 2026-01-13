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
        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->string('part')->nullable()->after('type'); // "I", "II", "III"
            $table->string('part_title')->nullable()->after('part'); // "QCM", "Étude de cas"
            $table->integer('order_in_part')->default(0)->after('part_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluation_questions', function (Blueprint $table) {
            $table->dropColumn(['part', 'part_title', 'order_in_part']);
        });
    }
};
