<?php

use App\Models\{Announcement, Etudiant};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('announcement_etudiant', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(Etudiant::class);
			$table->foreignIdFor(Announcement::class);
			$table->boolean('applied')->nullable()->default(false);
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('announcement_etudiant');
	}
};
