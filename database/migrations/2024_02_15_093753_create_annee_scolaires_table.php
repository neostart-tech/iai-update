<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('annee_scolaires', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('slug');
			$table->string('code');
		});
	}
	public function down(): void
	{
		Schema::dropIfExists('annee_scolaires');
	}
};
