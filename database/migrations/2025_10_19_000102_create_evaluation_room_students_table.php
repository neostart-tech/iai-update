<?php

use App\Models\Etudiant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluation_room_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_room_id')->constrained('evaluation_rooms')->cascadeOnDelete();
            $table->foreignIdFor(Etudiant::class)->constrained()->cascadeOnDelete();
            $table->unique(['evaluation_room_id','etudiant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_room_students');
    }
};
