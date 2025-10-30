<?php

use App\Models\{Group};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('emploi_du_temps', function (Blueprint $table) {
			$table->dropColumn('grade_id');
			$table->foreignIdFor(Group::class);
		});
	}

	public function down(): void
	{
		Schema::table('emploi_du_temps', function (Blueprint $table) {
			$table->dropColumn('group_id');
			$table->unsignedBigInteger('grade_id');
		});
	}
};
