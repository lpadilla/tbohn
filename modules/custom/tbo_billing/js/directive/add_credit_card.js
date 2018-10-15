myApp.directive('ngAddCreditCard', ['$http', ngAddCreditCard]);


function ngAddCreditCard($http) {
  var directive = {
    restrict: 'EA',
    controller: AddCreditCardController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    // var config = drupalSettings.b2bBlock[scope.uuidd];
    // retrieveInformation(scope, config, el);
    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };
    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        /*if (scope.summary.error) {
         jQuery("div.actions", el).hide();
         }*/
      }
    });

    scope.icon_card = 'default';
    scope.code_lenght = 3;

    //Formato para el número de teléfono
    scope.validatePhone = function () {
      response = validateFormatPhone(scope.phone);
      console.log(response);
      scope.phone = response['phone'];
      scope.phoneStatus = response['status'];

      if (scope.phoneStatus == false) {
        jQuery("#phone").removeClass('invalid-phone');
        jQuery("#phone").removeClass('error');
        jQuery("#phone").removeClass('valid-phone');
        jQuery("#phone").removeClass('valid');
        jQuery("#phone").addClass('invalid-phone');
        jQuery("#phone").addClass('error');
      } else {
        jQuery("#phone").removeClass('invalid-phone');
        jQuery("#phone").removeClass('error');
        jQuery("#phone").removeClass('valid-phone');
        jQuery("#phone").removeClass('valid');
        jQuery("#phone").addClass('valid-phone');
        jQuery("#phone").addClass('valid');
      }

      setTimeout(function () {
        var elemento = document.getElementById('phone');
        elemento.setSelectionRange(scope.phone.length, scope.phone.length);
      }, 100);

    }


    //Validacion del tipo de tarjeta
    scope.cardValidate = function () {
      scope.card = scope.card.replace(/\s/g, '');
      scope.card = scope.card.replace(/\D/g, '');
      var aux_card_number = scope.card;
      var aux_card = '';

      for (i = 0; i < scope.card.length; i++) {
        if (i == 4 || i == 8 || i == 12) {
          aux_card = aux_card + '-' + scope.card[i];
        } else {
          aux_card = aux_card + scope.card[i];
        }
      }

      scope.card = aux_card;

      if (scope.card.length > 19){
        scope.card = scope.card.substr(0, 19);
        aux_card_number = aux_card_number.substr(0,16);
      }

      if (scope.card.length < 1) {
        scope.icon_card = 'default'
      }

      var digit = '';
      aux = scope.card.substr(0, scope.card.length - 1);
      digit = scope.card.substr(scope.card.length - 1);
      firts_digit = scope.card.substr(0, 1);
      firts_two_digits = parseInt(scope.card.substr(0, 2));
      firts_three_digits = parseInt(scope.card.substr(0, 3));
      var isnumC = /^\d+$/.test(digit);

      if (isnumC) {
        if (firts_digit == '4') {
          scope.icon_card = 'visa';
          scope.code_lenght = 3;
          scope.card_status = validateCreditCard(aux_card_number);
          validateCardLength(scope.card.length, 19);
        }
        else if (firts_two_digits > 49 && firts_two_digits < 56) {
          scope.icon_card = 'mastercard';
          scope.code_lenght = 3;
          scope.card_status = validateCreditCard(aux_card_number);
          validateCardLength(scope.card.length, 19);
        }
        else if (firts_two_digits == 34 || firts_two_digits == 37) {
          scope.icon_card = 'amex';
          scope.code_lenght = 4;
          scope.card_status = validateCreditCard(aux_card_number);
          validateCardLength(scope.card.length, 18);
        }
        else if (firts_two_digits == 36 || firts_two_digits == 38 || firts_two_digits == 39) {
          scope.icon_card = 'diners';
          scope.code_lenght = 3;
          scope.card_status = validateCreditCard(aux_card_number);
          validateCardLength(scope.card.length, 17);
        }
        else if ((firts_three_digits > 299 && firts_three_digits < 306) || firts_three_digits == 309) {
          scope.icon_card = 'dinersclub';
          scope.code_lenght = 3;
          scope.card_status = validateCreditCard(aux_card_number);
          validateCardLength(scope.card.length, 17);
        }
      } else if (scope.card.substr(scope.card.length - 1) == '-') {
        scope.card = aux;
      } else {
        scope.card = aux;
      }

      setTimeout(function () {
        var elemento = document.getElementById('card');
        elemento.setSelectionRange(scope.card.length, scope.card.length);
      }, 100);
    }

    function validateCardLength(cardLength, card) {
      console.log(scope.card_status);
      if (!scope.card_status || cardLength > card || cardLength < card) {
        jQuery("#card").removeClass('invalid-card');
        jQuery("#card").removeClass('error');
        jQuery("#card").removeClass('valid-card');
        jQuery("#card").removeClass('valid');
        jQuery("#card").addClass('invalid-card');
        jQuery("#card").addClass('error');
        if (cardLength > card) scope.icon_card = 'default';
      } else {
        jQuery("#card").removeClass('invalid-card');
        jQuery("#card").removeClass('error');
        jQuery("#card").removeClass('valid-card');
        jQuery("#card").removeClass('valid');
        jQuery("#card").addClass('valid-card');
        jQuery("#card").addClass('valid');
      }
    }

    scope.validateCvv = function () {
      scope.code = scope.code.replace(/\s/g, '');
      scope.code = scope.code.replace(/\D/g, '');
      if (scope.code.length > scope.code_lenght){
        scope.code = scope.code.substr(0, scope.code_lenght);
      }
      digit = '';
      digit = scope.code.substr(scope.code.length - 1);
      isnum = /^\d+$/.test(digit);


      if (!isnum) {
        scope.code = scope.code.substr(0, scope.code.length - 1);
      } else {
        if (scope.code_lenght > scope.code.length) {
          jQuery("#code").removeClass('invalid-code');
          jQuery("#code").removeClass('error');
          jQuery("#code").removeClass('valid-code');
          jQuery("#code").removeClass('valid');
          jQuery("#code").addClass('invalid-code');
          jQuery("#code").addClass('error');
        } else {
          jQuery("#code").removeClass('invalid-code');
          jQuery("#code").removeClass('error');
          jQuery("#code").removeClass('valid-code');
          jQuery("#code").removeClass('valid');
          jQuery("#code").addClass('valid-code');
          jQuery("#code").addClass('valid');
        }
      }
    }

    //valicación del e-mail
    scope.validateMail = function () {
      emailRegex = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
      //Se muestra un texto a modo de ejemplo, luego va a ser un icono
      
      if (emailRegex.test(scope.mail)) {
        jQuery("#email").removeClass('invalid-mail');
        jQuery("#email").removeClass('error');
        jQuery("#email").removeClass('valid-mail');
        jQuery("#email").removeClass('valid');
        jQuery("#email").addClass('valid-mail');
        jQuery("#email").addClass('valid');
      } else {
        jQuery("#email").removeClass('invalid-mail');
        jQuery("#email").removeClass('error');
        jQuery("#email").removeClass('valid-mail');
        jQuery("#email").removeClass('valid');
        jQuery("#email").addClass('invalid-mail');
        jQuery("#email").addClass('error');
      }

      if (scope.mail.length == 0) {
        jQuery("#email").removeClass('invalid-mail');
        jQuery("#email").removeClass('error');
        jQuery("#email").removeClass('valid-mail');
        jQuery("#email").removeClass('valid');
        jQuery("#email").addClass('valid-mail');
        jQuery("#email").addClass('valid');
      }
    }

    scope.years = [];

    var actual_year = new Date().getFullYear();
    //array con los años
    for (i = 0; i <= 10; i++) {
      scope.years.push({year: actual_year + i});
    }

    scope.months = {
      01: '01',
      02: '02',
      03: '03',
      04: '04',
      05: '05',
      06: '06',
      07: '07',
      08: '08',
      09: '09',
      10: '10',
      11: '11',
      12: '12',
    };

    //inicializar selects
    window.onload = function () {
      jQuery('select').material_select();
    }

    scope.validateForm = function () {
      var status = false;
      var mail_classes = document.getElementById("email").className;
      var card_classes = document.getElementById("card").className;
      var code_classes = document.getElementById("code").className;
      var mail_status = false;
      var phone_status = false;
      var address_status = false;
      var card_status = false;
      var code_status = false;
      var month_status = false;
      var year_status = false;

      if (mail_classes.search('invalid-mail') == -1 || typeof scope.mail === 'undefined' || scope.mail.length == 0) {
        mail_status = true;
      }


      if (typeof scope.phone !== 'undefined' && scope.phone.length == 14 && scope.phoneStatus) {
        phone_status = true;
      }

      if (typeof scope.address !== 'undefined' && scope.address.length > 0) {
        address_status = true;
      }

      if (card_classes.search('invalid-card') == -1 && typeof scope.card !== 'undefined' && scope.card.length > 0) {
        card_status = true;
      }

      if (code_classes.search('invalid-code') == -1 && typeof scope.code !== 'undefined' && scope.code.length > 0) {
        code_status = true;
      }

      if (typeof scope.month !== 'undefined' && scope.month) {
        month_status = true;
      }

      if (typeof scope.year !== 'undefined' && scope.year) {
        year_status = true;
      }

      if (mail_status && phone_status && address_status && card_status && code_status && month_status && year_status) {
        status = true;
      }
      return status;
    }

    scope.submitFunction = function () {
      var parameters = {};
      parameters['mail'] = scope.mail;
      parameters['contract'] = scope.contract;
      parameters['phone'] = scope.phone;
      parameters['address'] = scope.address;
      parameters['type_card'] = scope.icon_card;
      parameters['card'] = scope.card;
      parameters['code'] = scope.code;
      parameters['month'] = scope.month;
      parameters['year'] = scope.year.year;
      parameters['brand'] = scope.icon_card;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      $http.get('/tboapi/billing/add/card?_format=json', config_data)
        .then(function (resp) {
          scope.summary = resp.data;
          if (scope.summary['message'] === 'Error') {
						window.location.reload();
          }
          else {
            if (scope.summary['url'] != '') {
							window.location.href = scope.summary['url'];
            }
            else {
							window.location.reload();
            }
          }
        }, function () {
          console.log("Error obteniendo los datos");
        });
    }
  }

  function retrieveInformation(scope, config, el) {
    if (scope.resources.indexOf(config.url) == -1) {
      //Add key for this display
      var parameters = {};
      parameters['type'] = config.type;
      parameters['config_columns'] = config.config_columns;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      console.log(config.url);
      $http.get(config.url, config_data)
        .then(function (resp) {
          scope.summary = resp.data;
          console.log(scope.summary);
          jQuery(el).parents("section").fadeIn('slow');
        }, function () {
          console.log("Error obteniendo los datos");
        });
    }
  }
}

AddCreditCardController.$inject = ['$scope', '$http'];

function AddCreditCardController($scope, $http) {
  // Init vars
  if (typeof $scope.adminCompany == 'undefined') {
    $scope.adminCompany = "";
    $scope.adminCompany.error = false;
  }

  if (typeof $scope.resources == 'undefined') {
    $scope.resources = [];
  }


  //Declare vars and function for ordering
  $scope.predicate = 'attraction';
  $scope.reverse = false;
  $scope.order = function (predicate) {
    $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
    $scope.predicate = predicate;
  };
}