<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('current_envs', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('valeur');
		});
	}
	public function down(): void
	{
		Schema::dropIfExists('current_envs');
	}
};
