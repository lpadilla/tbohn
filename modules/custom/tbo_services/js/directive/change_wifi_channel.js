/**
 * @file
 * Change Wifi Channel Angular logic.
 */

myApp.directive('ngChangeWifiChannel', ['$http', ngChangeWifiChannel]);

function ngChangeWifiChannel($http) {

  return {
    restrict: 'EA',
    controller: changeWifiChannelController,
    link: linkFunc
  };

  function linkFunc(scope, el, attr, ctrl) {

    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_change_wifi_channel];
    scope.state_button = 1;

    retrieveInformation(scope, config, el);
  }

  function retrieveInformation(scope, config, el) {
    $http({
      method: 'GET',
      url: config.url
    })
      .then(function (resp) {
          scope.wifi_resp = resp.data;

          // If the service is suspended we disable all form controls.
          if (resp.data.status != 'Activo') {
            scope.state = 1;
            scope.classState = 'disabled';
            jQuery('#btn-cambiar-canal').addClass('disabled');
            jQuery('#btn-cancelar-canal').addClass('disabled');
            jQuery('#wifi_channel').attr('disabled');
          }
          else {
            scope.state = 0;
            scope.classState = '';
            scope.service = resp.data.productId;
            jQuery('#btn-cambiar-canal').removeClass('disabled');
            jQuery('#btn-cancelar-canal').removeClass('disabled');
            jQuery('#wifi_channel').removeAttr('disabled');
          }
        },
        function (response) {
          scope.state = 1;
          scope.classState = 'disabled';
          jQuery('#btn-cambiar-canal').addClass('disabled');
          jQuery('#btn-cancelar-canal').addClass('disabled');
          jQuery('#wifi_channel').attr('disabled');
        })
  }
}

changeWifiChannelController.$inject = ['$scope', '$http', '$q'];

function changeWifiChannelController($scope, $http, $q) {
  // Private properties.
  var httpRequestCanceller;

  // Public properties.
  $scope.processing = undefined;
  $scope.response = undefined;

  // Private methods.
  function cancelChangeChannelRequest() {
    if (httpRequestCanceller) {
      // Time out the in-process $http request,
      // abandoning its callback listener.
      httpRequestCanceller.resolve();
    }
  }

  // Change WiFi Channel Button.
  $scope.changeWifiChannel = function (channel) {
    if (channel !== undefined && channel != 'seleccione') {
      var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_change_wifi_channel];

      var parameters = {
        channel: channel,
        contractId: $scope.wifi_resp.contractId,
        productId: $scope.wifi_resp.productId,
        subscriptionNumber: $scope.wifi_resp.subscriptionNumber
      };

      // Hook for abandoning the $http request.
      httpRequestCanceller = $q.defer();

      // Set new channel.
      $http.get('/rest/session/token').then(function (response) {
        $http({
          method: 'POST',
          url: config.url,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': response.data
          },
          data: parameters,
          timeout: httpRequestCanceller.promise
        })
          .then(
            function successCallback(response) {
              if (response.data.error) {
                $scope.show_message_data = response.data.message;
                $scope.alertas_servicios();
              }
              else {
                location.reload(true);
              }
            },
            function errorCallback(response) {
              // Error case.
              console.log('Error obteniendo la información');
            }
          );
      });
    }
  };

  // Show message service.
  $scope.alertas_servicios = function () {
    jQuery(".block-changeWifiChannelBlock .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_message_data + '</p></div>');
    var $html_mensaje = jQuery('.block-changeWifiChannelBlock .messages-only').html();
    jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

    jQuery('.messages .close').on('click', function () {
      jQuery('.messages').hide();
    });

    $scope.hideCards();
    jQuery("html, body").animate({scrollTop: 0}, 700);
  };

  // Clear fields.
  $scope.cancelChangeWifiChannel = function () {
    cancelChangeChannelRequest();

    jQuery('select#wifi_channel option').removeProp('selected');
    jQuery('select#wifi_channel option:first').prop('selected', 'selected');
    jQuery('select#wifi_channel').material_select();
  };
}
