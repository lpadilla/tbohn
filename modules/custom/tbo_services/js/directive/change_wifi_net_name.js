/**
 * @file
 * Implements directive ngChangeWifiNetName.
 */

myApp.directive('ngChangeWifiNetName',[ '$http', ngChangeWifiNetName]);

function ngChangeWifiNetName($http) {

  return {
    restrict: 'EA',
    controller: changeWifiNetNameController,
    link: linkFunc
  };

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_change_wifi_net_name];

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

    scope.$watchGroup(['netname','netname_confirm'], function (group) {
      var netname = group[0];
      var netname_confirm = group[1];

      var reg_ex = /[^a-zA-Z0-9]+/g;

      scope.state_button = 0;

      if (netname == undefined && netname_confirm == undefined) {
        scope.state_button = 1;
        return;
      }

      // Validate the first field.
      if (netname == '' || netname === undefined) {
        scope.state_button = 1;
        scope.status_netname = '';
        jQuery('#netname').removeClass('error valid');
        jQuery('#status_netname').removeClass('error good');
      }
      else {
        // To prevent user entering special characters.
        netname = netname.replace(new RegExp(reg_ex), '');
        netname = (netname.length > 10) ? netname.substring(0, 10) : netname;
        scope.netname = netname;

        if (netname.length < 6) {
          scope.state_button = 1;
          scope.status_netname = Drupal.t('Mínimo 6 caracteres');
          jQuery('#netname').removeClass('valid').addClass('error');
          jQuery('#status_netname').addClass('error').removeClass('good');
        }
        else {
          if ((/[a-z]+/.test(netname) || /[A-Z]+/.test(netname)) || /[0-9]+/.test(netname)) {
            scope.status_netname = Drupal.t('Muy bien, lo has logrado');
            jQuery('#netname').removeClass('error').addClass('valid');
            jQuery('#status_netname').removeClass('error').addClass('good');
          }
        }
      }

      // Validate the second field.
      if (netname_confirm == '' || netname_confirm == undefined) {
        scope.state_button = 1;
        scope.status_netname_confirm = '';
        jQuery('#netname_confirm').removeClass('error valid');
        jQuery('#status_netname_confirm').removeClass('error good');
      }
      else {
        // To prevent user entering special characters.
        netname_confirm = netname_confirm.replace(new RegExp(reg_ex), '');
        netname_confirm = (netname_confirm.length > 10) ? netname_confirm.substring(0, 10) : netname_confirm;
        scope.netname_confirm = netname_confirm;

        if (netname != netname_confirm) {
          scope.state_button = 1;
          scope.status_netname_confirm = Drupal.t('La confirmacion del nombre no coincide');
          jQuery('#netname_confirm').removeClass('valid').addClass('error');
          jQuery('#status_netname_confirm').addClass('error').removeClass('good');
        }
        else {
          scope.status_netname_confirm = Drupal.t('La confirmacion del nombre coincide');
          jQuery('#netname_confirm').removeClass('error').addClass('valid');
          jQuery('#status_netname_confirm').removeClass('error').addClass('good');
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
      scope.wifi_netname_resp = resp.data;
      if (resp.data.status != 'Activo') {
        scope.state = 1;
        scope.classState = 'disabled';
        jQuery('#cancelar-netname').addClass('disabled');
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
      jQuery('#cancelar-netname').addClass('disabled');
    })
  }
}

changeWifiNetNameController.$inject = ['$scope', '$http'];

function changeWifiNetNameController($scope, $http) {

  // Change WiFi net name.
  $scope.changeNetName = function (netname) {
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_change_wifi_net_name];
    var message_error = Drupal.t('Ha ocurrido un error.<br>En este momento no podemos procesar su solicitud de cambio de nombre de su red WiFi, por favor intente más tarde.');

    var parameters = {
      SSID: netname,
      contractId: $scope.wifi_netname_resp.contractId,
      productId: $scope.wifi_netname_resp.productId,
      subscriptionNumber: $scope.wifi_netname_resp.subscriptionNumber
    };

    // Set netName.
    $http.get('/rest/session/token').then(function (res) {
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
      .then(function successCallback(response) {
        if (response.data.error) {
          $scope.show_mesagge_data = message_error;
          $scope.clearFieldsNetName();
          $scope.alertaNetname();
        }
        else {
          location.reload(true);
        }
      },
      function errorCallback(response) {
        // Error case.
        $scope.show_mesagge_data = message_error;
        $scope.clearFieldsNetName();
        $scope.alertaNetname();
      });
    });
  };

  // Clear fields.
  $scope.clearFieldsNetName = function () {
    // Reset input value.
    $scope['netname'] = '';
    $scope['netname_confirm'] = '';
    jQuery('#netname').val('');
    jQuery('#netname_confirm').val('');

    // Reset validation text's.
    $scope.status_netname = '';
    $scope.status_netname_confirm = '';
    jQuery('#netname').removeClass('error valid');
    jQuery('#netname_confirm').removeClass('error valid');
  };

  // Show message service.
  $scope.alertaNetname = function () {
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
