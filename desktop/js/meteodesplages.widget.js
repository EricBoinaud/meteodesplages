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
    else console.error(message);
  }

  function storageKey(widget) {
    return 'meteodesplages.beach.' + (widget.dataset.eqlogic_id || widget.dataset.eqLogic_id || 'default');
  }

  function getSelectedBeach(widget) {
    var select = widget.querySelector('.mdp-beach-select');
    return select ? select.value : '';
  }

  function setLoading(widget, loading) {
    widget.classList.toggle('mdp-loading', loading);
    var button = widget.querySelector('.mdp-refresh');
    var select = widget.querySelector('.mdp-beach-select');
    var icon = button ? button.querySelector('i') : null;
    if (button) button.disabled = loading;
    if (select) select.disabled = loading;
    if (icon) icon.classList.toggle('fa-spin', loading);
  }

  function displayValue(value) {
    return value === null || typeof value === 'undefined' || value === '' ? '—' : value;
  }

  function applyData(widget, data) {
    Object.keys(data || {}).forEach(function (field) {
      widget.querySelectorAll('[data-field="' + field + '"]').forEach(function (element) {
        element.textContent = displayValue(data[field]);
      });
    });

    var icon = widget.querySelector('.mdp-weather-icon');
    if (icon) icon.textContent = weatherIcon(data.code_meteo);

    for (var i = 1; i <= 4; i++) {
      var symbol = widget.querySelector('[data-tide-symbol="' + i + '"]');
      if (symbol) symbol.textContent = data['maree_' + i + '_type'] === 'Haute' ? '🌊' : '🏖️';
    }

    if (data.image) {
      var hero = widget.querySelector('.mdp-hero');
      if (hero) hero.style.setProperty('--mdp-image', 'url("' + String(data.image).replace(/"/g, '\\"') + '")');
    }
  }

  function ajaxErrorMessage(error) {
    if (!error) return 'Échec du chargement de la plage';
    return error.message || error.result || error.statusText || 'Échec du chargement de la plage';
  }

  function loadBeach(widget, beachKey) {
    var eqLogicId = parseInt(widget.getAttribute('data-eqLogic_id') || widget.dataset.eqlogic_id || '0', 10);
    if (!eqLogicId || !beachKey) return;

    setLoading(widget, true);
    domUtils.ajax({
      type: 'POST',
      url: 'plugins/meteodesplages/core/ajax/meteodesplages.ajax.php',
      dataType: 'json',
      global: false,
      data: {
        action: 'getBeachData',
        eqLogic_id: eqLogicId,
        beach: beachKey
      },
      success: function (response) {
        var data = response && typeof response.result !== 'undefined' ? response.result : response;
        applyData(widget, data || {});
        try { localStorage.setItem(storageKey(widget), beachKey); } catch (e) {}
        setLoading(widget, false);
      },
      error: function (error) {
        setLoading(widget, false);
        notifyError(ajaxErrorMessage(error));
      }
    });
  }

  function bindSelector(widget) {
    var select = widget.querySelector('.mdp-beach-select');
    if (!select || select.dataset.mdpBound === '1') return;
    select.dataset.mdpBound = '1';

    var configured = select.dataset.configuredBeach || select.value;
    var remembered = '';
    try { remembered = localStorage.getItem(storageKey(widget)) || ''; } catch (e) {}
    if (remembered && select.querySelector('option[value="' + remembered + '"]')) {
      select.value = remembered;
    }

    select.addEventListener('click', function (event) { event.stopPropagation(); });
    select.addEventListener('change', function (event) {
      event.stopPropagation();
      loadBeach(widget, select.value);
    });

    if (select.value && select.value !== configured) loadBeach(widget, select.value);
  }

  function bindRefresh(widget) {
    var button = widget.querySelector('.mdp-refresh');
    if (!button || button.dataset.mdpBound === '1') return;
    button.dataset.mdpBound = '1';
    button.addEventListener('click', function (event) {
      event.stopPropagation();
      loadBeach(widget, getSelectedBeach(widget));
    });
  }

  function bindLiveValues(widget) {
    widget.querySelectorAll('[data-cmd-id][data-field]').forEach(function (element) {
      var commandId = parseInt(element.dataset.cmdId || '0', 10);
      var field = element.dataset.field;
      if (!commandId || element.dataset.mdpUpdateBound === '1') return;
      element.dataset.mdpUpdateBound = '1';

      jeedom.cmd.addUpdateFunction(commandId, function (_options) {
        var select = widget.querySelector('.mdp-beach-select');
        if (select && select.value !== select.dataset.configuredBeach) return;
        var display = _options.display_value;
        if (field === 'code_meteo') {
          var icon = widget.querySelector('.mdp-weather-icon');
          if (icon) icon.textContent = weatherIcon(display);
        } else {
          element.textContent = displayValue(display);
        }
        var match = field.match(/^maree_([1-4])_type$/);
        if (match) {
          var symbol = widget.querySelector('[data-tide-symbol="' + match[1] + '"]');
          if (symbol) symbol.textContent = display === 'Haute' ? '🌊' : '🏖️';
        }
      });
    });
  }

  function initWidget(widget) {
    if (!widget || widget.dataset.mdpInitialized === '1') return;
    widget.dataset.mdpInitialized = '1';
    bindSelector(widget);
    bindRefresh(widget);
    bindLiveValues(widget);
  }

  function initAll(root) {
    var scope = root || document;
    if (scope.matches && scope.matches('.meteodesplages-widget')) initWidget(scope);
    scope.querySelectorAll('.meteodesplages-widget').forEach(initWidget);
  }

  window.meteodesplagesWidget = { initAll: initAll, loadBeach: loadBeach };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { initAll(document); });
  else initAll(document);

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
