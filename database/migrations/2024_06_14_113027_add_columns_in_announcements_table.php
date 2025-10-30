<?php

use App\Enums\TypeContratEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('announcements', function (Blueprint $table) {
			$table->renameColumn('type', 'type_annonce');
			$table->enum('type_contrat', TypeContratEnum::values());
			$table->string('duration', 63)->nullable();
		});
	}

	public function down(): void
	{
		Schema::table('announcements', function (Blueprint $table) {
			$table->dropColumn(['type_contrat', 'duration']);
			$table->renameColumn('type_annonce', 'type');
		});
	}
};
