<?php
namespace App\Helpers;

use App\Models\Configuration;

class ConfigHelper
{
    public static function getAppName()
    {
        $config = Configuration::where('key', 'nom_de_etablissement')->first();
        return $config ? $config->getAttribute('value') : 'Nom de l\'établissement non configuré';
    }

    public static function getAppLogo()
    {
        $config = Configuration::where('key', 'logo_etablissement')->first();
        return $config ? $config->getAttribute('value') : null;
    }

    public static function getAppTitreDe()
    {
        $config = Configuration::where('key', 'titre_du_directeur_des_etudes')->first();
        return $config ? $config->getAttribute('value') : 'Titre non configuré';
    }

    public static function getAppDe()
    {
        $config = Configuration::where('key', 'nom_complet_du_directeur_des_etudes')->first();
        return $config ? $config->getAttribute('value') : 'Nom non configuré';
    }

     public static function getSystemePedagogique()
    {
        $config = Configuration::where('key', 'systeme_pedagogique_de_etablissement')->first();
        return $config ? $config->getAttribute('value') : 'Non configuré';
    }

     public static function getAfficherChoixDate()
    {
        $config = Configuration::where('key', 'afficher_le_choix_des_dates_pour_formations')->first();
        return $config ? (int) $config->getAttribute('value') : false;
    }

    /**
     * Mode global de sélection des candidats : 'dossier' ou 'concours'.
     * Sert de repli pour les candidatures qui ne sont liées à aucune session de concours.
     */
    public static function getModeSelectionCandidats(): string
    {
        $config = Configuration::where('key', 'mode_selection_candidats')->first();
        return $config ? $config->getAttribute('value') : 'dossier';
    }

    public static function isModeConcoursActif(): bool
    {
        return self::getModeSelectionCandidats() === 'concours';
    }
}