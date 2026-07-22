<?php

/**
 * Outils de présentation des données météorologiques.
 */
final class WeatherFormatter
{
    /**
     * Transforme un code météo WMO en libellé français.
     *
     * @param mixed $code Code météo renvoyé par Open-Meteo.
     */
    public static function weatherText($code): string
    {
        $labels = [
            0 => 'Ciel dégagé',
            1 => 'Plutôt dégagé',
            2 => 'Partiellement nuageux',
            3 => 'Couvert',
            45 => 'Brouillard',
            48 => 'Brouillard givrant',
            51 => 'Bruine faible',
            53 => 'Bruine',
            55 => 'Bruine forte',
            56 => 'Bruine verglaçante faible',
            57 => 'Bruine verglaçante forte',
            61 => 'Pluie faible',
            63 => 'Pluie',
            65 => 'Pluie forte',
            66 => 'Pluie verglaçante faible',
            67 => 'Pluie verglaçante forte',
            71 => 'Neige faible',
            73 => 'Neige',
            75 => 'Neige forte',
            77 => 'Grains de neige',
            80 => 'Averses faibles',
            81 => 'Averses',
            82 => 'Averses fortes',
            85 => 'Averses de neige faibles',
            86 => 'Averses de neige fortes',
            95 => 'Orage',
            96 => 'Orage avec grêle faible',
            99 => 'Orage avec forte grêle',
        ];

        $normalizedCode = (int) $code;

        return $labels[$normalizedCode] ?? 'Code météo ' . $normalizedCode;
    }
}
