<?php

use App\Models\{AnneeScolaire, Group, User};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('etudiant_group', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(User::class);
			$table->foreignIdFor(Group::class);
			$table->foreignIdFor(AnneeScolaire::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('etudiant_group');
	}
};
