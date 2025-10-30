<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('announcements', function (Blueprint $table) {
			$table->string('file_path')->nullable();
			$table->string('title');
		});
	}

	public function down(): void
	{
		Schema::table('announcements', function (Blueprint $table) {
			$table->dropColumn(['title', 'file_path']);
		});
	}
};
