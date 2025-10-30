<?php

use App\Models\{AnneeScolaire, FicheDePresence, UniteValeur};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('notes', function (Blueprint $table) {
			$table->id();
			$table->float('note', 4);
			$table->foreignId('etudiant_id')->constrained('users');
			$table->foreignId('ue_id')->constrained('unite_valeurs');
			$table->foreignId('fiche_id')->constrained('fiche_de_presences');
//			$table->foreignIdFor(UniteValeur::class);
//			$table->foreignIdFor(FicheDePresence::class);
			$table->string('slug');
			$table->timestamps();
			$table->foreignIdFor(AnneeScolaire::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('notes');
	}
};
