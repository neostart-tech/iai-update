<?php

use App\Enums\GenreEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('etudiants', function (Blueprint $table) {
			$table->id();
			$table->string('slug');
			$table->string('nom');
			$table->string('nom_jeune_fille')->nullable();
			$table->string('prenom');
			$table->string('email')->unique();
			$table->string('tel');
			$table->string('password');
			$table->string('matricule')->unique();
			$table->string('image');
			$table->timestamp('date_naissance');
			$table->string('lieu_naissance');
			$table->string('nationalite');
			$table->string('annee_admission',4);
			$table->enum('genre',GenreEnum::values());
			$table->timestamp('email_verified_at')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('etudiants');
	}
};
