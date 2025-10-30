<?php

use App\Enums\TypeProgrammeEnum;
use App\Models\Evenement;
use App\Models\Grade;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('emploi_du_temps', function (Blueprint $table) {
			$table->enum('type_programme', TypeProgrammeEnum::values())->change();
			$table->foreignIdFor(Grade::class)->nullable()->change();
			$table->text('details')->nullable();
			$table->foreignIdFor(Evenement::class)->nullable();
		});
	}

	public function down(): void
	{
		Schema::table('emploi_du_temps', function (Blueprint $table) {
			$table->dropColumn(['details', 'evenement_id' ]);
		});
	}
};
