<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Monarobase\CountryList\CountryListFacade;

class CandidaturesEtudeDossierExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $candidatures;
    protected $countries;

    public function __construct(Collection $candidatures)
    {
        $this->candidatures = $candidatures;
        // Le formulaire de dépôt enregistre le code pays (ex: "TG"), pas le nom complet.
        $this->countries = CountryListFacade::getList(config('app.locale'));
    }

    public function collection()
    {
        return $this->candidatures;
    }

    public function headings(): array
    {
        return [
            'Dossier N°',
            'Nom',
            'Prénom',
            'Nom de jeune fille',
            'Genre',
            'Date de naissance',
            'Lieu de naissance',
            'Nationalité',
            'Adresse',
            'Téléphone 1',
            'Téléphone 2',
            'Téléphone 3',
            'Email',
            'Numéro de table',
            'Année du BAC',
            'Série',
            'Mention au BAC',
            'Type de diplôme',
            'Niveau',
            'Filière',
            'Documents fournis',
            'Nom du tuteur/parent',
            'Téléphone du tuteur/parent',
            'Email du tuteur/parent',
            'Profession du tuteur/parent',
            'Responsable des frais',
            'Nombre de tuteurs déclarés',
            'Statut du dossier',
            'Motif',
            'Date de dépôt',
        ];
    }

    public function map($candidature): array
    {
        $responsable = $candidature->tuteurs->firstWhere('responsable_des_frais', true)
            ?? $candidature->tuteurs->first();

        return [
            $candidature->code ?? '--',
            $candidature->nom ?? '--',
            $candidature->prenom ?? '--',
            $candidature->nom_jeune_fille ?? '--',
            $candidature->genre?->value ?? $candidature->genre ?? '--',
            $candidature->date_naissance ? $candidature->date_naissance->format('d/m/Y') : '--',
            $candidature->lieu_naissance ?? '--',
            $this->countries[$candidature->nationalite] ?? $candidature->nationalite ?? '--',
            $candidature->adresse ?? '--',
            $candidature->tel ?? '--',
            $candidature->tel2 ?? '--',
            $candidature->tel3 ?? '--',
            $candidature->email ?? '--',
            $candidature->numero_table ?? '--',
            $candidature->annee_bac ?? '--',
            $candidature->serie ?? '--',
            $candidature->mention_bac ?? '--',
            $candidature->album->type_diplome ?? '--',
            $candidature->niveau->libelle ?? '--',
            $candidature->filiere->nom ?? '--',
            $candidature->submittedDocuments->count(),
            $responsable ? trim($responsable->nom . ' ' . $responsable->prenom) : '--',
            $responsable->tel ?? '--',
            $responsable->email ?? '--',
            $responsable->profession ?? '--',
            $responsable && $responsable->responsable_des_frais ? 'Oui' : 'Non',
            $candidature->tuteurs->count(),
            $this->statutLabel($candidature),
            $candidature->motif ?? '--',
            $candidature->created_at ? $candidature->created_at->format('d/m/Y H:i') : '--',
        ];
    }

    private function statutLabel($candidature): string
    {
        if ($candidature->dossier_valide) {
            return 'Validé';
        }
        if ($candidature->rectification_expected) {
            return 'En correction';
        }
        if ($candidature->motif) {
            return 'Rejeté';
        }
        return 'En étude';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
