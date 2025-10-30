<?php

use App\Models\{AnneeScolaire, UniteValeur, User};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('user_unite_valeur', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(User::class);
			$table->foreignIdFor(UniteValeur::class);
			$table->foreignIdFor(AnneeScolaire::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('user_unite_valeur');
	}
};
