<?php

use App\Models\{AnneeScolaire, Note, User};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('note_variations', function (Blueprint $table) {
			$table->id();
			$table->string('from');
			$table->string('to');
			$table->text('motif');
			$table->timestamp('created_at');
			$table->foreignIdFor(User::class);
			$table->foreignIdFor(Note::class);
			$table->foreignIdFor(AnneeScolaire::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('note_variations');
	}
};
