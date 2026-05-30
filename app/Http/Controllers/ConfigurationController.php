<?php

namespace App\Http\Controllers;

use App\Http\Resources\ParametreResource;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Illuminate\Support\Str as SupportStr;
use Storage;
use Str;

class ConfigurationController extends Controller
{
    public function index()
    {
        $keys = [
            ['key' => 'stat_etudiants_formes', 'value' => '520', 'type' => 'text', 'name' => 'Étudiants formés', 'group' => 'Communication'],
            ['key' => 'stat_diplomes', 'value' => '410', 'type' => 'text', 'name' => 'Diplômés', 'group' => 'Communication'],
            ['key' => 'stat_partenaires', 'value' => '84', 'type' => 'text', 'name' => 'Partenaires', 'group' => 'Communication'],
            ['key' => 'stat_insertion_pro', 'value' => '76', 'type' => 'text', 'name' => 'Insertion pro.', 'group' => 'Communication']
        ];
        foreach($keys as $k) {
            if (!\App\Models\Configuration::where('key', $k['key'])->exists()) {
                \App\Models\Configuration::create($k);
            }
        }
        
        return ParametreResource::collection(Configuration::all());
        return view('admin.config.index', [
            'configurations' => Configuration::all()
        ]);
    }
public function update(Request $request)
{
    $configurations = Configuration::all();

    foreach ($configurations as $config) {
        $slug = SupportStr::slug($config->key, '_');

        switch ($config->type) {
            case 'file':
                if ($request->hasFile("config_value.$slug")) {
                    $file = $request->file("config_value.$slug");
                    $path = $file->store('configuration', 'public');

                    // Supprimer l'ancien fichier
                    if ($config->value && FacadesStorage::disk('public')->exists($config->value)) {
                        FacadesStorage::disk('public')->delete($config->value);
                    }

                    $config->update(['value' => $path]);
                } elseif ($request->has("delete_file.$slug") && $request->input("delete_file.$slug") == '1') {
                    // Supprimer le fichier si demandé
                    if ($config->value && FacadesStorage::disk('public')->exists($config->value)) {
                        FacadesStorage::disk('public')->delete($config->value);
                    }
                    $config->update(['value' => null]);
                }
                break;

            case 'boolean':
                // Vérifier si la clé existe dans la requête
                if ($request->has("config_value.$slug")) {
                    $value = $request->input("config_value.$slug");
                    
                    // Conversion explicite en entier (0 ou 1)
                    // "0" (string) devient 0 (int), "1" devient 1
                    $config->update([
                        'value' => intval($value)
                    ]);
                    
                    // Log pour déboguer
                    // \Log::info("Mise à jour boolean {$slug}: valeur reçue = {$value}, convertie = " . intval($value));
                } else {
                    // Si la clé n'existe pas, c'est que le champ n'a pas été envoyé
                    // Dans ce cas, on ne fait rien (on garde l'ancienne valeur)
                    // \Log::info("Boolean {$slug} non présent dans la requête, valeur conservée: " . $config->value);
                }
                break;

            default: // text, select, etc.
                if ($request->has("config_value.$slug")) {
                    $config->update([
                        'value' => $request->input("config_value.$slug")
                    ]);
                }
                break;
        }
    }

    // Recharger les configurations pour s'assurer d'avoir les dernières valeurs
    $updatedConfigurations = Configuration::all();
    
    return ParametreResource::collection($updatedConfigurations);
}
}
