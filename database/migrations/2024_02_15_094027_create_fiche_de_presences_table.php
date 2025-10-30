<?php

use App\Models\AnneeScolaire;
use App\Enums\{TypeEvaluationEnum, TypeProgrammeEnum};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('fiche_de_presences', function (Blueprint $table) {
			$table->id();
			$table->date('date');
			$table->time('debut');
			$table->time('fin');
			$table->enum('type_fiche', TypeProgrammeEnum::values());
			$table->enum('type_evaluation', TypeEvaluationEnum::values())->nullable();
			$table->foreignId('enseignant_id')->nullable()->constrained(table: 'users');
			$table->foreignId('surveillant_1_id')->nullable()->constrained(table: 'users');
			$table->foreignId('surveillant_2_id')->nullable()->constrained(table: 'users');
			$table->string('slug');
			$table->foreignIdFor(AnneeScolaire::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('fiche_de_presences');
	}
};
