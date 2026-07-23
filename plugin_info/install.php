<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

function meteodesplages_install() {
}

function meteodesplages_update() {
    foreach (eqLogic::byType('meteodesplages') as $eqLogic) {
        if ($eqLogic->getConfiguration('tide_source', '') === '') {
            $eqLogic->setConfiguration('tide_source', 'openmeteo');
            $eqLogic->save(true);
        }
    }
}

function meteodesplages_remove() {
}
