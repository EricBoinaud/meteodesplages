<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

class meteodesplages extends eqLogic {

    const PLUGIN_VERSION = '1.1.0';

    private const BEACHES = [
        'pontaillac' => [
            'name' => 'Pontaillac',
            'latitude' => '45.6267',
            'longitude' => '-1.0518',
            'image' => '/plugins/meteodesplages/data/images/pontaillac.webp'
        ],
        'grande_conche' => [
            'name' => 'Grande Conche',
            'latitude' => '45.6184',
            'longitude' => '-1.0208',
            'image' => '/plugins/meteodesplages/data/images/grande_conche.webp'
        ],
        'foncillon' => [
            'name' => 'Foncillon',
            'latitude' => '45.6229',
            'longitude' => '-1.0364',
            'image' => '/plugins/meteodesplages/data/images/foncillon.webp'
        ],
        'le_chay' => [
            'name' => 'Le Chay',
            'latitude' => '45.6265',
            'longitude' => '-1.0427',
            'image' => '/plugins/meteodesplages/data/images/le_chay.webp'
        ],
        'pigeonnier' => [
            'name' => 'Le Pigeonnier',
            'latitude' => '45.6295',
            'longitude' => '-1.0481',
            'image' => '/plugins/meteodesplages/data/images/pigeonnier.webp'
        ],
        'nauzan' => [
            'name' => 'Nauzan',
            'latitude' => '45.6388',
            'longitude' => '-1.0725',
            'image' => '/plugins/meteodesplages/data/images/nauzan.webp'
        ],
        'saint_georges' => [
            'name' => 'Saint-Georges-de-Didonne',
            'latitude' => '45.6038',
            'longitude' => '-1.0009',
            'image' => '/plugins/meteodesplages/data/images/saint_georges.webp'
        ]
    ];

    public static function getBeachList() {
        $beaches = [];
        foreach (self::BEACHES as $key => $beach) {
            $beaches[$key] = $beach['name'];
        }
        return $beaches;
    }

    public static function getBeachPreset($key) {
        return self::BEACHES[$key] ?? null;
    }

    public function preSave() {
        $preset = $this->getConfiguration('plage', 'pontaillac');
        $presets = self::BEACHES;
        if ($preset !== 'personnalisee' && isset($presets[$preset])) {
            $this->setConfiguration('latitude', $presets[$preset]['latitude']);
            $this->setConfiguration('longitude', $presets[$preset]['longitude']);
            if ($presets[$preset]['image'] !== '') {
                $this->setConfiguration('image', $presets[$preset]['image']);
            } elseif (trim($this->getConfiguration('image', '')) === '/plugins/meteodesplages/data/images/pontaillac.webp') {
                $this->setConfiguration('image', '');
            }
        }
    }

    public static function cron30() {
        foreach (self::byType('meteodesplages', true) as $eqLogic) {
            try {
                $eqLogic->refresh();
            } catch (Throwable $e) {
                log::add('meteodesplages', 'error', $eqLogic->getHumanName() . ' : ' . $e->getMessage());
            }
        }
    }

    public function postInsert() {
        if ($this->getConfiguration('latitude', '') === '') {
            $this->setConfiguration('latitude', '45.6267');
        }
        if ($this->getConfiguration('longitude', '') === '') {
            $this->setConfiguration('longitude', '-1.0518');
        }
        if ($this->getConfiguration('plage', '') === '') {
            $this->setConfiguration('plage', 'pontaillac');
        }
        if ($this->getConfiguration('image', '') === '') {
            $this->setConfiguration('image', '/plugins/meteodesplages/data/images/pontaillac.webp');
        }
        $this->setConfiguration('tide_source', 'openmeteo');
    }

    public function postSave() {
        $this->createCommands();
    }

    private function createInfoCommand($logicalId, $name, $subType = 'numeric', $unit = '', $order = 0, $visible = 1) {
        $cmd = $this->getCmd(null, $logicalId);
        if (!is_object($cmd)) {
            $cmd = new meteodesplagesCmd();
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId($logicalId);
        }
        $cmd->setName($name);
        $cmd->setType('info');
        $cmd->setSubType($subType);
        $cmd->setUnite($unit);
        $cmd->setOrder($order);
        $cmd->setIsVisible($visible);
        if ($subType === 'numeric') {
            $cmd->setConfiguration('historizeRound', 1);
        }
        $cmd->save();
    }

    private function createCommands() {
        $commands = [
            ['temperature_air', 'Température extérieure', 'numeric', '°C'],
            ['temperature_ressentie', 'Température ressentie', 'numeric', '°C'],
            ['humidite', 'Humidité', 'numeric', '%'],
            ['condition', 'Conditions', 'string', ''],
            ['code_meteo', 'Code météo', 'numeric', ''],
            ['vent_vitesse', 'Vent', 'numeric', 'km/h'],
            ['vent_rafales', 'Rafales', 'numeric', 'km/h'],
            ['vent_direction', 'Direction du vent', 'numeric', '°'],
            ['precipitations', 'Précipitations', 'numeric', 'mm'],
            ['uv_max', 'Indice UV maximal', 'numeric', ''],
            ['temperature_max', 'Température maximale', 'numeric', '°C'],
            ['temperature_min', 'Température minimale', 'numeric', '°C'],
            ['risque_pluie', 'Risque de pluie', 'numeric', '%'],
            ['temperature_mer', 'Température de la mer', 'numeric', '°C'],
            ['vague_hauteur', 'Hauteur des vagues', 'numeric', 'm'],
            ['vague_periode', 'Période des vagues', 'numeric', 's'],
            ['vague_direction', 'Direction des vagues', 'numeric', '°'],
            ['houle_hauteur', 'Hauteur de la houle', 'numeric', 'm'],
            ['houle_periode', 'Période de la houle', 'numeric', 's'],
            ['houle_direction', 'Direction de la houle', 'numeric', '°'],
            ['maree_1_type', 'Marée 1 type', 'string', ''],
            ['maree_1_jour', 'Marée 1 jour', 'string', ''],
            ['maree_1_heure', 'Marée 1 heure', 'string', ''],
            ['maree_1_hauteur', 'Marée 1 hauteur', 'numeric', 'm'],
            ['maree_1_coefficient', 'Marée 1 coefficient estimé', 'numeric', ''],
            ['maree_2_type', 'Marée 2 type', 'string', ''],
            ['maree_2_jour', 'Marée 2 jour', 'string', ''],
            ['maree_2_heure', 'Marée 2 heure', 'string', ''],
            ['maree_2_hauteur', 'Marée 2 hauteur', 'numeric', 'm'],
            ['maree_2_coefficient', 'Marée 2 coefficient estimé', 'numeric', ''],
            ['maree_3_type', 'Marée 3 type', 'string', ''],
            ['maree_3_jour', 'Marée 3 jour', 'string', ''],
            ['maree_3_heure', 'Marée 3 heure', 'string', ''],
            ['maree_3_hauteur', 'Marée 3 hauteur', 'numeric', 'm'],
            ['maree_3_coefficient', 'Marée 3 coefficient estimé', 'numeric', ''],
            ['maree_4_type', 'Marée 4 type', 'string', ''],
            ['maree_4_jour', 'Marée 4 jour', 'string', ''],
            ['maree_4_heure', 'Marée 4 heure', 'string', ''],
            ['maree_4_hauteur', 'Marée 4 hauteur', 'numeric', 'm'],
            ['maree_4_coefficient', 'Marée 4 coefficient estimé', 'numeric', ''],
            ['derniere_mise_a_jour', 'Dernière mise à jour', 'string', '']
        ];
        $order = 1;
        foreach ($commands as $definition) {
            $this->createInfoCommand($definition[0], $definition[1], $definition[2], $definition[3], $order++);
        }

        $refresh = $this->getCmd(null, 'refresh');
        if (!is_object($refresh)) {
            $refresh = new meteodesplagesCmd();
            $refresh->setEqLogic_id($this->getId());
            $refresh->setLogicalId('refresh');
        }
        $refresh->setName('Rafraîchir');
        $refresh->setType('action');
        $refresh->setSubType('other');
        $refresh->setOrder(100);
        $refresh->setIsVisible(1);
        $refresh->save();
    }

    private function getJson($url, $maxAttempts = 3) {
        $lastError = 'Erreur inconnue';

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 25,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Jeedom-MeteoDesPlages/' . self::PLUGIN_VERSION,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2
            ]);

            $body = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($body !== false && $curlError === '' && $httpCode >= 200 && $httpCode < 300) {
                $json = json_decode($body, true);
                if (!is_array($json)) {
                    throw new Exception('Réponse JSON invalide');
                }
                if (!empty($json['error'])) {
                    throw new Exception(isset($json['reason']) ? $json['reason'] : 'Erreur renvoyée par Open-Meteo');
                }
                return $json;
            }

            if ($curlError !== '') {
                $lastError = 'Erreur réseau : ' . $curlError;
            } else {
                $lastError = 'Réponse HTTP ' . $httpCode;
            }

            $retryable = ($curlError !== '') || $httpCode === 429 || $httpCode >= 500;
            if (!$retryable || $attempt >= $maxAttempts) {
                break;
            }

            usleep(300000 * $attempt);
        }

        throw new Exception($lastError . ' après ' . $maxAttempts . ' tentative(s)');
    }

    private function updateValue($logicalId, $value, $allowEmpty = false) {
        if (!$allowEmpty && ($value === null || $value === '')) {
            return;
        }
        $cmd = $this->getCmd(null, $logicalId);
        if (is_object($cmd)) {
            $cmd->event($value);
        }
    }

    private function weatherText($code) {
        $labels = [
            0 => 'Ciel dégagé', 1 => 'Plutôt dégagé', 2 => 'Partiellement nuageux', 3 => 'Couvert',
            45 => 'Brouillard', 48 => 'Brouillard givrant', 51 => 'Bruine faible', 53 => 'Bruine',
            55 => 'Bruine forte', 56 => 'Bruine verglaçante faible', 57 => 'Bruine verglaçante forte',
            61 => 'Pluie faible', 63 => 'Pluie', 65 => 'Pluie forte', 66 => 'Pluie verglaçante faible',
            67 => 'Pluie verglaçante forte', 71 => 'Neige faible', 73 => 'Neige', 75 => 'Neige forte',
            77 => 'Grains de neige', 80 => 'Averses faibles', 81 => 'Averses', 82 => 'Averses fortes',
            85 => 'Averses de neige faibles', 86 => 'Averses de neige fortes', 95 => 'Orage',
            96 => 'Orage avec grêle faible', 99 => 'Orage avec forte grêle'
        ];
        return isset($labels[(int)$code]) ? $labels[(int)$code] : 'Code météo ' . (int)$code;
    }

    private function extractTides($marine) {
        $series = null;
        foreach (['minutely_15', 'hourly'] as $key) {
            if (isset($marine[$key]['time'], $marine[$key]['sea_level_height_msl'])
                && is_array($marine[$key]['time'])
                && is_array($marine[$key]['sea_level_height_msl'])) {
                $series = $marine[$key];
                break;
            }
        }
        if ($series === null) {
            log::add('meteodesplages', 'warning', $this->getHumanName() . ' : aucune série de marée renvoyée par Open-Meteo');
            return [];
        }

        $points = [];
        $count = min(count($series['time']), count($series['sea_level_height_msl']));
        for ($i = 0; $i < $count; $i++) {
            $level = $series['sea_level_height_msl'][$i];
            if (!is_numeric($level)) continue;
            $ts = strtotime($series['time'][$i]);
            if ($ts === false) continue;
            $points[] = ['ts' => $ts, 'level' => (float)$level];
        }
        if (count($points) < 12) {
            log::add('meteodesplages', 'warning', $this->getHumanName() . ' : série de marée insuffisante (' . count($points) . ' points)');
            return [];
        }

        // Lissage sur trois points, puis détection des changements de pente.
        $smooth = [];
        $n = count($points);
        for ($i = 0; $i < $n; $i++) {
            $from = max(0, $i - 1);
            $to = min($n - 1, $i + 1);
            $sum = 0.0;
            for ($j = $from; $j <= $to; $j++) $sum += $points[$j]['level'];
            $smooth[$i] = $sum / ($to - $from + 1);
        }

        $extrema = [];
        for ($i = 1; $i < $n - 1; $i++) {
            $prev = $smooth[$i - 1];
            $cur = $smooth[$i];
            $next = $smooth[$i + 1];
            $type = null;
            if ($cur >= $prev && $cur > $next) $type = 'Haute';
            if ($cur <= $prev && $cur < $next) $type = 'Basse';
            if ($type === null) continue;

            $event = ['type' => $type, 'ts' => $points[$i]['ts'], 'height' => round($points[$i]['level'], 2)];
            $lastIndex = count($extrema) - 1;
            if ($lastIndex >= 0 && $extrema[$lastIndex]['type'] === $type && ($event['ts'] - $extrema[$lastIndex]['ts']) < 5 * 3600) {
                $replace = ($type === 'Haute' && $event['height'] > $extrema[$lastIndex]['height'])
                    || ($type === 'Basse' && $event['height'] < $extrema[$lastIndex]['height']);
                if ($replace) $extrema[$lastIndex] = $event;
                continue;
            }
            $extrema[] = $event;
        }

        $now = time() - 3600;
        $future = [];
        foreach ($extrema as $idx => $event) {
            if ($event['ts'] < $now) continue;
            $ranges = [];
            if ($idx > 0 && $extrema[$idx - 1]['type'] !== $event['type']) {
                $ranges[] = abs($event['height'] - $extrema[$idx - 1]['height']);
            }
            if ($idx + 1 < count($extrema) && $extrema[$idx + 1]['type'] !== $event['type']) {
                $ranges[] = abs($event['height'] - $extrema[$idx + 1]['height']);
            }
            $range = count($ranges) ? array_sum($ranges) / count($ranges) : null;
            $coef = null;
            if ($range !== null) {
                // Approximation d'affichage uniquement : le coefficient officiel reste celui du SHOM.
                $coef = (int) round(20 + (($range - 1.0) / 4.5) * 100);
                $coef = max(20, min(120, $coef));
            }
            $future[] = [
                'type' => $event['type'],
                'time' => date('H:i', $event['ts']),
                'date' => date('d/m', $event['ts']),
                'day' => $this->tideDayLabel($event['ts']),
                'height' => $event['height'],
                'coefficient' => $coef
            ];
            if (count($future) >= 4) break;
        }
        log::add('meteodesplages', 'debug', $this->getHumanName() . ' : ' . count($points) . ' points de niveau marin, ' . count($future) . ' marées retenues');
        return $future;
    }

    private function tideDayLabel($timestamp) {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $date = date('Y-m-d', $timestamp);
        if ($date === $today) return "Aujourd'hui";
        if ($date === $tomorrow) return 'Demain';
        $days = ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'];
        return $days[(int)date('w', $timestamp)] . ' ' . date('d/m', $timestamp);
    }

    private function getTideJson($latitude, $longitude) {
        $base = [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timezone' => 'Europe/Paris',
            'forecast_days' => 4,
            'past_days' => 1,
            'cell_selection' => 'sea'
        ];

        // La série 15 minutes est prioritaire. En cas d'indisponibilité, repli sur l'horaire.
        try {
            $params = $base;
            $params['minutely_15'] = 'sea_level_height_msl';
            $params['forecast_minutely_15'] = 384;
            $params['past_minutely_15'] = 96;
            return $this->getJson('https://marine-api.open-meteo.com/v1/marine?' . http_build_query($params));
        } catch (Throwable $e) {
            log::add('meteodesplages', 'warning', $this->getHumanName() . ' : marées 15 min indisponibles, repli horaire : ' . $e->getMessage());
        }

        $params = $base;
        $params['hourly'] = 'sea_level_height_msl';
        return $this->getJson('https://marine-api.open-meteo.com/v1/marine?' . http_build_query($params));
    }

    private function fetchBeachData($latitude, $longitude) {
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            throw new Exception('Latitude ou longitude invalide');
        }

        $weatherUrl = 'https://api.open-meteo.com/v1/forecast?' . http_build_query([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'current' => 'temperature_2m,apparent_temperature,relative_humidity_2m,weather_code,wind_speed_10m,wind_direction_10m,wind_gusts_10m,precipitation',
            'daily' => 'uv_index_max,temperature_2m_max,temperature_2m_min,precipitation_probability_max',
            'timezone' => 'Europe/Paris',
            'forecast_days' => 1
        ]);

        $marineUrl = 'https://marine-api.open-meteo.com/v1/marine?' . http_build_query([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'current' => 'wave_height,wave_direction,wave_period,swell_wave_height,swell_wave_direction,swell_wave_period,sea_surface_temperature',
            'timezone' => 'Europe/Paris',
            'forecast_days' => 3,
            'cell_selection' => 'sea'
        ]);

        $weather = $this->getJson($weatherUrl);
        $marine = $this->getJson($marineUrl);
        $tideMarine = $this->getTideJson($latitude, $longitude);

        $current = $weather['current'] ?? [];
        $daily = $weather['daily'] ?? [];
        $sea = $marine['current'] ?? [];
        $code = $current['weather_code'] ?? null;

        $data = [
            'temperature_air' => $current['temperature_2m'] ?? null,
            'temperature_ressentie' => $current['apparent_temperature'] ?? null,
            'humidite' => $current['relative_humidity_2m'] ?? null,
            'code_meteo' => $code,
            'condition' => $code === null ? '—' : $this->weatherText($code),
            'vent_vitesse' => $current['wind_speed_10m'] ?? null,
            'vent_rafales' => $current['wind_gusts_10m'] ?? null,
            'vent_direction' => $current['wind_direction_10m'] ?? null,
            'precipitations' => $current['precipitation'] ?? null,
            'uv_max' => $daily['uv_index_max'][0] ?? null,
            'temperature_max' => $daily['temperature_2m_max'][0] ?? null,
            'temperature_min' => $daily['temperature_2m_min'][0] ?? null,
            'risque_pluie' => $daily['precipitation_probability_max'][0] ?? null,
            'temperature_mer' => $sea['sea_surface_temperature'] ?? null,
            'vague_hauteur' => $sea['wave_height'] ?? null,
            'vague_periode' => $sea['wave_period'] ?? null,
            'vague_direction' => $sea['wave_direction'] ?? null,
            'houle_hauteur' => $sea['swell_wave_height'] ?? null,
            'houle_periode' => $sea['swell_wave_period'] ?? null,
            'houle_direction' => $sea['swell_wave_direction'] ?? null,
            'derniere_mise_a_jour' => date('d/m/Y H:i:s')
        ];

        $tides = is_array($tideMarine) ? $this->extractTides($tideMarine) : [];
        for ($i = 1; $i <= 4; $i++) {
            $event = $tides[$i - 1] ?? null;
            $data['maree_' . $i . '_type'] = $event['type'] ?? 'Indisponible';
            $data['maree_' . $i . '_jour'] = $event['day'] ?? '—';
            $data['maree_' . $i . '_heure'] = $event['time'] ?? '—';
            $data['maree_' . $i . '_hauteur'] = $event['height'] ?? '—';
            $data['maree_' . $i . '_coefficient'] = isset($event['coefficient']) && $event['coefficient'] !== null ? $event['coefficient'] : '—';
        }

        return $data;
    }

    public function getBeachData($beachKey) {
        $preset = self::getBeachPreset($beachKey);
        if (!is_array($preset)) {
            throw new Exception('Plage inconnue');
        }

        $data = $this->fetchBeachData($preset['latitude'], $preset['longitude']);
        $data['beach_key'] = $beachKey;
        $data['beach_name'] = $preset['name'];
        $data['image'] = $preset['image'];
        return $data;
    }

    private function applyBeachData(array $data) {
        $allowEmpty = [
            'maree_1_type', 'maree_1_jour', 'maree_1_heure', 'maree_1_hauteur', 'maree_1_coefficient',
            'maree_2_type', 'maree_2_jour', 'maree_2_heure', 'maree_2_hauteur', 'maree_2_coefficient',
            'maree_3_type', 'maree_3_jour', 'maree_3_heure', 'maree_3_hauteur', 'maree_3_coefficient',
            'maree_4_type', 'maree_4_jour', 'maree_4_heure', 'maree_4_hauteur', 'maree_4_coefficient'
        ];
        foreach ($data as $logicalId => $value) {
            if (in_array($logicalId, ['beach_key', 'beach_name', 'image'], true)) {
                continue;
            }
            $this->updateValue($logicalId, $value, in_array($logicalId, $allowEmpty, true));
        }
    }

    public function refresh() {
        $latitude = trim($this->getConfiguration('latitude', '45.6267'));
        $longitude = trim($this->getConfiguration('longitude', '-1.0518'));
        $data = $this->fetchBeachData($latitude, $longitude);
        $this->applyBeachData($data);

        $this->setStatus('lastCommunication', date('Y-m-d H:i:s'));
        $this->setStatus('timeout', 0);
        return true;
    }

    private function cmdValue($logicalId, $default = '—') {
        $cmd = $this->getCmd(null, $logicalId);
        if (!is_object($cmd)) {
            return $default;
        }
        $value = $cmd->execCmd();
        return ($value === '' || $value === null) ? $default : $value;
    }

    private function weatherIcon($code) {
        $code = (int) $code;
        if ($code === 0) return '☀️';
        if (in_array($code, [1,2], true)) return '🌤️';
        if ($code === 3) return '☁️';
        if (in_array($code, [45,48], true)) return '🌫️';
        if (in_array($code, [51,53,55,56,57,61,63,65,66,67,80,81,82], true)) return '🌧️';
        if (in_array($code, [71,73,75,77,85,86], true)) return '❄️';
        if (in_array($code, [95,96,99], true)) return '⛈️';
        return '🌤️';
    }

    public function toHtml($_version = 'dashboard') {
        $replace = $this->preToHtml($_version);
        if (!is_array($replace)) {
            return $replace;
        }

        $version = jeedom::versionAlias($_version);
        if (!in_array($version, ['dashboard', 'mobile'], true)) {
            $version = 'dashboard';
        }

        $id = (int) $this->getId();
        $escape = static function ($value) {
            return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        };
        $value = function ($logicalId, $default = '—') use ($escape) {
            return $escape($this->cmdValue($logicalId, $default));
        };
        $commandId = function ($logicalId) {
            $cmd = $this->getCmd(null, $logicalId);
            return is_object($cmd) ? (int) $cmd->getId() : 0;
        };

        $image = trim($this->getConfiguration('image', '/plugins/meteodesplages/data/images/pontaillac.webp'));
        $code = $this->cmdValue('code_meteo', 1);
        $configuredBeach = $this->getConfiguration('plage', 'pontaillac');
        $beachOptions = '';
        foreach (self::getBeachList() as $key => $name) {
            $selected = ($key === $configuredBeach) ? ' selected' : '';
            $beachOptions .= '<option value="' . $escape($key) . '"' . $selected . '>' . $escape($name) . '</option>';
        }

        $replace['#id#'] = $id;
        $replace['#name#'] = $escape($this->getName());
        $replace['#image#'] = $escape($image);
        $replace['#weather_icon#'] = $this->weatherIcon($code);
        $replace['#refresh_cmd_id#'] = $commandId('refresh');
        $replace['#configured_beach#'] = $escape($configuredBeach);
        $replace['#beach_options#'] = $beachOptions;

        $fields = [
            'code_meteo', 'temperature_air', 'temperature_ressentie', 'humidite', 'condition',
            'vent_vitesse', 'vent_rafales', 'vent_direction', 'precipitations', 'uv_max',
            'temperature_max', 'temperature_min', 'risque_pluie', 'temperature_mer',
            'vague_hauteur', 'vague_periode', 'houle_hauteur', 'houle_periode',
            'maree_1_type', 'maree_1_jour', 'maree_1_heure', 'maree_1_hauteur', 'maree_1_coefficient',
            'maree_2_type', 'maree_2_jour', 'maree_2_heure', 'maree_2_hauteur', 'maree_2_coefficient',
            'maree_3_type', 'maree_3_jour', 'maree_3_heure', 'maree_3_hauteur', 'maree_3_coefficient',
            'maree_4_type', 'maree_4_jour', 'maree_4_heure', 'maree_4_hauteur', 'maree_4_coefficient',
            'derniere_mise_a_jour'
        ];

        foreach ($fields as $logicalId) {
            $replace['#' . $logicalId . '#'] = $value($logicalId);
            $replace['#cmd_' . $logicalId . '#'] = $commandId($logicalId);
        }

        for ($i = 1; $i <= 4; $i++) {
            $type = (string) $this->cmdValue('maree_' . $i . '_type', '—');
            $replace['#maree_' . $i . '_symbol#'] = ($type === 'Haute') ? '🌊' : '🏖️';
        }

        $template = getTemplate('core', $version, 'meteodesplages', 'meteodesplages');
        $html = template_replace($replace, $template);
        return $this->postToHtml($_version, $html);
    }

}

class meteodesplagesCmd extends cmd {
    public function execute($_options = []) {
        $eqLogic = $this->getEqLogic();
        if (!is_object($eqLogic)) {
            throw new Exception('Équipement introuvable');
        }
        if ($this->getLogicalId() === 'refresh') {
            return $eqLogic->refresh();
        }
        return false;
    }
}
