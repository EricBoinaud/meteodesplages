<?php

/**
 * Client pour les API météo et marine d'Open-Meteo.
 */
final class OpenMeteoProvider
{
    private const WEATHER_API_URL = 'https://api.open-meteo.com/v1/forecast';
    private const MARINE_API_URL = 'https://marine-api.open-meteo.com/v1/marine';

    private $timezone;

    public function __construct($timezone = 'Europe/Paris')
    {
        $this->timezone = $timezone;
    }

    /**
     * Récupère les conditions météorologiques actuelles
     * et les prévisions journalières.
     */
    public function getWeather($latitude, $longitude): array
    {
        $url = self::WEATHER_API_URL . '?' . http_build_query([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'current' => implode(',', [
                'temperature_2m',
                'apparent_temperature',
                'relative_humidity_2m',
                'weather_code',
                'wind_speed_10m',
                'wind_direction_10m',
                'wind_gusts_10m',
                'precipitation',
            ]),
            'daily' => implode(',', [
                'uv_index_max',
                'temperature_2m_max',
                'temperature_2m_min',
                'precipitation_probability_max',
            ]),
            'timezone' => $this->timezone,
            'forecast_days' => 1,
        ]);

        return $this->requestJson($url);
    }

    /**
     * Récupère les conditions marines actuelles.
     */
    public function getMarine($latitude, $longitude): array
    {
        $url = self::MARINE_API_URL . '?' . http_build_query([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'current' => implode(',', [
                'wave_height',
                'wave_direction',
                'wave_period',
                'swell_wave_height',
                'swell_wave_direction',
                'swell_wave_period',
                'sea_surface_temperature',
            ]),
            'timezone' => $this->timezone,
            'forecast_days' => 3,
            'cell_selection' => 'sea',
        ]);

        return $this->requestJson($url);
    }

    /**
     * Exécute une requête HTTP et retourne le JSON décodé.
     */
    private function requestJson($url): array
    {
        $ch = curl_init();

        if ($ch === false) {
            throw new Exception('Impossible d’initialiser la requête HTTP');
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Jeedom-MeteoDesPlages/4.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($body === false || $error !== '') {
            throw new Exception('Erreur réseau Open-Meteo : ' . $error);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception('Réponse HTTP Open-Meteo ' . $httpCode);
        }

        $json = json_decode($body, true);

        if (!is_array($json)) {
            throw new Exception('Réponse JSON Open-Meteo invalide');
        }

        if (!empty($json['error'])) {
            $reason = isset($json['reason'])
                ? $json['reason']
                : 'Erreur renvoyée par Open-Meteo';

            throw new Exception($reason);
        }

        return $json;
    }
}
