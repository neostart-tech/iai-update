<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('emploi_du_temps', function (Blueprint $table) {
			$table->unsignedBigInteger('owner_id')->nullable();
			$table->string('owner_type')->nullable();
			$table->dropConstrainedForeignId('enseignant_id');
		});
	}

	public function down(): void
	{
		Schema::table('emploi_du_temps', function (Blueprint $table) {
			$table->foreignId('enseignant_id')->nullable()->constrained('users');
			$table->dropColumn(['owner_id', 'owner_type']);
		});
	}
};
