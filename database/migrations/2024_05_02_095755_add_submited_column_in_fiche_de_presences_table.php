<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('fiche_de_presences', function (Blueprint $table) {
			$table->boolean('submitted')->default(false);
			$table->boolean('processed')->default(false);
		});
	}

	public function down(): void
	{
		Schema::table('fiche_de_presences', function (Blueprint $table) {
			$table->dropColumn(['submitted', 'processed']);
		});
	}
};
