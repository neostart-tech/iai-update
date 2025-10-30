<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('evaluations', fn(Blueprint $table) => $table->boolean('has_anonymat')->nullable()->default(false));
	}

	public function down(): void
	{
		Schema::table('evaluations', fn(Blueprint $table) => $table->dropColumn('has_anonymat'));
	}
};
