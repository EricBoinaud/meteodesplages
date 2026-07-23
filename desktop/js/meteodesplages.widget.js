(function () {
  'use strict';

  function weatherIcon(code) {
    code = parseInt(code, 10);
    if (code === 0) return '☀️';
    if (code === 1 || code === 2) return '🌤️';
    if (code === 3) return '☁️';
    if (code === 45 || code === 48) return '🌫️';
    if ([51,53,55,56,57,61,63,65,66,67,80,81,82].indexOf(code) !== -1) return '🌧️';
    if ([71,73,75,77,85,86].indexOf(code) !== -1) return '❄️';
    if ([95,96,99].indexOf(code) !== -1) return '⛈️';
    return '🌤️';
  }

  function notifyError(message) {
    if (typeof toastr !== 'undefined') toastr.error(message);
  }

  function bindRefresh(widget) {
    var button = widget.querySelector('.mdp-refresh');
    if (!button || button.dataset.mdpBound === '1') return;
    button.dataset.mdpBound = '1';

    button.addEventListener('click', function (event) {
      event.stopPropagation();
      var commandId = parseInt(button.dataset.cmdId || '0', 10);
      var icon = button.querySelector('i');
      if (!commandId) {
        notifyError('Commande Rafraîchir introuvable');
        return;
      }
      button.disabled = true;
      if (icon) icon.classList.add('fa-spin');
      jeedom.cmd.execute({
        id: commandId,
        success: function () {
          window.setTimeout(function () {
            if (icon) icon.classList.remove('fa-spin');
            button.disabled = false;
          }, 600);
        },
        error: function () {
          if (icon) icon.classList.remove('fa-spin');
          button.disabled = false;
          notifyError('Échec du rafraîchissement');
        }
      });
    });
  }

  function bindLiveValues(widget) {
    widget.querySelectorAll('[data-cmd-id][data-field]').forEach(function (element) {
      var commandId = parseInt(element.dataset.cmdId || '0', 10);
      var field = element.dataset.field;
      if (!commandId || element.dataset.mdpUpdateBound === '1') return;
      element.dataset.mdpUpdateBound = '1';

      jeedom.cmd.addUpdateFunction(commandId, function (_options) {
        var displayValue = _options.display_value;
        if (field === 'code_meteo') {
          var icon = widget.querySelector('.mdp-weather-icon');
          if (icon) icon.textContent = weatherIcon(displayValue);
          return;
        }

        element.textContent = displayValue;
        var tideMatch = field.match(/^maree_([1-4])_type$/);
        if (tideMatch) {
          var symbol = widget.querySelector('[data-tide-symbol="' + tideMatch[1] + '"]');
          if (symbol) symbol.textContent = displayValue === 'Haute' ? '🌊' : '🏖️';
        }
      });
    });
  }

  function initWidget(widget) {
    if (!widget || widget.dataset.mdpInitialized === '1') return;
    widget.dataset.mdpInitialized = '1';
    bindRefresh(widget);
    bindLiveValues(widget);
  }

  function initAll(root) {
    var scope = root || document;
    if (scope.matches && scope.matches('.meteodesplages-widget')) initWidget(scope);
    scope.querySelectorAll('.meteodesplages-widget').forEach(initWidget);
  }

  window.meteodesplagesWidget = { initAll: initAll };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { initAll(document); });
  } else {
    initAll(document);
  }

  if (typeof MutationObserver !== 'undefined') {
    new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) initAll(node);
        });
      });
    }).observe(document.documentElement, { childList: true, subtree: true });
  }
})();
