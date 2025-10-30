<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use function Laravel\Prompts\table;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('contacts', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('email');
			$table->string('tel');
			$table->longText('message');
			$table->string('slug');
			$table->boolean('status');
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('contacts');
	}
};
