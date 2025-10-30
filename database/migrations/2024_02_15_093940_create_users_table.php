<?php

use App\Enums\GenreEnum;
use App\Models\{Grade, Role};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
	public function up()
	{
		Schema::create('users', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('prenom');
			$table->string('password');
			$table->string('email')->unique();
			$table->enum('genre', GenreEnum::values());
			$table->timestamp('email_verified_at')->nullable();
			$table->string('image');
			$table->string('matricule')->nullable()->unique();
			$table->longText('biographie')->nullable();
			$table->text('annee_admission')->nullable();
			$table->rememberToken();
			$table->string('slug');
			$table->foreignIdFor(Grade::class)->nullable();
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('users');
	}
}
