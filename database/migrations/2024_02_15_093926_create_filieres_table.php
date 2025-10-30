<?php

use App\Models\AnneeScolaire;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('filieres', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('code');
			$table->string('image')->nullable();
			$table->text('description')->nullable();
			$table->string('slug');
			$table->foreignIdFor(AnneeScolaire::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('filieres');
	}
};
