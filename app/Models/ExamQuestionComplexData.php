<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestionComplexData extends Model
{
    protected $table = 'exam_question_complex_data';

    protected $fillable = [
        'question_id',
        'data_type',
        'configuration',
        'cell_configuration',
        'data',
        'cell_data',
        'metadata'
    ];

    protected $casts = [
        'configuration' => 'array',
        'cell_configuration' => 'array',
        'data' => 'array',
        'cell_data' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }

    /**
     * Récupérer la configuration du tableau
     */
    public function getTableauConfig(): array
    {
        return [
            'colonnes' => $this->configuration['colonnes'] ?? [],
            'lignes' => count($this->data ?? []),
            'cellules' => $this->cell_configuration ?? []
        ];
    }

    /**
     * Récupérer les données avec types
     */
    public function getDonneesAvecTypes(): array
    {
        $result = [];
        
        foreach ($this->data ?? [] as $index => $ligne) {
            $ligneAvecTypes = [];
            foreach ($this->configuration['colonnes'] ?? [] as $colIndex => $colonne) {
                $valeur = $ligne["col_{$colIndex}"] ?? null;
                $type = $colonne['type'] ?? 'text';
                
                // Typer la valeur selon le type de colonne
                if ($type === 'number' && $valeur !== null) {
                    $valeur = is_numeric($valeur) ? (float) $valeur : $valeur;
                }
                
                $ligneAvecTypes["col_{$colIndex}"] = $valeur;
            }
            $result[] = $ligneAvecTypes;
        }
        
        return $result;
    }
}