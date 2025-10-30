<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn('grade_id');
			$table->foreignIdFor(Group::class)->nullable();
			$table->string('image')->nullable()->change();
		});
	}

	public function down(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->string('image')->nullable()->change();
			$table->renameColumn('group_id', 'grade_id');
		});
	}
};
