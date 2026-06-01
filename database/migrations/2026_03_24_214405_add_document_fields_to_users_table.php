<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('identity_document_path')->nullable()->after('nif');
            $table->string('nif_document_path')->nullable()->after('identity_document_path');
            $table->string('diploma_document_path')->nullable()->after('nif_document_path');
            $table->string('cv_document_path')->nullable()->after('diploma_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'identity_document_path',
                'nif_document_path',
                'diploma_document_path',
                'cv_document_path'
            ]);
        });
    }
};