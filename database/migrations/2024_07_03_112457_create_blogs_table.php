<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up()
	{
		Schema::create('blogs', function (Blueprint $table) {
			$table->id();
			$table->string('title');
			$table->string('image')->nullable();
			$table->longText('content');
			$table->timestamp('publication_date');
			$table->string('slug');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('blogs');
	}
};
