/**
 * @file
 * Configuration of behaviour, for the "Change Security Type" Card.
 */

myApp.directive('ngChangeSecurityType', ['$http', ngChangeSecurityType]);

function ngChangeSecurityType($http) {

  return {
    restrict: 'EA',
    controller: changeSecurityTypeController,
    link: linkFunc
  };

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.changeSecurityTypeBlock[scope.uuid_data_ng_change_security_type];
    scope.state_button = 1;

    retrieveInformation(scope, config, el);

    scope.$watchGroup(['new_password'], function (group) {
      var new_password = group[0];
      var reg_ex = /[^a-zA-Z0-9]+/g;

      scope.state_button = 0;

      // Validate the Password field.
      if (new_password !== '' && new_password !== undefined) {
        // To prevent user entering special characters.
        scope.new_password = new_password.replace(new RegExp(reg_ex), '');
      }
    });
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

            jQuery('#btn-cancel-wifi-security-type').addClass('disabled');
            jQuery('#security_type').prop('disabled', true);
          }
          else {
            scope.state = 0;
            scope.classState = '';
            scope.service = resp.data.productId;
            jQuery('#btn-cancel-wifi-security-type').removeClass('disabled');
            jQuery('#security_type').prop('disabled', false);
          }

          // We deactivate the Change button,
          // so we check first if it pass the validations.
          jQuery('#btn-change-wifi-security-type').addClass('disabled');

          jQuery('#new_password').prop('disabled', true);
        },
        function (response) {
          scope.state = 1;
          scope.classState = 'disabled';
          jQuery('#btn-change-wifi-security-type').addClass('disabled');
          jQuery('#btn-cancel-wifi-security-type').addClass('disabled');
          jQuery('#security_type').prop('disabled', true);
          jQuery('#new_password').prop('disabled', true);
        });

    // Let's configure the class for the password input without the Icon Left.
    if (!config.pop_fields.new_password.password_to_confirm.icon_left) {
      jQuery('div.progress.new-password').addClass('without_icon_left');
    }
  }
}

changeSecurityTypeController.$inject = ['$scope', '$http', '$q'];

// Basic validations.
function validateOnlyLetters($scope, passwordText) {
  // Test if only have letters.
  if (/[a-zA-Z]/.test(passwordText)) {
    $scope.passForce = Drupal.t('Débil');
    $scope.status_new_password = Drupal.t('Añada números');
    jQuery("#change-wifi-security-type .determinate").css("width", "66.6%").removeClass('strong bad').addClass('medium');
    jQuery("#status_new_password").addClass('medium').removeClass('val_bad val_good');

    return true;
  }

  return false;
}

function validateOnlyNumbers($scope, passwordText) {
  // Test if only have numbers.
  if (/[0-9]/.test(passwordText)) {
    $scope.passForce = Drupal.t('Débil');
    $scope.status_new_password = Drupal.t('Añada letras');
    jQuery("#change-wifi-security-type .determinate").css("width", "66.6%").removeClass('strong bad').addClass('medium');
    jQuery("#status_new_password").addClass('medium').removeClass('val_bad val_good');

    return true;
  }

  return false;
}

function validateLettersAndNumbers($scope, passwordText) {
  // Test if all conditions are met.
  if ((/[a-z]+/.test(passwordText) || /[A-Z]+/.test(passwordText)) && /[0-9]+/.test(passwordText)) {
    $scope.passForce = Drupal.t('Fuerte');
    $scope.status_new_password = Drupal.t('Muy bien, lo has logrado');
    jQuery("#change-wifi-security-type .determinate").css("width", "100%").removeClass('medium bad').addClass('strong');
    jQuery("#status_new_password").addClass('val_good').removeClass('val_bad medium');
    jQuery("#new_password").addClass('val_good').removeClass('val_bad medium val_valid');

    return true;
  }

  return false;
}

function resetPasswordInput($scope, security_type) {
  $scope.new_password = '';
  $scope.passForce = '';

  jQuery("#change-wifi-security-type .determinate")
    .css("width", "0%")
    .removeClass('strong medium bad');

  jQuery("#new_password").removeClass('val_good');
  jQuery("#status_new_password").css('color', '#00377B');
  jQuery('#btn-change-wifi-security-type').addClass('disabled');

  switch (security_type) {
    case 'WPA':
    case 'WPA2':
      $scope.status_new_password = Drupal.t('Debe contener entre 8 y 64 caracteres');
      jQuery('#new_password').attr('maxlength', '64').prop('disabled', false);
      break;

    case 'WEP64':
      $scope.status_new_password = Drupal.t('Debe contener 5 caracteres');
      jQuery('#new_password').attr('maxlength', '5').prop('disabled', false);
      break;

    case 'WEP128':
      $scope.status_new_password = Drupal.t('Debe contener 13 caracteres');
      jQuery('#new_password').attr('maxlength', '13').prop('disabled', false);
      break;

    case 'OPEN':
      jQuery('#new_password').attr('maxlength', '').prop('disabled', true);
      jQuery('label[for="new_password"]').removeClass('active');
      $scope.status_new_password = Drupal.t('Con esta configuración cualquier persona se podrá conectar a su red WiFi sin autorización');
      jQuery('#btn-change-wifi-security-type').removeClass('disabled');
      break;

    default:
      $scope.status_new_password = '';
      jQuery('#new_password').attr('maxlength', '0').prop('disabled', true);
      jQuery('label[for="new_password"]').removeClass('active');
      jQuery('#btn-change-wifi-security-type').addClass('disabled');
      break;
  }
}

function changeSecurityTypeController($scope, $http, $q) {
  // Private properties.
  var httpRequestCanceller;

  // Public properties.
  $scope.processing = undefined;
  $scope.response = undefined;

  // Private methods.
  function cancelRequest() {
    if (httpRequestCanceller) {
      // Time out the in-process $http request,
      // abandoning its callback listener.
      httpRequestCanceller.resolve();
    }
  }

  // Validation Functions for the password.
  $scope.validatePasswordWpa = function (passwordText) {
    if (passwordText.length < 8) {
      $scope.status_new_password = Drupal.t('Mínimo 8 caracteres');
      $scope.passForce = Drupal.t('Muy corta');
      jQuery("#change-wifi-security-type .determinate").css("width", "33.3%").removeClass('strong medium').addClass('bad');
      jQuery("#status_new_password").addClass('val_bad').removeClass('val_good medium');

      return false;
    }

    if (passwordText.length >= 8) {
      var onlyLetters = validateOnlyLetters($scope, passwordText);
      var onlyNumbers = validateOnlyNumbers($scope, passwordText);
      var lettersAndNumbers = validateLettersAndNumbers($scope, passwordText);
      if (!onlyLetters || !onlyNumbers || !lettersAndNumbers) {
        return false;
      }
    }

    return true;
  };

  $scope.validatePasswordWep64 = function (passwordText) {
    if (passwordText.length < 5) {
      $scope.status_new_password = Drupal.t('Debe ingresar 5 caracteres');
      $scope.passForce = Drupal.t('Muy corta');
      jQuery("#change-wifi-security-type .determinate").css("width", "33.3%").removeClass('strong medium').addClass('bad');
      jQuery("#status_new_password").addClass('val_bad').removeClass('val_good medium');

      return false;
    }

    if (passwordText.length = 5) {
      var onlyLetters = validateOnlyLetters($scope, passwordText);
      var onlyNumbers = validateOnlyNumbers($scope, passwordText);
      var lettersAndNumbers = validateLettersAndNumbers($scope, passwordText);
      if (!onlyLetters || !onlyNumbers || !lettersAndNumbers) {
        return false;
      }
    }

    return true;
  };

  $scope.validatePasswordWep128 = function (passwordText) {
    if (passwordText.length < 13) {
      $scope.status_new_password = Drupal.t('Debe ingresar 13 caracteres');
      $scope.passForce = Drupal.t('Muy corta');
      jQuery("#change-wifi-security-type .determinate").css("width", "33.3%").removeClass('strong medium').addClass('bad');
      jQuery("#status_new_password").addClass('val_bad').removeClass('val_good medium');

      return false;
    }

    if (passwordText.length = 13) {
      var onlyLetters = validateOnlyLetters($scope, passwordText);
      var onlyNumbers = validateOnlyNumbers($scope, passwordText);
      var lettersAndNumbers = validateLettersAndNumbers($scope, passwordText);
      if (!onlyLetters || !onlyNumbers || !lettersAndNumbers) {
        return false;
      }
    }

    return true;
  };

  $scope.validatePasswordOpen = function (passwordText) {
    return true;
  };

  // Validate the new password according to the selected Security Type.
  $scope.validatePassword = function (new_password, security_type) {
    var result = false;

    if (new_password != '' && new_password !== undefined) {
      var passwordValidated = false;
      switch (security_type) {
        case 'WPA':
        case 'WPA2':
          passwordValidated = $scope.validatePasswordWpa(new_password);
          break;

        case 'WEP64':
          passwordValidated = $scope.validatePasswordWep64(new_password);
          break;

        case 'WEP128':
          passwordValidated = $scope.validatePasswordWep128(new_password);
          break;
      }

      if (!passwordValidated) {
        jQuery('#btn-change-wifi-security-type').addClass('disabled');
      }
      else {
        jQuery('#btn-change-wifi-security-type').removeClass('disabled');
      }
    }
    else if (new_password == '' || new_password === undefined) {
      resetPasswordInput($scope, security_type);
    }
  };

  // Function for select security event.
  $scope.change_security_type = function (security_type) {
    // We reset the password input status.
    resetPasswordInput($scope, security_type);

  };

  // Change Security Type Button.
  $scope.changeSecurityType = function (new_password) {
    var config = drupalSettings.changeSecurityTypeBlock[$scope.uuid_data_ng_change_security_type];
    var security_type = jQuery('select#security_type').val();

    if ((security_type !== undefined && security_type != 'seleccione')) {
      if (security_type == 'OPEN' || (new_password !== undefined && new_password != '')) {
        var parameters = {
          security_type: security_type,
          new_password: new_password,
          contractId: $scope.wifi_resp.contractId,
          productId: $scope.wifi_resp.productId,
          subscriptionNumber: $scope.wifi_resp.subscriptionNumber
        };

        // Hook for abandoning the $http request.
        httpRequestCanceller = $q.defer();

        // Set Security Type and new Password.
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
            .then(function successCallback(response) {
              if (response.data.error) {
                if (response.data.message_error === undefined || response.data.message_error === '') {
                  $scope.show_message_data = response.data.message;
                }
                else {
                  $scope.show_message_data = response.data.message_error;
                }

                $scope.alertChangeSecurityType();
              }
              else {
                location.reload(true);
              }

            }, function errorCallback() {
               location.reload(true);
            });
        });
      }
    }
  };

  /**
   * Cancels the "Change Wifi Security Type" request and clear the fields.
   */
  $scope.cancelChangeSecurityTypeRequest = function () {
    cancelRequest();

    // Reset inputs values.
    $scope['new_password'] = '';
    jQuery('#new_password').val('').prop('disabled', true);
    jQuery('select#security_type option').prop('selected', false);
    jQuery('select#security_type option:first').prop('selected', true);
    jQuery('select#security_type').material_select();
    jQuery('label[for="new_password"]').removeClass('active');

    // Reset validation's text.
    $scope.passForce = '';
    $scope.status_new_password = '';
    jQuery("#change-wifi-security-type .determinate").css("width", "0%")
      .removeClass('strong medium bad');

    // Disable the Change button.
    if (!jQuery('#btn-change-wifi-security-type').hasClass('disabled')) {
      jQuery('#btn-change-wifi-security-type').addClass('disabled');
    }
  };

  // Show message service.
  $scope.alertChangeSecurityType = function () {
    jQuery(".block-changeSecurityTypeBlock .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_message_data + '</p></div>');
    $html_mensaje = jQuery('.block-changeSecurityTypeBlock .messages-only').html();
    jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

    jQuery('.messages .close').on('click', function () {
      jQuery('.messages').hide();
    });
  }
}
