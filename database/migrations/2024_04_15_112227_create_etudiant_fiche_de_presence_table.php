<?php

use App\Models\{AnneeScolaire, Etudiant, FicheDePresence};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('etudiant_fiche_de_presence', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(Etudiant::class);
			$table->foreignIdFor(FicheDePresence::class);
			$table->boolean('was_present');
			$table->foreignIdFor(AnneeScolaire::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('etudiant_fiche_de_presence');
	}
};
