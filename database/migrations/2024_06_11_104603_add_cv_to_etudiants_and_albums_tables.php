<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('etudiants', function (Blueprint $table) {
			$table->longText('cv')->nullable();
		});


		Schema::table('albums', function (Blueprint $table) {
			$table->string('cv')->after('coupon')->nullable();
		});
	}

	public function down(): void
	{
		Schema::table('etudiants', function (Blueprint $table) {
			$table->dropColumn('cv');
		});

		Schema::table('albums', function (Blueprint $table) {
			$table->dropColumn('cv');
		});
	}
};
