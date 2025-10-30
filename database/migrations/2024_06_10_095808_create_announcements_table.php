<?php

use App\Enums\TypeAnnonceEnum;
use App\Models\Advertiser;
use App\Models\AnneeScolaire;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

	public function up(): void
	{
		Schema::create('advertisers', function (Blueprint $table) {
			$table->id();
			$table->string('nom', 127);
			$table->longText('details');
			$table->string('site', 127)->nullable();
			$table->string('ville', 63);
			$table->string('email', 31);
			$table->string('slug');
		});

		Schema::create('announcements', function (Blueprint $table) {
			$table->id();
			$table->enum('type', TypeAnnonceEnum::values());
			$table->string('ville', 63);
			$table->longText('content');
			$table->boolean('status');
			$table->string('slug');
			$table->foreignIdFor(AnneeScolaire::class);
			$table->foreignIdFor(Advertiser::class);
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('announcements');
		Schema::dropIfExists('advertisers');
	}
};
