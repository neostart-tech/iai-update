<?php

use App\Models\UniteValeur;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('notes', function (Blueprint $table) {
			$table->removeColumn('ue_id');
			$table->double('note', 4, 2)->nullable()->change();
			$table->foreignIdFor(UniteValeur::class);
		});
	}

	public function down(): void
	{
		Schema::table('notes', function (Blueprint $table) {
			$table->removeColumn('unite_valeur_id');
			$table->string('ue_id')->nullable();
		});
	}
};
