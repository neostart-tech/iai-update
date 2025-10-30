<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::table('password_resets', fn(Blueprint $table) => $table->rename('password_reset_tokens'));
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('password_reset_tokens', fn(Blueprint $table) => $table->rename('password_resets'));
	}
};
