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
        Schema::table('configurations', function (Blueprint $table) {
            $table->string('valueKey')->nullable()->after('value');
            $table->text('options')->nullable()->after('valueKey');
            $table->enum('type',['text','boolean','integer',"file",'textarea','select'])->nullable()->after('options');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn(['valueKey','options','type','select']);
        });
    }
};
