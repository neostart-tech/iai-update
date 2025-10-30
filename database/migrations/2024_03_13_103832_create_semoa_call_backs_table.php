<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('semoa_call_backs', function (Blueprint $table) {
			$table->id();
			$table->json('data');
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('semoa_call_backs');
	}
};
