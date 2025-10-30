<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('tuteurs', function (Blueprint $table) {
			$table->unsignedBigInteger('candidature_id')->change();
			$table->renameColumn('candidature_id', 'owner_id');
			$table->string('owner_type');
		});


		Schema::table('responsable_frais', function (Blueprint $table) {
			$table->unsignedBigInteger('candidature_id')->change();
			$table->renameColumn('candidature_id', 'owner_id');
			$table->string('owner_type');
		});


		Schema::table('albums', function (Blueprint $table) {
			$table->unsignedBigInteger('candidature_id')->change();
			$table->renameColumn('candidature_id', 'owner_id');
			$table->string('owner_type');
		});
	}


	public function down(): void
	{
		Schema::table('albums', function (Blueprint $table) {
			$table->dropColumn('owner_type');
			$table->renameColumn('owner_id', 'candidature_id');
		});

		Schema::table('responsable_frais', function (Blueprint $table) {
			$table->dropColumn('owner_type');
			$table->renameColumn('owner_id', 'candidature_id');
		});

		Schema::table('tuteurs', function (Blueprint $table) {
			$table->dropColumn('owner_type');
			$table->renameColumn('owner_id', 'candidature_id');
		});
	}
};
