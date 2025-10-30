<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use Illuminate\Http\Request;
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
            $slug = Str::slug($config->key, '_'); // même transformation que dans le formulaire
    
            if ($request->hasFile("config_value.$slug")) {
                $file = $request->file("config_value.$slug");
    
                $path = $file->store('configuration', 'public');
    
                if ($config->value && Storage::disk('public')->exists($config->value)) {
                    Storage::disk('public')->delete($config->value);
                }
    
                $config->update(['value' => $path]);
            } else {
                $config->update(['value' => $request->input("config_value.$slug")]);
            }
        }
    
        return redirect()->back()->with('success', 'Configurations mises à jour avec succès.');
    }



}
