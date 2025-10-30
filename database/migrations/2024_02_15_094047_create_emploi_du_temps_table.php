<?php

use App\Enums\TypeProgrammeEnum;
use App\Models\{AnneeScolaire, Grade, Salle};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('emploi_du_temps', function (Blueprint $table) {
			$table->id();
			$table->timestamp('debut')->nullable();
			$table->timestamp('fin')->nullable();
			$table->foreignId('uv_id')->nullable()->constrained('unite_valeurs');
			$table->enum('type_programme', TypeProgrammeEnum::values());
			$table->string('slug');
			$table->foreignIdFor(Grade::class);
			$table->foreignIdFor(Salle::class);
			$table->foreignId('enseignant_id')->nullable()->constrained('users');
			$table->foreignIdFor(AnneeScolaire::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('emploi_du_temps');
	}
};
