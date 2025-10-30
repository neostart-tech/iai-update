<?php

use App\Models\{AnneeScolaire, FicheDePresence, User};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('fiche_de_presences', function (Blueprint $table) {
			$table->dropConstrainedForeignIdFor(User::class, 'surveillant_1_id');
			$table->dropConstrainedForeignIdFor(User::class, 'surveillant_2_id');
		});

		Schema::create('fiche_de_presence_user', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(AnneeScolaire::class);
			$table->foreignIdFor(FicheDePresence::class);
			$table->foreignIdFor(User::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('fiche_de_presence_user');

		Schema::table('fiche_de_presences', function (Blueprint $table) {
			$table->dropColumn(['surveillant_1_id', 'surveillant_2_id']);
		});
	}
};
