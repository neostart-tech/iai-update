<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('evenements', function (Blueprint $table) {
			$table->renameColumn('date', 'start_date');
			$table->timestamp('end_date')->nullable();
		});
	}

	public function down(): void
	{
		Schema::table('evenements', function (Blueprint $table) {
			$table->dropColumn('end_date');
			$table->renameColumn('start_date', 'date');
		});
	}
};
