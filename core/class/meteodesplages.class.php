<?php

require_once __DIR__ . '/Utils/WeatherFormatter.php';
require_once __DIR__ . '/Repository/BeachRepository.php';
require_once __DIR__ . '/Provider/OpenMeteoProvider.php';
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

class meteodesplages extends eqLogic {

    const PLUGIN_VERSION = '3.1.0';

    private static function beachPresets(): array
    {
        return BeachRepository::all();
    }

    public function preSave() {
        $preset = $this->getConfiguration('plage', 'pontaillac');
        $presets = self::beachPresets();
        if ($preset !== 'personnalisee' && isset($presets[$preset])) {
            $this->setConfiguration('latitude', $presets[$preset]['latitude']);
            $this->setConfiguration('longitude', $presets[$preset]['longitude']);
            if ($presets[$preset]['image'] !== '') {
                $this->setConfiguration('image', $presets[$preset]['image']);
            } elseif (trim($this->getConfiguration('image', '')) === 'plugins/meteodesplages/data/images/pontaillac.webp') {
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
            $this->setConfiguration('image', 'plugins/meteodesplages/data/images/pontaillac.webp');
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

    private function getJson($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Jeedom-MeteoDesPlages/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);
        $body = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false || $error !== '') {
            throw new Exception('Erreur réseau : ' . $error);
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception('Réponse HTTP ' . $httpCode);
        }
        $json = json_decode($body, true);
        if (!is_array($json)) {
            throw new Exception('Réponse JSON invalide');
        }
        if (isset($json['error']) && $json['error']) {
            throw new Exception(isset($json['reason']) ? $json['reason'] : 'Erreur renvoyée par Open-Meteo');
        }
        return $json;
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

    private function weatherText($code): string
    {
        return WeatherFormatter::weatherText($code);
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

    public function refresh() {
        $latitude = trim($this->getConfiguration('latitude', '45.6267'));
        $longitude = trim($this->getConfiguration('longitude', '-1.0518'));
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            throw new Exception('Latitude ou longitude invalide');
        }

        $provider = new OpenMeteoProvider();

        $weather = $provider->getWeather($latitude, $longitude);
        $marine = $provider->getMarine($latitude, $longitude);
        $tideMarine = $this->getTideJson($latitude, $longitude);

        $current = isset($weather['current']) ? $weather['current'] : [];
        $daily = isset($weather['daily']) ? $weather['daily'] : [];
        $sea = isset($marine['current']) ? $marine['current'] : [];

        $code = isset($current['weather_code']) ? $current['weather_code'] : null;
        $this->updateValue('temperature_air', $current['temperature_2m'] ?? null);
        $this->updateValue('temperature_ressentie', $current['apparent_temperature'] ?? null);
        $this->updateValue('humidite', $current['relative_humidity_2m'] ?? null);
        $this->updateValue('code_meteo', $code);
        if ($code !== null) {
            $this->updateValue('condition', $this->weatherText($code));
        }
        $this->updateValue('vent_vitesse', $current['wind_speed_10m'] ?? null);
        $this->updateValue('vent_rafales', $current['wind_gusts_10m'] ?? null);
        $this->updateValue('vent_direction', $current['wind_direction_10m'] ?? null);
        $this->updateValue('precipitations', $current['precipitation'] ?? null);
        $this->updateValue('uv_max', $daily['uv_index_max'][0] ?? null);
        $this->updateValue('temperature_max', $daily['temperature_2m_max'][0] ?? null);
        $this->updateValue('temperature_min', $daily['temperature_2m_min'][0] ?? null);
        $this->updateValue('risque_pluie', $daily['precipitation_probability_max'][0] ?? null);

        $this->updateValue('temperature_mer', $sea['sea_surface_temperature'] ?? null);
        $this->updateValue('vague_hauteur', $sea['wave_height'] ?? null);
        $this->updateValue('vague_periode', $sea['wave_period'] ?? null);
        $this->updateValue('vague_direction', $sea['wave_direction'] ?? null);
        $this->updateValue('houle_hauteur', $sea['swell_wave_height'] ?? null);
        $this->updateValue('houle_periode', $sea['swell_wave_period'] ?? null);
        $this->updateValue('houle_direction', $sea['swell_wave_direction'] ?? null);

        $tides = is_array($tideMarine) ? $this->extractTides($tideMarine) : [];
        for ($i = 1; $i <= 4; $i++) {
            $event = $tides[$i - 1] ?? null;
            if ($event) {
                $this->updateValue('maree_'.$i.'_type', $event['type']);
                $this->updateValue('maree_'.$i.'_jour', $event['day']);
                $this->updateValue('maree_'.$i.'_heure', $event['time']);
                $this->updateValue('maree_'.$i.'_hauteur', $event['height']);
                $this->updateValue('maree_'.$i.'_coefficient', $event['coefficient'] === null ? '—' : $event['coefficient'], true);
            } else {
                $this->updateValue('maree_'.$i.'_type', 'Indisponible', true);
                $this->updateValue('maree_'.$i.'_jour', '—', true);
                $this->updateValue('maree_'.$i.'_heure', '—', true);
                $this->updateValue('maree_'.$i.'_hauteur', '—', true);
                $this->updateValue('maree_'.$i.'_coefficient', '—', true);
            }
        }
        $this->updateValue('derniere_mise_a_jour', date('d/m/Y H:i:s'));

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

        $id = (int) $this->getId();
        $name = htmlspecialchars($this->getName(), ENT_QUOTES, 'UTF-8');
        $v = function ($logicalId, $default = '—') {
            return htmlspecialchars((string) $this->cmdValue($logicalId, $default), ENT_QUOTES, 'UTF-8');
        };
        $cmdId = function ($logicalId) {
            $cmd = $this->getCmd(null, $logicalId);
            return is_object($cmd) ? (int) $cmd->getId() : 0;
        };

        $code = $this->cmdValue('code_meteo', 1);
        $icon = $this->weatherIcon($code);
        $refreshId = $cmdId('refresh');
        $image = trim($this->getConfiguration('image', 'plugins/meteodesplages/data/images/pontaillac.webp'));
        $imageCss = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');

        $fields = [
            'temperature_air','temperature_ressentie','humidite','condition','vent_vitesse','vent_rafales','vent_direction',
            'precipitations','uv_max','temperature_max','temperature_min','risque_pluie','temperature_mer','vague_hauteur',
            'vague_periode','vague_direction','houle_hauteur','houle_periode','houle_direction',
            'maree_1_type','maree_1_jour','maree_1_heure','maree_1_hauteur','maree_1_coefficient',
            'maree_2_type','maree_2_jour','maree_2_heure','maree_2_hauteur','maree_2_coefficient',
            'maree_3_type','maree_3_jour','maree_3_heure','maree_3_hauteur','maree_3_coefficient',
            'maree_4_type','maree_4_jour','maree_4_heure','maree_4_hauteur','maree_4_coefficient','derniere_mise_a_jour'
        ];

        $html = '<div class="eqLogic-widget eqLogic meteodesplages-widget" data-eqType="meteodesplages" data-eqLogic_id="'.$id.'" data-eqLogic_uid="'.$replace['#uid#'].'" data-version="'.$_version.'">';
        $html .= '<style>
        .meteodesplages-widget{width:100%;min-width:300px;max-width:920px;padding:0!important;background:transparent!important;border:none!important;box-shadow:none!important;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
        .mdp-card{overflow:hidden;border-radius:18px;background:linear-gradient(145deg,#eefaff 0%,#ffffff 45%,#f0fbf8 100%);color:#183445;box-shadow:0 8px 28px rgba(10,65,90,.18);border:1px solid rgba(40,130,160,.16)}
        .mdp-hero{position:relative;padding:18px 20px 16px;color:white;background-image:linear-gradient(90deg,rgba(5,42,61,.58),rgba(5,75,95,.16)),url("'.$imageCss.'");background-size:cover;background-position:center;overflow:hidden;min-height:245px}
        .mdp-hero:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,0),rgba(0,0,0,.12));pointer-events:none}
        .mdp-top{position:relative;z-index:2;display:flex;justify-content:space-between;gap:12px;align-items:flex-start}
        .mdp-title{font-size:25px;font-weight:750;line-height:1.05;text-transform:uppercase;letter-spacing:.4px}.mdp-sub{font-size:13px;opacity:.88;margin-top:6px}
        .mdp-refresh{border:1px solid rgba(255,255,255,.55);background:rgba(0,0,0,.14);color:#fff;border-radius:12px;padding:7px 11px;cursor:pointer;font-size:13px}.mdp-refresh:hover{background:rgba(0,0,0,.25)}
        .mdp-main{position:relative;z-index:2;display:flex;align-items:center;gap:14px;margin-top:16px}.mdp-weather-icon{font-size:54px;filter:drop-shadow(0 3px 3px rgba(0,0,0,.18))}.mdp-temp{font-size:52px;font-weight:750;line-height:.95}.mdp-temp small{font-size:21px;font-weight:500}.mdp-condition{font-size:15px;margin-top:5px}.mdp-updated{font-size:11px;opacity:.82;margin-top:5px}
        .mdp-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;padding:12px}.mdp-section{background:rgba(255,255,255,.84);border:1px solid rgba(20,105,135,.12);border-radius:14px;padding:12px;box-shadow:0 3px 10px rgba(20,80,105,.07)}
        .mdp-section-title{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.7px;color:#167a9d;margin-bottom:10px}.mdp-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.mdp-stat{min-width:0}.mdp-stat.wide{grid-column:1/-1}.mdp-label{font-size:10px;color:#69818e;margin-bottom:2px}.mdp-value{font-size:18px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.mdp-value small{font-size:11px;font-weight:500;color:#5d7785}.mdp-symbol{font-size:17px;margin-right:4px}.mdp-tides{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;padding:0 12px 12px}.mdp-tide{background:rgba(255,255,255,.88);border:1px solid rgba(20,105,135,.14);border-radius:14px;padding:10px;text-align:center}.mdp-tide-type{font-size:11px;color:#167a9d;font-weight:800;text-transform:uppercase}.mdp-tide-day{font-size:11px;color:#607d8b;font-weight:650;margin-top:3px}.mdp-tide-time{font-size:22px;font-weight:750;margin:3px 0}.mdp-tide-meta{font-size:11px;color:#607d8b}.mdp-footer{padding:0 14px 12px;text-align:center;color:#78909b;font-size:10px}
        @media(max-width:720px){.mdp-tides{grid-template-columns:1fr 1fr}.mdp-grid{grid-template-columns:1fr 1fr}.mdp-section:last-child{grid-column:1/-1}.mdp-title{font-size:21px}.mdp-temp{font-size:44px}}
        @media(max-width:460px){.mdp-tides{grid-template-columns:1fr 1fr}.mdp-grid{grid-template-columns:1fr}.mdp-section:last-child{grid-column:auto}.mdp-hero{padding:16px}.mdp-weather-icon{font-size:44px}.mdp-temp{font-size:39px}.mdp-refresh{padding:6px 8px}.mdp-title{font-size:19px}}
        </style>';
        $html .= '<div class="mdp-card"><div class="mdp-hero"><div class="mdp-top"><div><div class="mdp-title">'.$name.'</div><div class="mdp-sub">Météo de la plage</div></div><button class="mdp-refresh" onclick="jeedom.cmd.execute({id:'.$refreshId.',success:function(){setTimeout(function(){window.location.reload();},900);}})"><i class="fas fa-sync-alt"></i> Rafraîchir</button></div>';
        $html .= '<div class="mdp-main"><div class="mdp-weather-icon">'.$icon.'</div><div><div class="mdp-temp"><span id="mdp-'.$id.'-temperature_air">'.$v('temperature_air').'</span><small> °C</small></div><div class="mdp-condition" id="mdp-'.$id.'-condition">'.$v('condition').'</div><div class="mdp-updated">Mise à jour : <span id="mdp-'.$id.'-derniere_mise_a_jour">'.$v('derniere_mise_a_jour').'</span></div></div></div></div>';

        $section = function($title, $items) use ($id, $v) {
            $out = '<div class="mdp-section"><div class="mdp-section-title">'.$title.'</div><div class="mdp-stats">';
            foreach ($items as $item) {
                [$logicalId,$label,$symbol,$unit,$wide] = $item;
                $out .= '<div class="mdp-stat'.($wide?' wide':'').'"><div class="mdp-label">'.$label.'</div><div class="mdp-value"><span class="mdp-symbol">'.$symbol.'</span><span id="mdp-'.$id.'-'.$logicalId.'">'.$v($logicalId).'</span>'.($unit!==''?'<small> '.$unit.'</small>':'').'</div></div>';
            }
            return $out.'</div></div>';
        };
        $html .= '<div class="mdp-grid">';
        $html .= $section('Atmosphère', [
            ['temperature_ressentie','Ressentie','🌡️','°C',false],['humidite','Humidité','💧','%',false],
            ['temperature_min','Minimum','↘️','°C',false],['temperature_max','Maximum','↗️','°C',false],
            ['precipitations','Précipitations','☔','mm',false],['uv_max','Indice UV','☀️','',false]
        ]);
        $html .= $section('Vent', [
            ['vent_vitesse','Vitesse moyenne','💨','km/h',false],['vent_rafales','Rafales','🌬️','km/h',false],
            ['vent_direction','Direction','🧭','°',true]
        ]);
        $html .= $section('Mer & houle', [
            ['temperature_mer','Température de la mer','🌊','°C',true],['vague_hauteur','Vagues','〰️','m',false],
            ['vague_periode','Période vagues','⏱️','s',false],['houle_hauteur','Houle','🌊','m',false],
            ['houle_periode','Période houle','⏱️','s',false],['risque_pluie','Risque de pluie','🌧️','%',true]
        ]);
        $html .= '</div>';
        $html .= '<div class="mdp-tides">';
        for ($i = 1; $i <= 4; $i++) {
            $type = $v('maree_'.$i.'_type');
            $symbol = ($type === 'Haute') ? '🌊' : '🏖️';
            $html .= '<div class="mdp-tide"><div class="mdp-tide-type">'.$symbol.' <span id="mdp-'.$id.'-maree_'.$i.'_type">'.$type.'</span></div><div class="mdp-tide-day" id="mdp-'.$id.'-maree_'.$i.'_jour">'.$v('maree_'.$i.'_jour').'</div><div class="mdp-tide-time" id="mdp-'.$id.'-maree_'.$i.'_heure">'.$v('maree_'.$i.'_heure').'</div><div class="mdp-tide-meta"><span id="mdp-'.$id.'-maree_'.$i.'_hauteur">'.$v('maree_'.$i.'_hauteur').'</span> m · coef. estimé <span id="mdp-'.$id.'-maree_'.$i.'_coefficient">'.$v('maree_'.$i.'_coefficient').'</span></div></div>';
        }
        $html .= '</div><div class="mdp-footer">Données Open-Meteo — marées modélisées : horaires, hauteurs et coefficients estimés. Référence officielle pour la navigation : SHOM.</div></div>';

        $html .= '<script>(function(){';
        foreach ($fields as $logicalId) {
            $cid = $cmdId($logicalId);
            if ($cid > 0) {
                $html .= 'jeedom.cmd.addUpdateFunction('.$cid.',function(_options){var e=document.getElementById("mdp-'.$id.'-'.$logicalId.'");if(e){e.textContent=_options.display_value;}});';
            }
        }
        $html .= '})();</script></div>';
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
