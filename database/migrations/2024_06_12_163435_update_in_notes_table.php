<?php

use App\Models\Evaluation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('notes', function (Blueprint $table) {
			$table->dropColumn('slug');
			$table->dropConstrainedForeignId('fiche_id');
			$table->foreignIdFor(Evaluation::class);
		});
	}

	public function down(): void
	{
		Schema::table('notes', function (Blueprint $table) {
			$table->dropColumn(['evaluation_id']);
			$table->char('slug');
			$table->foreignId('fiche_id')->constrained('fiche_de_presences');
		});
	}
};
