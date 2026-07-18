<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidatureFieldConfig;
use App\Models\Configuration;
use Illuminate\Http\Request;

class CandidatureFieldConfigController extends Controller
{
    private function payload()
    {
        $identifiant = Configuration::where('key', 'identifiant_dossier_source')->first();

        return [
            'champs' => CandidatureFieldConfig::orderBy('label')->get(),
            'identifiant_dossier_source' => $identifiant?->value ?? 'code',
            'identifiant_dossier_source_options' => collect(explode(',', $identifiant?->options ?? ''))
                ->filter()
                ->map(fn ($opt) => array_combine(['value', 'label'], explode('|', $opt)))
                ->values(),
        ];
    }

    public function index()
    {
        return response()->json($this->payload());
    }

    /**
     * Mise à jour en masse des interrupteurs obligatoire/optionnel/afficher, du
     * label de chaque champ, et du réglage "identifiant de dossier affiché" (code
     * de convocation ou numéro de bordereau).
     * Payload attendu : { champs: [{ id, obligatoire, afficher, label }, ...], identifiant_dossier_source? }
     */
    public function update(Request $request)
    {
        $request->validate([
            'champs' => ['required', 'array'],
            'champs.*.id' => ['required', 'exists:candidature_field_configs,id'],
            'champs.*.obligatoire' => ['required', 'boolean'],
            'champs.*.afficher' => ['required', 'boolean'],
            'champs.*.label' => ['required', 'string', 'max:255'],
            'identifiant_dossier_source' => ['nullable', 'in:code,numero_bordereau'],
        ]);

        foreach ($request->input('champs') as $champ) {
            CandidatureFieldConfig::where('id', $champ['id'])->update([
                // Un champ masqué ne peut pas rester "obligatoire" en base — évite un état
                // incohérent (obligatoire=true sur un champ jamais présenté au candidat).
                'obligatoire' => $champ['afficher'] && $champ['obligatoire'],
                'afficher' => $champ['afficher'],
                'label' => $champ['label'],
            ]);
        }

        if ($request->filled('identifiant_dossier_source')) {
            Configuration::where('key', 'identifiant_dossier_source')
                ->update(['value' => $request->input('identifiant_dossier_source')]);
        }

        return response()->json($this->payload());
    }
}
