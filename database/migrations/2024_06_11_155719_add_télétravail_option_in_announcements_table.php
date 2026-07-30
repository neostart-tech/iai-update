<?php

use App\Enums\TypeAnnonceEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		if (Schema::hasTable('announcements')) {
			if (Schema::hasColumn('announcements', 'type')) {
				try {
					Schema::table('announcements', function (Blueprint $table) {
						$table->string('type')->change();
					});
				} catch (\Throwable $e) {
					// Ignores error if doctrine/dbal or driver does not support enum modify
				}
			} else {
				Schema::table('announcements', function (Blueprint $table) {
					$table->string('type')->nullable()->after('id');
				});
			}
		}
	}

	public function down(): void
	{
	}
};
