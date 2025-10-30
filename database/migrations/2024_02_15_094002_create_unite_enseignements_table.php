<?php

use App\Models\{AnneeScolaire, Periode};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('unite_enseignements', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('code');
			$table->string('slug');
			$table->integer('credit')->nullable();
			$table->foreignIdFor(Periode::class);
			$table->foreignIdFor(AnneeScolaire::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('unite_enseignements');
	}
};
