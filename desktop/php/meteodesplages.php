<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('meteodesplages');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>
<style>
.mdp-toolbar{display:flex;gap:6px;flex-wrap:wrap;justify-content:flex-end;margin:4px 0 8px 8px;position:relative;z-index:5}
.mdp-toolbar .btn{margin:0!important}
@media(max-width:760px){.mdp-toolbar{float:none!important;justify-content:flex-start;margin-left:0}.meteodesplages .nav-tabs{clear:both}}
</style>
<div class="row row-overflow">
  <div class="col-xs-12 eqLogicThumbnailDisplay">
    <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction logoPrimary" data-action="add">
        <i class="fas fa-plus-circle"></i><br><span>{{Ajouter}}</span>
      </div>
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i><br><span>{{Configuration}}</span>
      </div>
    </div>
    <legend><i class="fas fa-table"></i> {{Mes plages}}</legend>
    <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
    <div class="eqLogicThumbnailContainer">
      <?php foreach ($eqLogics as $eqLogic) {
        $opacity = $eqLogic->getIsEnable() ? '' : 'disableCard'; ?>
        <div class="eqLogicDisplayCard cursor <?= $opacity ?>" data-eqLogic_id="<?= $eqLogic->getId() ?>">
          <img src="plugins/meteodesplages/plugin_info/meteodesplages_icon.png" />
          <br><span class="name"><?= $eqLogic->getHumanName(true, true) ?></span>
        </div>
      <?php } ?>
    </div>
  </div>

  <div class="col-xs-12 eqLogic meteodesplages" style="display:none;">
    <div class="mdp-toolbar pull-right">
      <a class="btn btn-sm btn-default eqLogicAction" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
      <a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
      <a class="btn btn-sm btn-danger eqLogicAction" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
    </div>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-target="#eqlogictab"><i class="fas fa-tachometer-alt"></i> {{Équipement}}</a></li>
      <li role="presentation"><a aria-controls="profile" role="tab" data-toggle="tab" data-target="#commandtab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
    </ul>
    <div class="tab-content">
      <div role="tabpanel" class="tab-pane" id="eqlogictab">
        <br/>
        <form class="form-horizontal">
          <fieldset>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
              <div class="col-sm-3"><input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display:none;"/><input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="Pontaillac"/></div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Objet parent}}</label>
              <div class="col-sm-3"><select class="eqLogicAttr form-control" data-l1key="object_id"><?= jeeObject::getUISelectList() ?></select></div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Options}}</label>
              <div class="col-sm-3"><label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label><label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label></div>
            </div>
            <hr>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Plage}}</label>
              <div class="col-sm-3">
                <select id="mdpBeachPreset" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="plage">
                  <option value="pontaillac" data-lat="45.6267" data-lon="-1.0518" data-image="/plugins/meteodesplages/data/images/pontaillac.webp">Pontaillac</option>
                  <option value="grande_conche" data-lat="45.6184" data-lon="-1.0208" data-image="">Grande Conche</option>
                  <option value="foncillon" data-lat="45.6229" data-lon="-1.0364" data-image="">Foncillon</option>
                  <option value="le_chay" data-lat="45.6265" data-lon="-1.0427" data-image="">Le Chay</option>
                  <option value="pigeonnier" data-lat="45.6295" data-lon="-1.0481" data-image="">Le Pigeonnier</option>
                  <option value="nauzan" data-lat="45.6388" data-lon="-1.0725" data-image="">Nauzan</option>
                  <option value="saint_georges" data-lat="45.6038" data-lon="-1.0009" data-image="">Saint-Georges-de-Didonne</option>
                  <option value="personnalisee">Coordonnées personnalisées</option>
                </select>
                <span class="help-block">{{La latitude, la longitude et l’image sont remplies automatiquement. Cliquez ensuite sur Sauvegarder.}}</span>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Latitude}}</label>
              <div class="col-sm-3"><input type="number" step="0.000001" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="latitude" placeholder="45.6267"/></div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Longitude}}</label>
              <div class="col-sm-3"><input type="number" step="0.000001" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="longitude" placeholder="-1.0518"/></div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Image du widget}}</label>
              <div class="col-sm-5">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="image" placeholder="/plugins/meteodesplages/data/images/pontaillac.webp"/>
                <span class="help-block">{{Pour Pontaillac, l'image est incluse. Pour une autre plage, indiquez l'URL d'une image ou laissez vide pour utiliser le fond marin.}}</span>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Actualisation}}</label>
              <div class="col-sm-3"><span class="form-control" style="border:0;box-shadow:none;">{{Toutes les 30 minutes, ou avec la commande Rafraîchir}}</span></div>
            </div>
          </fieldset>
        </form>
      </div>
      <div role="tabpanel" class="tab-pane" id="commandtab">
        <br/>
        <table id="table_cmd" class="table table-bordered table-condensed">
          <thead><tr><th>{{Nom}}</th><th>{{Type}}</th><th>{{Options}}</th><th>{{État}}</th><th>{{Action}}</th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script>
function mdpApplyBeachPreset(select) {
  if (!select) return;
  var option = select.options[select.selectedIndex];
  var custom = select.value === 'personnalisee';
  var lat = document.querySelector('.eqLogicAttr[data-l1key="configuration"][data-l2key="latitude"]');
  var lon = document.querySelector('.eqLogicAttr[data-l1key="configuration"][data-l2key="longitude"]');
  var image = document.querySelector('.eqLogicAttr[data-l1key="configuration"][data-l2key="image"]');
  if (!custom && option) {
    if (lat) lat.value = option.getAttribute('data-lat') || '';
    if (lon) lon.value = option.getAttribute('data-lon') || '';
    if (image) image.value = option.getAttribute('data-image') || '';
  }
  if (lat) lat.readOnly = !custom;
  if (lon) lon.readOnly = !custom;
  if (typeof toastr !== 'undefined') toastr.success(custom ? '{{Coordonnées personnalisées activées}}' : '{{Plage configurée. Cliquez sur Sauvegarder.}}');
}
$(document).off('change.mdpBeach', '#mdpBeachPreset').on('change.mdpBeach', '#mdpBeachPreset', function(){
  mdpApplyBeachPreset(this);
  var custom = this.value === 'personnalisee';
  var lat = document.querySelector('.eqLogicAttr[data-l1key="configuration"][data-l2key="latitude"]');
  var lon = document.querySelector('.eqLogicAttr[data-l1key="configuration"][data-l2key="longitude"]');
  if (lat) lat.readOnly = !custom;
  if (lon) lon.readOnly = !custom;
});
</script>
<?php include_file('desktop', 'meteodesplages', 'js', 'meteodesplages'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
