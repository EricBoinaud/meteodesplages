<?php

function meteodesplages_install()
{
}

function meteodesplages_update()
{
    foreach (eqLogic::byType('meteodesplages') as $eqLogic) {
        if ($eqLogic->getConfiguration('tide_source', '') === '') {
            $eqLogic->setConfiguration('tide_source', 'openmeteo');
            $eqLogic->save(true);
        }
    }
}

function meteodesplages_remove()
{
}
