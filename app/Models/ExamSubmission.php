<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSubmission extends Model
{
    protected $table = 'exam_submissions';

    protected $fillable = [
        'evaluation_id',
        'etudiant_id',
        'question_id',
        'reponse',
        'is_correct',
        'points_obtenus',
        'submitted_at',
        'auto_saved_at',
        'ip_address',
        'user_agent',
        'metadata'
    ];

    protected $casts = [
        'reponse' => 'array',
        'is_correct' => 'boolean',
        'points_obtenus' => 'float',
        'submitted_at' => 'datetime',
        'auto_saved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * 🔴 NOUVEAU: Accesseur pour récupérer une valeur spécifique d'un tableau
     */
    public function getTableCellValue($row, $column)
    {
        $reponse = $this->reponse;
        
        if (!isset($reponse['type']) || $reponse['type'] !== 'complex_data') {
            return null;
        }
        
        $cellKey = "{$row}-{$column}";
        return $reponse['data'][$cellKey] ?? null;
    }

    /**
     * 🔴 NOUVEAU: Accesseur pour récupérer une partie spécifique d'une question multi-parties
     */
    public function getMultiPartValue($partIndex)
    {
        $reponse = $this->reponse;
        
        if (!isset($reponse['type']) || $reponse['type'] !== 'multi_parts') {
            return null;
        }
        
        $partKey = "part_{$partIndex}";
        return $reponse['data'][$partKey] ?? null;
    }

    /**
     * 🔴 NOUVEAU: Vérifier si une réponse complexe est complète
     */
    public function isComplexComplete()
    {
        $reponse = $this->reponse;
        
        if (!isset($reponse['type'])) {
            return true; // Réponse simple toujours complète
        }

        switch ($reponse['type']) {
            case 'complex_data':
                // Logique pour vérifier si toutes les cellules sont remplies
                // À adapter selon votre besoin
                return !empty($reponse['data']);
                
            case 'multi_parts':
                // Logique pour vérifier si toutes les parties sont remplies
                // À adapter selon votre besoin
                return !empty($reponse['data']);
                
            case 'structured_data':
                // Logique pour vérifier si tous les items sont remplis
                // À adapter selon votre besoin
                return !empty($reponse['data']);
                
            default:
                return true;
        }
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class, 'etudiant_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }
}