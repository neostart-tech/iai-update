<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluation_room_supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_room_id')->constrained('evaluation_rooms')->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->unique(['evaluation_room_id','user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_room_supervisors');
    }
};
