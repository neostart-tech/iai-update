<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->string('tel')->unique();
			$table->string('nom_jeune_fille')->nullable();
			$table->timestamp('date_naissance')->nullable();
			$table->string('lieu_naissance')->nullable();
			$table->string('nationalite')->nullable();
		});
	}

	public function down(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(['tel', 'nom_jeune_fille', 'date_naissance', 'nom_jeune_fille', 'nationalite']);
		});
	}
};
