function addCmdToTable(_cmd) {
  if (!isset(_cmd)) _cmd = {};
  if (!isset(_cmd.configuration)) _cmd.configuration = {};
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="id" style="display:none;"/><input class="cmdAttr form-control input-sm" data-l1key="name"/></td>';
  tr += '<td><span class="type"></span><span class="subType"></span></td>';
  tr += '<td><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible"/> {{Afficher}}</label><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized"/> {{Historiser}}</label><input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="{{Unité}}" style="width:90px;display:inline-block;"/></td>';
  tr += '<td><span class="cmdAttr" data-l1key="htmlstate"></span></td>';
  tr += '<td><i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i><i class="fas fa-cogs pull-right cmdAction cursor" data-action="configure"></i></td>';
  tr += '</tr>';
  $('#table_cmd tbody').append(tr);
  $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
  if (isset(_cmd.type)) jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}
