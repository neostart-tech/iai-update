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
}