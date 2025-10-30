<?php
namespace App\Helpers;

use App\Models\Configuration;

class ConfigHelper
{
    public static function getAppName()
    {
        $AppName = Configuration::where('key', 'Nom de l\'établissement')->first()->getAttribute('value');
        return $AppName;
    }

    public static function getAppLogo()
    {
        $AppLogo = Configuration::where('key', 'Logo de l\'établissement')->first()->getAttribute('value');
        return $AppLogo;
    }

    public static function getAppTitreDe()
    {
        $AppTitreDe = Configuration::where('key', 'Titre du Chargé des études et de la scolarité')->first()->getAttribute('value');
        return $AppTitreDe;
    }

    public static function getAppDe()
    {
        $AppDe = Configuration::where('key', 'Nom complet du Chargé des études et de la scolarité')->first()->getAttribute('value');
        return $AppDe;
    }
}