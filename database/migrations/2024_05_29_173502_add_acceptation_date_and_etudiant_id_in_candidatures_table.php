<?php

use App\Models\Etudiant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('candidatures', function (Blueprint $table) {
			$table->timestamp('acceptation_date')->nullable();
			$table->timestamp('end_accessibility_date')->nullable();
			$table->foreignIdFor(Etudiant::class)->nullable();
		});
	}

	public function down(): void
	{
		Schema::table('candidatures', function (Blueprint $table) {
			$table->dropColumn(['acceptation_date', 'end_accessibility_date', 'etudiant_id']);
		});
	}
};
