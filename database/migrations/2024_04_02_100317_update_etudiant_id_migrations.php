<?php

use App\Models\Etudiant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('notes', function (Blueprint $table) {
//			$table->foreignIdFor(Etudiant::class);
		});

		Schema::table('etudiant_group', function (Blueprint $table) {
			$table->dropColumn('user_id');
			$table->foreignIdFor(Etudiant::class);
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('etudiant_group', function (Blueprint $table) {
			$table->dropColumn('etudiant_id');
			$table->unsignedBigInteger('user_id');
		});

		Schema::table('notes', function (Blueprint $table) {
//			$table->dropColumn('etudiant_id');
		});
	}
};
