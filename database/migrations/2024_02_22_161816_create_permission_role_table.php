<?php

use App\Models\{Permission, Role};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('permission_role', function (Blueprint $table) {
			$table->id();
			$table->foreignIdFor(Permission::class);
			$table->foreignIdFor(Role::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('permission_role');
	}
};
