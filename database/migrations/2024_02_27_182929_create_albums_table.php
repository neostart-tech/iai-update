<?php

use App\Enums\TypeDiplomeEnum;
use App\Models\Candidature;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('albums', function (Blueprint $table) {
			$table->id();
			$table->string('lettre');
			$table->string('naissance');
			$table->string('diplome');
			$table->string('nationalite');
			$table->string('photo');
			$table->enum('type_diplome', TypeDiplomeEnum::values());
			$table->string('certificat_medical');
			$table->string('coupon')->nullable();
			$table->foreignIdFor(Candidature::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('albums');
	}
};
