<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');

if (!isConnect()) {
    throw new Exception(__('401 - Accès non autorisé', __FILE__));
}

ajax::init();

try {
    $action = init('action');

    if ($action === 'getBeachData') {
        $eqLogicId = (int) init('eqLogic_id');
        $beachKey = trim((string) init('beach'));

        $eqLogic = eqLogic::byId($eqLogicId);
        if (!is_object($eqLogic) || $eqLogic->getEqType_name() !== 'meteodesplages') {
            throw new Exception(__('Équipement Météo des plages introuvable', __FILE__));
        }
        if ($beachKey === '') {
            throw new Exception(__('Aucune plage sélectionnée', __FILE__));
        }

        ajax::success($eqLogic->getBeachData($beachKey));
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . secureXSS($action));
} catch (Throwable $e) {
    ajax::error(displayException($e), $e->getCode());
}
