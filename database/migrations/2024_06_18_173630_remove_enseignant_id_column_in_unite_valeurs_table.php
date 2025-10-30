<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('unite_valeurs', function (Blueprint $table) {
			$table->dropConstrainedForeignId('enseignant_id');
		});
	}

	public function down(): void
	{
		Schema::table('unite_valeurs', function (Blueprint $table) {
			$table->foreignId('enseignant_id')->nullable()->constrained('users');
		});
	}
};
