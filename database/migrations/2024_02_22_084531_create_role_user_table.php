<?php

use App\Models\{Role, User};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('role_user', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(User::class);
			$table->foreignIdFor(Role::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('role_user');
	}
};
