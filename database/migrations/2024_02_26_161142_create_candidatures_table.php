<?php

use App\Models\AnneeScolaire;
use App\Enums\{GenreEnum, TypeDiplomeEnum, TypePieceIdentiteEnum};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('candidatures', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('nom_jeune_fille')->nullable();
			$table->string('prenom');
			$table->enum('genre', GenreEnum::values());
			$table->timestamp('date_naissance');
			$table->string('lieu_naissance');
			$table->string('nationalite');
			$table->string('tel')->unique();
			$table->string('bp')->nullable();
			$table->string('fax')->nullable();
			$table->text('hobbit')->nullable();
			$table->string('email')->unique();
			$table->string('password');

			$table->boolean('dossier_valide')->default(false);
			$table->timestamp('validation_date')->nullable();
			$table->boolean('frais_paye')->default(false);
			$table->timestamp('frai_paye_date')->nullable();
			$table->boolean('participation')->default(false);
			$table->timestamp('participation_date')->nullable();
			$table->boolean('admission')->default(false);
			$table->timestamp('admission_date')->nullable();
			$table->text('motif')->nullable();
			$table->boolean('rectification_expected')->default(false);


			$table->string('slug');
			$table->string('code')->nullable();
			$table->foreignIdFor(AnneeScolaire::class);
			$table->rememberToken();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('candidatures');
	}
};
