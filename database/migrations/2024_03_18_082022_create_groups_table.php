<?php

use App\Models\{AnneeScolaire, Filiere};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('groups', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('slug');
			$table->foreignIdFor(Filiere::class);
			$table->foreignIdFor(AnneeScolaire::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('groups');
	}
};
