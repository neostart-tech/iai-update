<?php

namespace App\Exports;

use App\Models\Etudiant;
use App\Models\EtudiantGroup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EtudiantsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Retourner tous les étudiants avec leurs infos
     */
    public function collection()
    {
        return Etudiant::with(['etudiantGroups.filiere', 'etudiantGroups.niveau', 'etudiantGroups.group'])
            ->get();
    }

    /**
     * Map les données pour Excel
     */
    public function map($etudiant): array
    {
        $group = $etudiant->etudiantGroups->first(); // Premier groupe si plusieurs

        return [
            $etudiant->matricule,
            $etudiant->nom,
            $etudiant->prenom,
            $etudiant->email,
            $etudiant->tel,
            $etudiant->genre->value ==="Masculin" ? "M" : "F",
            $etudiant->date_naissance ? $etudiant->date_naissance->format('Y-m-d') : null,
            $group->filiere->nom ?? null,
            $group->niveau->libelle ?? null,
            $group->group->nom ?? null,
        ];
    }

    /**
     * Entêtes Excel
     */
    public function headings(): array
    {
        return [
            'Matricule',
            'Nom',
            'Prénom',
            'Email',
            'Téléphone',
            'Genre',
            'Date de naissance',
            'Filière',
            'Niveau',
            'Groupe',
        ];
    }
}
