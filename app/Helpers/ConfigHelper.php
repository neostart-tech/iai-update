<?php
namespace App\Helpers;

use App\Models\Configuration;

class ConfigHelper
{
    public static function getAppName()
    {
        $config = Configuration::where('key', 'Nom de l\'établissement')->first();
        return $config ? $config->getAttribute('value') : 'Nom de l\'établissement non configuré';
    }

    public static function getAppLogo()
    {
        $config = Configuration::where('key', 'Logo de l\'établissement')->first();
        return $config ? $config->getAttribute('value') : null;
    }

    public static function getAppTitreDe()
    {
        $config = Configuration::where('key', 'Titre du Chargé des études et de la scolarité')->first();
        return $config ? $config->getAttribute('value') : 'Titre non configuré';
    }

    public static function getAppDe()
    {
        $config = Configuration::where('key', 'Nom complet du Chargé des études et de la scolarité')->first();
        return $config ? $config->getAttribute('value') : 'Nom non configuré';
    }
}