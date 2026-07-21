<?php
function meteodesplages_install() {
}

function meteodesplages_update() {
    foreach (eqLogic::byType('meteodesplages') as $eqLogic) {
        $eqLogic->setConfiguration('tide_source', 'openmeteo');
        $eqLogic->save(true);
    }
}

function meteodesplages_remove() {
}
