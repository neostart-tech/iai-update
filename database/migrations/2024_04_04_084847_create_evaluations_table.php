<?php

use App\Models\{AnneeScolaire, Group, Salle, UniteValeur};
use App\Enums\TypeEvaluationEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('evaluations', function (Blueprint $table) {
			$table->id();
			$table->string('slug');
			$table->foreignIdFor(Group::class);
			$table->foreignIdFor(UniteValeur::class);
			$table->foreignIdFor(Salle::class);
			$table->boolean('published')->default(false);
			$table->enum('type', TypeEvaluationEnum::values());
			$table->timestamp('debut');
			$table->timestamp('fin');
			$table->foreignIdFor(AnneeScolaire::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('evaluations');
	}
};
