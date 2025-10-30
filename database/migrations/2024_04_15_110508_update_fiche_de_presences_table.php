<?php

use App\Enums\{TypeEvaluationEnum, TypeProgrammeEnum};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('fiche_de_presences', function (Blueprint $table) {
			$table->dropConstrainedForeignId('enseignant_id');
			$table->dropColumn([
				'fin',
				'date',
				'debut',
				'type_fiche',
				'type_evaluation',
			]);

			$table->morphs('controllable');
		});
	}

	public function down(): void
	{
		Schema::table('fiche_de_presences', function (Blueprint $table) {
			$table->dropMorphs('controllable');
			$table->date('date');
			$table->time('debut');
			$table->time('fin');
			$table->enum('type_fiche', TypeProgrammeEnum::values());
			$table->enum('type_evaluation', TypeEvaluationEnum::values())->nullable();
			$table->foreignId('enseignant_id')->nullable()->constrained(table: 'users');
		});
	}
};
