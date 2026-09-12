<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\UniteValeur;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('unite_valeurs', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
        });

        // Generate clean unique slugs for existing records
        $uvs = UniteValeur::all();
        foreach ($uvs as $uv) {
            $nom = $uv->nom ?? 'matiere';
            $baseSlug = Str::slug($nom);
            $slug = $baseSlug;
            
            $i = 1;
            while (UniteValeur::where('slug', $slug)->where('id', '!=', $uv->id)->exists()) {
                $slug = $baseSlug . '-' . $i;
                $i++;
            }
            $uv->slug = $slug;
            $uv->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unite_valeurs', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
