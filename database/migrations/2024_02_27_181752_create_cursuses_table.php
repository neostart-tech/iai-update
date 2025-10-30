<?php

use App\Models\Candidature;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('cursuses', function (Blueprint $table) {
			$table->id();
			$table->string('annee');
			$table->string('etablissement');
			$table->string('niveau');
			$table->foreignIdFor(Candidature::class);
		});
	}
	public function down(): void
	{
		Schema::dropIfExists('cursuses');
	}
};
