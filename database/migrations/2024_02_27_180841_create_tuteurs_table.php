<?php /** @noinspection DuplicatedCode */

use App\Models\Candidature;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('tuteurs', function (Blueprint $table) {
			$table->id();
			$table->string('nom');
			$table->string('prenom');
			$table->string('email');
			$table->string('profession');
			$table->string('employeur')->nullable();
			$table->string('adresse');
			$table->string('tel');
			$table->string('bp')->nullable();
			$table->string('fax')->nullable();
			$table->foreignIdFor(Candidature::class);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('tuteurs');
	}
};
