/**
 * @file
 * Implements directive ngChangeWifiPass.
 */

myApp.directive('ngChangeWifiPass',[ '$http', ngChangeWifiPass]);

function ngChangeWifiPass($http) {

  return {
    restrict: 'EA',
    controller: changeWifiPassController,
    link: linkFunc
  };

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_change_wifi_pass];
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

      if (resp.data.status != 'Activo') {
        scope.state = 1;
        scope.classState = 'disabled';
        jQuery('#Cancelar').addClass('disabled');
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
      jQuery('#Cancelar').addClass('disabled');
    })
  }
}

// Directive to prevent user entering special characters.
myApp.directive("regExInput", function () {
  "use strict";
  return {
    restrict: "A",
    scope: {},
    replace: false,
    link: function (scope, element, attrs, ctrl) {
      element.bind('keypress', function (event) {
        var reg_ex = "^[a-zA-Z0-9]+$";
        var regex = new RegExp(reg_ex);
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
          event.preventDefault();
          return false;
        }
      });
    }
  };
});

changeWifiPassController.$inject = ['$scope', '$http'];

function changeWifiPassController($scope, $http) {

  // Validate password.
  $scope.validateStatus = function (text_val, text_confirm) {
    if (text_val != '' || text_val !== undefined) {
      if (text_val.length < 8) {
        $scope.status_pass = 'Mínimo 8 caracteres';
        $scope.passForce = 'Muy corta';
        jQuery("#ChangeWifiPass .determinate").css("width", "33.3%").removeClass('strong medium').addClass('bad');
        jQuery('#status_pass').addClass('bad').removeClass('strong medium');
      }

      if (text_val.length >= 8) {
        if (/[a-z]/.test(text_val)) {
          $scope.passForce = 'Débil';
          $scope.status_pass = 'Añada números';
          jQuery("#ChangeWifiPass .determinate").css("width", "66.6%").removeClass('strong bad').addClass('medium');
          jQuery('#status_pass').addClass('medium').removeClass('strong bad');
        }
        if (/[0-9]/.test(text_val)) {
          $scope.passForce = 'Débil';
          $scope.status_pass = 'Añada letras';
          jQuery("#ChangeWifiPass .determinate").css("width", "66.6%").removeClass('strong bad').addClass('medium');
          jQuery('#status_pass').addClass('medium').removeClass('strong bad');
        }

        if ((/[a-z]+/.test(text_val) || /[A-Z]+/.test(text_val)) && /[0-9]+/.test(text_val)) {
          $scope.passForce = 'Fuerte';
          $scope.status_pass = 'Muy bien, lo has logrado';
          jQuery("#ChangeWifiPass .determinate").css("width", "100%").removeClass('medium bad').addClass('strong');
          jQuery('#status_pass').addClass('strong').removeClass('medium bad');
        }
      }
    }

    if (text_val == '' || text_val === 'undefined') {
      $scope.passForce = '';
      $scope.status_pass = '';
      jQuery("#ChangeWifiPass .determinate").css("width", "0%").removeClass('strong medium bad');
    }

    $scope.validatePass(text_val, text_confirm);
  };

  // Validate password confirm.
  $scope.validatePass = function (pass, val_pass) {
    if (val_pass != pass) {
      $scope.val_status = 'La clave no coincide';
      jQuery("#val_status").addClass('error').removeClass('good');
      jQuery("#Cambiar").attr('disabled', 'disabled');
      jQuery("#password_confirm").addClass('val_bad').removeClass('val_good');
    }
    else if (pass == val_pass) {
      $scope.val_status = 'Las claves coinciden';
      jQuery("#password_confirm").addClass('val_good').removeClass('val_bad');
			jQuery("#val_status").addClass('good').removeClass('error');

      if ((/[a-z]+/.test(pass) || /[A-Z]+/.test(pass)) && /[0-9]+/.test(pass) && pass.length >= 8) {
        jQuery("#val_status").addClass('good').removeClass('error');
        $scope.state_button = 0;
      }
      else {
        $scope.state_button = 1;
        $scope.val_staus = 'la contraseña no cumple con los requerimientos';
      }
    }

    if (val_pass === '' || val_pass === undefined) {
      $scope.val_status = '';
      jQuery(".val_status").removeClass('good error');
      jQuery("#password_confirm").removeClass('val_good val_bad');
    }
  };

  // Show or hide the password.
  $scope.showHide = function () {
    if ($scope.state == 0) {
      var type = jQuery("#password").attr('type');

      if (type == 'password') {
        jQuery("#password").attr('type', 'text');
        jQuery("#show_hide").addClass("icon-show-cyan").removeClass("icon-hide-cyan");
      }

      if (type == 'text') {
        jQuery("#password").attr('type', 'password');
        jQuery("#show_hide").addClass("icon-hide-cyan").removeClass("icon-show-cyan");
      }
    }
  };

  // Change WiFi Password.
  $scope.change = function (pass) {

    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_change_wifi_pass];

    var parameters = {
      password: pass,
      contractId: $scope.wifi_resp.contractId,
      productId: $scope.wifi_resp.productId,
      subscriptionNumber: $scope.wifi_resp.subscriptionNumber
    };

    // Set new password.
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
          if (response.data.message_error == '')
          {
            $scope.show_mesagge_data = response.data.message;
          }
          else {
            $scope.show_mesagge_data = response.data.message_error;
          }

          $scope.alertas_servicios();
        }
        else {
          location.reload(true);
        }

      },
      function errorCallback(response) {
        // Error case.
        console.log('Error obteniendo la información');
      });
    });
  };

  // Clear fields.
  $scope.clearFields = function () {

    // Reset input value.
    $scope['password'] = '';
    $scope['password_confirm'] = '';
    jQuery('#password').val('');
    jQuery('#password_confirm').val('');

    // Reset validation text's.
    $scope.val_status = '';
    $scope.passForce = '';
    $scope.status_pass = '';
    jQuery("#ChangeWifiPass .determinate").css("width", "0%").removeClass('strong medium bad');
    jQuery("#password_confirm").removeClass('val_good val_bad');

    // Disabled button.
    if (jQuery('#Cambiar').attr('disabled') === undefined) {
      jQuery('#Cambiar').attr('disabled', 'disabled');
    }
  };

  // Show message service.
  $scope.alertas_servicios = function () {
    jQuery(".block-changeWifiPasswordBlock .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
    $html_mensaje = jQuery('.block-changeWifiPasswordBlock .messages-only').html();
    jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

    jQuery('.messages .close').on('click', function () {
      jQuery('.messages').hide();
    });
  }
}
