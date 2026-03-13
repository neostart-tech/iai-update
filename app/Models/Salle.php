<?php
// app/Models/Salle.php

namespace App\Models;

use App\Traits\Routing\{GenerateUniqueSlugTrait, ModelsSlugKeyTrait};
use Illuminate\Database\Eloquent\{Collection, Model};
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property Collection<array-key, EmploiDuTemp>|null emploiDuTemps
 */
class Salle extends Model
{
    use GenerateUniqueSlugTrait, ModelsSlugKeyTrait;

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'effectif',
        'type',
        'lien_reunion',
        'plateforme',
        'instructions',
        'annee_scolaire_id'
    ];

    protected $casts = [
        'type' => 'string'
    ];

    // Constantes pour les types
    const TYPE_PHYSIQUE = 'physique';
    const TYPE_VIRTUELLE = 'virtuelle';

    // Liste des plateformes supportées
    const PLATEFORMES = [
        'zoom' => 'Zoom',
        'teams' => 'Microsoft Teams',
        'meet' => 'Google Meet',
        'whatsapp' => 'WhatsApp',
        'discord' => 'Discord',
        'autres' => 'Autre'
    ];

    /**
     * Vérifie si la salle est virtuelle
     */
    public function getEstVirtuelleAttribute(): bool
    {
        return $this->type === self::TYPE_VIRTUELLE;
    }

    /**
     * Vérifie si la salle est physique
     */
    public function getEstPhysiqueAttribute(): bool
    {
        return $this->type === self::TYPE_PHYSIQUE;
    }

    /**
     * Formate le lien de réunion selon la plateforme
     */
    public function getLienReunionFormateAttribute(): ?string
    {
        if (!$this->lien_reunion) {
            return null;
        }

        // Si c'est déjà une URL complète
        if (filter_var($this->lien_reunion, FILTER_VALIDATE_URL)) {
            return $this->lien_reunion;
        }

        // Formater selon la plateforme
        return match($this->plateforme) {
            'zoom' => 'https://zoom.us/j/' . $this->lien_reunion,
            'meet' => 'https://meet.google.com/' . $this->lien_reunion,
            'teams' => 'https://teams.microsoft.com/l/meetup-join/' . $this->lien_reunion,
            'whatsapp' => 'https://chat.whatsapp.com/' . $this->lien_reunion,
            'discord' => 'https://discord.gg/' . $this->lien_reunion,
            default => $this->lien_reunion
        };
    }

    /**
     * Obtenir le nom de la plateforme
     */
    public function getPlateformeNomAttribute(): ?string
    {
        return self::PLATEFORMES[$this->plateforme] ?? $this->plateforme;
    }

    /**
     * Scope pour les salles virtuelles
     */
    public function scopeVirtuelles($query)
    {
        return $query->where('type', self::TYPE_VIRTUELLE);
    }

    /**
     * Scope pour les salles physiques
     */
    public function scopePhysiques($query)
    {
        return $query->where('type', self::TYPE_PHYSIQUE);
    }

    public function emploiDuTemps(): HasMany
    {
        return $this->hasMany(EmploiDuTemp::class);
    }
}