myApp.directive('ngUpdateUserInfo', ['$http', ngUpdateUserInfo]);


function ngUpdateUserInfo($http) {
  var directive = {
    restrict: 'EA',
    controller: UpdateUserInfoController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var configs = drupalSettings.tbo_account.updateUserInfo;
    scope.messages_update_user = configs['message'];
    scope.formats_global_config = configs['format'];
    scope.text_validate = "";

    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };

    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section")
        .fadeIn(400);
      }
    });

    scope.formatPhone = function () {
      // Delete letters.
      var value = "";
      var value_copy = "";
      value = scope.phoneNumber;
      value_copy = scope.phoneNumber;
      value = value.replace(/[^A-Za-z .-_,:;´`+]/g, '');
      value = value.replace(/\s/g, '');
      value = value.replace(/\D/g, '');
      value = value.replace(/[\s]/g, '');
      value = value.split(' ').join('');
      value = value.replace(/ /g, '');
      value = value.replace(" ","");

      scope.phoneNumber = value;

      // Validate fixed phone.
      first_number = "";
      second_number = "";
      length_phoneNumber = 0;
      if (scope.phoneNumber != undefined && scope.phoneNumber != "undefined") {
        first_number = scope.phoneNumber.substring(0, 1);
        second_number = scope.phoneNumber.substring(0, 2);
        length_phoneNumber = scope.phoneNumber.length;
      }

      // Only numbers.
      is_numeric = false;
      if (length_phoneNumber == 1) {
        var convert_key = parseInt(first_number);
        if (typeof Number.isInteger === "function") {
          is_numeric = Number.isInteger(convert_key);
        }
        else {
          is_numeric = scope.isInteger(convert_key);
        }

        if (!is_numeric) {
          scope.phoneNumber = "";
        }
      }

      // Validate if 0-2, 4, 6-9
      if (first_number != 3 && first_number != 5 && scope.phoneNumber != undefined && scope.phoneNumber != "undefined" && scope.phoneNumber != "") {
        scope.show_message_tooltip(4);
        return false;
      }

      if (first_number == "5" && length_phoneNumber > 2) {
        last_number = scope.phoneNumber.substring(2, 3);
        if (last_number == "0" || last_number == "3" || last_number == "9") {
          scope.show_message_tooltip(5);
          return false;
        }
      }

      if (first_number == "5" && length_phoneNumber > 3) {
        last_number = scope.phoneNumber.substring(3, 4);
        if (last_number == "0") {
          scope.show_message_tooltip(7);
          return false;
        }
      }

      if (first_number == "5" && length_phoneNumber == 1) {
        scope.phoneNumber = scope.phoneNumber + 7;
      }
      else if ((value_copy == "(57" && length_phoneNumber == 2)) {
        scope.phoneNumber = "";
      }

      // Show empty message phone.
      if (scope.phoneNumber == "" || scope.phoneNumber == undefined || scope.phoneNumber == "undefined") {
        scope.show_message_tooltip(1);
        return false;
      }
      else {
        response = validateFormatCellPhone(scope.phoneNumber, scope.formats_global_config['mobile']);
        scope.phoneNumber = response['phone'];
        scope.phoneStatus = response['status'];
        scope.phone_length = response['length'];
        scope.phone_type = response['type'];
        var element = jQuery('#update-cel-number');

        if (scope.phoneNumber == "") {
          scope.show_message_tooltip(1);
        }
        else {
          scope.text_validate = "";
        }

        if (scope.phoneStatus === false) {
          element.removeClass('valid-phone')
          .removeClass('valid')
          .removeClass('error')
          .removeClass('invalid-phone')
          .addClass('invalid-phone')
          .addClass('error');
        }
        else {
          element.removeClass('valid-phone')
          .removeClass('valid')
          .removeClass('error')
          .removeClass('invalid-phone')
          .addClass('valid-phone')
          .addClass('valid');
        }

        // Add show messages.
        if (scope.phone_type == 'fixed' && scope.phone_length == 2) {
          scope.show_message_tooltip(3);
        }
        else if (scope.phone_type == 'fixed' && scope.phone_length == 3) {
          scope.show_message_tooltip(6);
        }
        else if (scope.phone_type == 'fixed' && scope.phone_length < 10) {
          scope.show_message_tooltip(8);
        }
        else if (scope.phone_length < 10) {
          scope.show_message_tooltip(2);
        }


        scope.validateFormUser();
      }
    };

    scope.show_message_tooltip = function (code) {
      if (code == 1) {
        scope.text_validate = scope.messages_update_user['empty_phone_number'];
        jQuery(".form-item-cel-number .js-form-required.form-required").removeClass("active");
      }
      else if (code == 2) {
        scope.text_validate = scope.messages_update_user['minimum_length'];
      }
      else if (code == 3) {
        scope.text_validate = scope.messages_update_user['indicative_deparment'];
      }
      else if (code == 4) {
        scope.text_validate = scope.messages_update_user['fixed_number_invalid'];
      }
      else if (code == 5) {
        scope.text_validate = scope.messages_update_user['indicative_city'];
      }
      else if (code == 6) {
        scope.text_validate = scope.messages_update_user['fixed_contact_number'];
      }
      else if (code == 7) {
        scope.text_validate = scope.messages_update_user['can_not_start_zero'];
      }
      else if (code == 8) {
        scope.text_validate = scope.messages_update_user['must_have_7_digits'];
      }
    };

    scope.validateFormUser = function () {
      var name_status = 0,
        doc_type_status = 0,
        doc_number_status = 0;

      if (scope.userName !== '' && scope.userName !== undefined) {
        name_status = 1;
      }

      if (scope.documentType !== '' && scope.documentType !== undefined) {
        doc_type_status = 1;
      }

      if (scope.documentNumber !== '' && scope.documentNumber !== undefined) {
        doc_number_status = 1;
      }

      if (name_status === 1 && doc_type_status === 1 && doc_number_status === 1 && scope.phoneStatus === true) {
        jQuery('#submit-update-user').parent()
        .removeClass('disabled');
      }
      else {
        jQuery('#submit-update-user').parent()
        .addClass('disabled');
      }
    };


    /**
     *
     * @param event
     * @returns {boolean}
     */
    scope.checkKeyDownPhone = function (event) {
      var is_numeric = false;
      var convert_key = parseInt(event.key);

      if (typeof Number.isInteger === "function") {
        is_numeric = Number.isInteger(convert_key);
      }
      else {
        is_numeric = scope.isInteger(convert_key);
      }

      if (event.keyCode === 8) {
        if (scope.phoneNumber == "" || scope.phoneNumber == undefined || scope.phoneNumber == "undefined") {
          scope.formatPhone();
        }
      }
      else if (is_numeric) {
        // Some action here.
      }
      else {
        if (event.key == "Unidentified") {
          // Validate empty space.
          var current_value = event.target.value;
          var length = current_value.length;
          var last_character = current_value.charAt(current_value.length-1);
          if (last_character == " ") {
            current_value = current_value.substring(0, current_value.length-1);
            jQuery('#update-cel-number').val(current_value);
            scope.phoneNumber = current_value;
          }
          else if (length > 14) {
            current_value = current_value.substring(0, 17);
            jQuery('#update-cel-number').val(current_value);
            scope.phoneNumber = current_value;
          }
          scope.formatPhone();
        }
        else {
          event.preventDefault();
          return false;
        }
      }
    };

    scope.checkKeyDownOtherFields = function (event) {
      if (event.keyCode === 8) {// Down key, increment selectedIndex.
        if (scope.phoneNumber == "" || scope.phoneNumber == undefined || scope.phoneNumber == "undefined") {
          scope.formatPhone();
        }
      }
    }

    window.onload = new function () {
      if (scope.phoneNumber != "" && scope.phoneNumber != undefined && scope.phoneNumber != "undefined") {
        scope.phoneStatus = true;
      }
      scope.validateFormUser();
    };

    scope.isInteger = function (value) {
      return typeof value === "number" &&
        isFinite(value) &&
        Math.floor(value) === value;
    };
  }
}

UpdateUserInfoController.$inject = ['$scope', '$http'];

function UpdateUserInfoController($scope, $http) {
  $scope.userName = "";
  $scope.documentNumber = "";
}
