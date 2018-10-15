/**
 * @file
 * Implements directive ngChangeWifiDmz.
 */

myApp.directive('ngChangeWifiDmz',[ '$http', ngChangeWifiDmz]);

function ngChangeWifiDmz($http) {

  return {
    restrict: 'EA',
    controller: changeWifiDmzController,
    link: linkFunc
  };

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_change_wifi_dmz];

    retrieveInformation(scope, config, el);

    // Disable button while http request ends.
    scope.$watch(function () {
      return $http.pendingRequests.length > 0;
    },
    function (hasPending) {
      if (hasPending) {
        // Disable until has no pending request.
        scope.state_button = 1;
      }
    });

    scope.$watch('wifi_dmz', function (wifi_dmz) {
      var message_success = Drupal.t('Muy bien, lo has logrado');
      var message_error = Drupal.t('Dirección IP invalida, ejemplo de formato aceptado 255.255.255.255');

      var reg_ex = /[^0-9.]+/g;

      if (wifi_dmz == '' || wifi_dmz === undefined) {
        scope.status_dmz = '';
        scope.state_button = 1;
        jQuery('#wifi_dmz').removeClass('error valid');
        jQuery('#status_dmz').removeClass('error good');
      }
      else {
        // To prevent user entering special characters.
        scope.wifi_dmz = wifi_dmz.replace(new RegExp(reg_ex), '');

        if (wifi_dmz.length < 7) {
          scope.status_dmz = message_error;
          jQuery('#wifi_dmz').removeClass('valid').addClass('error');
          jQuery('#status_dmz').addClass('error').removeClass('good');
          scope.state_button = 1;
        }

        if (wifi_dmz.length >= 7) {

          if ((/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$|^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$|^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/.test(wifi_dmz))) {
            scope.status_dmz = message_success;
            jQuery('#wifi_dmz').removeClass('error').addClass('valid');
            jQuery('#status_dmz').removeClass('error').addClass('good');
            scope.state_button = 0;
          }
          else {
            scope.status_dmz = message_error;
            jQuery('#wifi_dmz').removeClass('valid').addClass('error');
            jQuery('#status_dmz').addClass('error').removeClass('good');
            scope.state_button = 1;
          }
        }
      }
    });
  }

  function retrieveInformation(scope, config, el) {
    $http({
      method: 'GET',
      url: config.url
    })
    .then(function (resp) {
      scope.wifi_dmz_resp = resp.data;
      if (resp.data.status != 'Activo') {
        scope.state = 1;
        scope.classState = 'disabled';
        jQuery('#cancelar-dmz').addClass('disabled');
      }
      else {
        scope.state = 0;
        scope.classState = '';
        scope.service = resp.data.productId;
      }
    },
    function (response) {
      scope.state = 1;
      scope.classState = 'disabled';
      jQuery('#cancelar-dmz').addClass('disabled');
    })
  }
}

changeWifiDmzController.$inject = ['$scope', '$http'];

function changeWifiDmzController($scope, $http) {

  // Change WiFi net name.
  $scope.changeWifiDmz = function (new_dmz) {
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_change_wifi_dmz];
    var message_error = Drupal.t('Ha ocurrido un error.<br>En este momento no podemos procesar su solicitud de configuración de la DMZ de la red WiFi, por favor intente más tarde.');

    var parameters = {
      ipdmz: new_dmz,
      contractId: $scope.wifi_dmz_resp.contractId,
      productId: $scope.wifi_dmz_resp.productId,
      subscriptionNumber: $scope.wifi_dmz_resp.subscriptionNumber
    };

    // Set new dmz.
    $http.get('/rest/session/token')
      .then(function (res) {
        $http({
          method: 'POST',
          url: config.url,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': res.data
          },
          data: parameters
        })
        .then(
          function successCallback(response) {
            if (response.data.error) {
              $scope.show_mesagge_data = response.data.message_error;
              $scope.clearFields();
              $scope.alertas_servicios();
            }
            else {
              location.reload(true);
            }
          },
          function errorCallback(response){
            // Error case.
            $scope.show_mesagge_data = message_error;
            $scope.clearFields();
            $scope.alertas_servicios();
          });
      });
  };

  // Clear fields.
  $scope.clearFieldsDmz = function () {
    // Reset input value.
    $scope['wifi_dmz'] = '';
    jQuery('#wifi_dmz').val('');

    // Reset validation text's.
    $scope.val_status = '';
    jQuery('#wifi_dmz').removeClass('error valid');

    // Disabled button.
    if (jQuery('#cambiar-dmz').attr('disabled') === undefined) {
      jQuery('#cambiar-dmz').attr('disabled', 'disabled');
    }
  };

  // Show message service.
   $scope.alertas_servicios = function () {
    var message = '' +
      '<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' +
      ' <button type="button" class="close prefix icon-x-cyan" data-dismiss="alert" aria-hidden="true">' +
      '   <span class="path1"></span><span class="path2"></span>' +
      ' </button>' +
      ' <div class="text-alert">' +
      '   <div class="icon-alert">' +
      '     <span class="icon-1"></span>' +
      '     <span class="icon-2"></span>' +
      '   </div>' +
      '   <div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>' +
      ' </div>' +
      '</div>';

    jQuery('.main-top').empty();
    jQuery('.main-top').append(message);
    jQuery("html, body").animate({ scrollTop: 0 }, 750);

    jQuery('.messages .close').on('click', function () {
      jQuery('.main-top').empty();
    });
  }
}
