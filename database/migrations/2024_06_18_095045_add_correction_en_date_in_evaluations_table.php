<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('evaluations', function (Blueprint $table) {
			$table->timestamp('correction_end_date')->nullable();
			$table->timestamp('correction_submission_date')->nullable();
		});
	}

	public function down(): void
	{
		Schema::table('evaluations', function (Blueprint $table) {
			$table->dropColumn(['correction_end_date', 'correction_submission_date']);
		});
	}
};
