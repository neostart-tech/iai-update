<?php

if (!function_exists('getAnneeScolaireId')) {
    function getAnneeScolaireId()
    {
        return App\Models\AnneeScolaire::where('active', true)->value('id');
    }
}

if (!function_exists('formatMontant')) {
    function formatMontant($montant)
    {
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }
}