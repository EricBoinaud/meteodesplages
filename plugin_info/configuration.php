<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<form class="form-horizontal">
  <fieldset>
    <legend><i class="fas fa-info-circle"></i> {{Informations}}</legend>
    <div class="form-group">
      <label class="col-sm-4 control-label">{{Source des données}}</label>
      <div class="col-sm-6">
        <span class="form-control" style="border:0;box-shadow:none;background:transparent;padding-left:0;">Open-Meteo</span>
        <span class="help-block">{{Aucune clé API n’est nécessaire pour la météo et les données marines Open-Meteo.}}</span>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-4 control-label">{{Version du paquet local}}</label>
      <div class="col-sm-6">
        <span class="form-control" style="border:0;box-shadow:none;background:transparent;padding-left:0;"><strong>4.0.0-beta1</strong</strong></span>
        <span class="help-block">{{Jeedom laisse parfois le champ Version de l’encadré État vide pour un plugin installé manuellement sans dépôt Market ou GitHub associé.}}</span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">{{Choix de la plage}}</label>
      <div class="col-sm-6">
        <a class="btn btn-default" href="index.php?v=d&m=meteodesplages&p=meteodesplages"><i class="fas fa-umbrella-beach"></i> {{Ouvrir mes plages}}</a>
        <span class="help-block">{{Le choix de la plage se fait dans l’onglet Équipement de chaque équipement Météo des plages, et non dans cette page de configuration générale.}}</span>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">{{Marées}}</label>
      <div class="col-sm-6">
        <span class="help-block" style="margin-top:7px;">{{Les quatre cartes de marée sont calculées à partir du niveau marin modélisé par Open-Meteo. Elles restent indicatives ; le SHOM demeure la référence officielle.}}</span>
      </div>
    </div>
  </fieldset>
</form>
