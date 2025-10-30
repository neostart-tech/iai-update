<?php

use App\Models\AnneeScolaire;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('evenements', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->text('details')->nullable();
			$table->foreignIdFor(AnneeScolaire::class);
			$table->string('slug');
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('evenements');
	}
};
