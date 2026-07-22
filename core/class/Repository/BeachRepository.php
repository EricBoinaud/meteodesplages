<?php

/**
 * Gestion des plages prédéfinies du plugin.
 */
final class BeachRepository
{
    /**
     * Retourne la liste des plages prédéfinies.
     */
    public static function all(): array
    {
        return [
            'pontaillac' => [
                'latitude' => '45.6267',
                'longitude' => '-1.0518',
                'image' => 'plugins/meteodesplages/data/images/pontaillac.webp',
            ],
            'grande_conche' => [
                'latitude' => '45.6184',
                'longitude' => '-1.0208',
                'image' => '',
            ],
            'foncillon' => [
                'latitude' => '45.6229',
                'longitude' => '-1.0364',
                'image' => '',
            ],
            'le_chay' => [
                'latitude' => '45.6265',
                'longitude' => '-1.0427',
                'image' => '',
            ],
            'pigeonnier' => [
                'latitude' => '45.6295',
                'longitude' => '-1.0481',
                'image' => '',
            ],
            'nauzan' => [
                'latitude' => '45.6388',
                'longitude' => '-1.0725',
                'image' => '',
            ],
            'saint_georges' => [
                'latitude' => '45.6038',
                'longitude' => '-1.0009',
                'image' => '',
            ],
        ];
    }

    /**
     * Retourne une plage ou null.
     */
    public static function get(string $id): ?array
    {
        $beaches = self::all();

        return $beaches[$id] ?? null;
    }
}
