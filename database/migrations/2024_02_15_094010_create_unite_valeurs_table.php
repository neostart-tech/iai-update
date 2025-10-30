<?php

use App\Models\{AnneeScolaire, UniteEnseignement, User};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('unite_valeurs', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('code');
			$table->string('cm');
			$table->string('td');
			$table->string('tp');
			$table->string('ec');
			$table->string('slug');
			$table->integer('coefficient');
			$table->foreignId('enseignant_id')->constrained('users');
			$table->foreignId('unite_enseignement_id')->constrained();
			$table->foreignIdFor(AnneeScolaire::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('unite_valeurs');
	}
};
