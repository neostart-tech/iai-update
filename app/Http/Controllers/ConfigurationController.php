<?php

namespace App\Http\Controllers;

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
                }
                break;

            case 'boolean':
                // Checkbox cochée = 1, non cochée = 0
                $config->update([
                    'value' => $request->has("config_value.$slug") ? 1 : 0
                ]);
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

    return redirect()->back()->with('success', 'Configurations mises à jour avec succès.');
}



}
