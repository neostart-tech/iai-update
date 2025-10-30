<?php

use App\Enums\TypeAnnonceEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('announcements', function (Blueprint $table) {
			$table->enum('type', TypeAnnonceEnum::values())->change();
		});
	}

	public function down(): void
	{
	}
};
