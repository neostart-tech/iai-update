<?php

use App\Models\Evaluation;
use App\Models\Salle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluation_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Evaluation::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Salle::class)->constrained()->cascadeOnDelete();
            $table->unsignedInteger('assigned_capacity')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_rooms');
    }
};
