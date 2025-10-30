<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('class_committee_members')) {
            Schema::create('class_committee_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('etudiant_id');
                $table->enum('role', ['delegue','delegue_adjoint','secretaire','secretaire_adjoint']);
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->unique(['group_id','role'], 'uniq_group_role');
                $table->index(['etudiant_id','group_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_committee_members');
    }
};
