/**
 * @file
 * Configuration of behaviour, for the "Consult Companies Blocked Card" Card.
 */

myApp.directive('ngConsultCompaniesBlockedCard', ['$http', ngConsultCompaniesBlockedCard]);

function ngConsultCompaniesBlockedCard($http) {
  var directive = {
    restrict: 'EA',
    controller: ConsultCompaniesBlockedCardController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_consult_companies_blocked_card];
    scope.show_message_data = "";
    retrieveInformation(scope, config, el);

    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };

    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        if (scope.companiesBlockedCardsResults.error) {
          jQuery("div.actions", el).hide();
        }
      }
    });

    // Validate numeric input on the Document Number field.
    scope.$watch('document_number', function (newValue) {
      var document_number = newValue;
      var reg_ex = /[^0-9]+/g;

      // Validate the Document Number field.
      if (document_number != '' && document_number != undefined) {
        // To prevent user entering special characters.
        document_number = document_number.replace(new RegExp(reg_ex), '');
        document_number = (document_number.length > 50) ? document_number.substring(0, 50) : document_number;
        scope.document_number = document_number;
      }
    });

    scope.message_row_display = {display: 'none'};
    scope.suggestionsAjax = [];
    scope.selectedIndexAjax = -1;

    scope.resultClickedAjax = function (index, field) {
      scope[field] = scope.suggestionsAjax[index].name;
      scope.suggestionsAjax = [];
    };

    scope.suggestions = [];
    scope.selectedIndex = -1; // Currently selected suggestion index.
  }

  function retrieveInformation(scope, config, el) {
    var parameters = {};
    parameters['config_columns'] = config.uuid;
    parameters['config_name'] = config.config_name;
    parameters['save_audit_log'] = 'save_audit_log';

    // Add config to url.
    var config_data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };

    // Set an audit log.
    $http.get(config.url, config_data)
      .then(function (resp) {
        },
        function () {
        });
  }
}

ConsultCompaniesBlockedCardController.$inject = ['$scope', '$http'];

function ConsultCompaniesBlockedCardController($scope, $http) {
  // Init vars.
  if (typeof $scope.companiesBlockedCardsResults == 'undefined') {
    $scope.companiesBlockedCardsResults = "";
    $scope.companiesBlockedCardsResults.error = false;
  }

  if (typeof $scope.resources == 'undefined') {
    $scope.resources = [];
  }

  // Filter info.
  $scope.filterCompaniesBlockedCards = function () {
    // Get config.
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_consult_companies_blocked_card];

    // Get value filters.
    var parameters = {};
    if (!$scope['selected_block_id'] == '' || !$scope['selected_block_id'] === undefined) {
      parameters['selected_block_id'] = $scope['selected_block_id'];

      if (!$scope['document_number'] == '' || !$scope['document_number'] === undefined) {
        parameters['document_number'] = $scope['document_number'];
      }

      if (!$scope['document_type'] == '' || !$scope['document_type'] === undefined) {
        parameters['document_type'] = $scope['document_type'];
      }

      parameters['config_columns'] = config.uuid;
      parameters['config_name'] = config.config_name;

      // Add config to url.
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };

      // Get Data For Filters.
      $http.get(config.url, config_data)
        .then(function (resp) {
            if (resp.data.error) {
              $scope.show_message_data = resp.data.message;
              $scope.alertType = 'alert-danger';
              $scope.cardAccessAlert();
            }
            else if (resp.data.length > 0) {
              $scope.message_row_display = {display: 'none'};
              $scope.companiesBlockedCardsResults = resp.data;
            }
            else {
              $scope.message_row_display = {display: 'block'};
              $scope.companiesBlockedCardsResults = '';
            }
          },
          function () {
            $scope.show_message_data = Drupal.t('Error obteniendo los datos REST.');
            $scope.alertType = 'alert-danger';
            $scope.cardAccessAlert();
          });
    }
  };

  // Clear filters and validated values.
  $scope.clearFiltersCompaniesBlockedCards = function () {
    $scope.document_number = '';
    $scope.card_name = '';
    $scope.validated_company_id = '';
    $scope.selected_block_id = '';
    $scope.selected_card_name = '';
    $scope.selected_company_name = '';
    $scope.selected_company_document = '';

    jQuery('#document_type option[value=""]').prop('selected', true);
    jQuery('#document_type').material_select();

    // Clean the results table.
    $scope.companiesBlockedCardsResults = '';
    $scope.message_row_display = {display: 'none'};

    jQuery('#action_card_search_companies_blocked_card').addClass('disabled');
  };

  $scope.orderReverse = function () {
    $scope.companiesList = $scope.companiesList.reverse();
    $scope.groupToPages();
  };

  // Declare vars and function for ordering.
  $scope.predicate = 'attraction';
  $scope.reverse = false;

  $scope.order = function (predicate) {
    $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
    $scope.predicate = predicate;
  };

  // Show message service.
  $scope.cardAccessAlert = function () {
    jQuery(".block-consultCompaniesBlockedCardBlock .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_message_data + '</p></div>');
    $html_mensaje = jQuery('.block-consultCompaniesBlockedCardBlock .messages-only').html();
    jQuery('.main-top').append('<div class="messages clearfix messages--danger alert ' + $scope.alertType + '" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

    jQuery(".block-consultCompaniesBlockedCardBlock .messages-only .text-alert .txt-message").remove();

    jQuery('.messages .close').on('click', function () {
      jQuery('.messages').hide();
    });

    jQuery('html, body').animate({scrollTop: 0}, 737);
  };

  $scope.searchCard = function (key_data) {
    // Get config.
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_consult_companies_blocked_card];

    var cardName = jQuery('#card_name').val();
    if (!(cardName == '') && !(cardName == undefined)) {
      $scope.selected_card_name = '';
      $scope.selected_block_id = '';
      $scope.companiesBlockedCardsResults = [];
      jQuery('#action_card_search_companies_blocked_card').addClass('disabled');

      $http.get(config.url + '&autocomplete_card_name=' + cardName).success(function (data) {
        $scope.suggestions = data;
        $scope.selectedIndex = -1;
        jQuery('#suggestions').show();
      });
    }
    else {
      $scope.suggestions = [];
      $scope.selected_card_name = '';
      $scope.selected_block_id = '';
      $scope.companiesBlockedCardsResults = [];
      jQuery('#action_card_search_companies_blocked_card').addClass('disabled');
      jQuery('#suggestions').hide();
    }
  };

  $scope.checkKeyDownCard = function (event, field) {
    if (event.keyCode === 40) {// Down key, increment selectedIndex.
      event.preventDefault();
      if ($scope.selectedIndex + 1 !== $scope.suggestions.length) {
        $scope.selectedIndex++;
      }
    }
    else if (event.keyCode === 38) { // Up key, decrement selectedIndex.
      event.preventDefault();
      if ($scope.selectedIndex - 1 !== -1) {
        $scope.selectedIndex--;
      }
    }
    else if (event.keyCode === 13) { // Enter pressed.
      $scope.resultClickedCard($scope.selectedIndex, field);
    }
    else {
      $scope.suggestions = [];
    }
  };

  $scope.resultClickedCard = function (index, field) {
    $scope[field] = $scope.suggestions[index].label;
    $scope['selected_card_name'] = $scope.suggestions[index].label;
    $scope['selected_block_id'] = $scope.suggestions[index].block_id;
    $scope.suggestions = [];

    jQuery('#action_card_search_companies_blocked_card').removeClass('disabled');
  };

  $scope.downloadReportBlockedCards = function () {
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_consult_companies_blocked_card];
    var parameters = {
      headers: {
        document_type: 'Tipo de Documento',
        document_number: 'Número de Documento',
        company_name: 'Nombre de Empresa',
        card_name: 'Nombre del Card',
        access_status: 'Estado',
        date: 'Fecha Bloqueo del Card'
      }
    };

    $http.get('/rest/session/token').then(function (resp) {
      $http({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        url: config.url,
        data: parameters
      }).then(function (resp) {
        if (resp.data.file_name != '' && resp.data.file_name != undefined) {
          location.href = '/adf_core/download-example/' + resp.data.file_name + '/NULL';
        }
        else {
          if (resp.data.error != '' && resp.data.error != undefined) {
            $scope.show_message_data = resp.data.error;
          }
          else {
            $scope.show_message_data = Drupal.t('No se pudo generar la ' +
              'descarga, por favor intente más tarde.');
          }

          $scope.alertType = 'alert-danger';
          $scope.cardAccessAlert();
        }
      });
    });
  };

  function validateCompanyDocument(document_type, document_number) {
    if (document_number != '' && document_number != undefined &&
      document_type != '' && document_type != undefined) {
      // Get config.
      var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_consult_companies_blocked_card];

      // Add key for this display.
      var parameters = {};

      parameters['config_columns'] = config.uuid;
      parameters['config_name'] = config.config_name;
      parameters['validate_document_number'] = document_number;
      parameters['validate_document_type'] = document_type;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      $http.get(config.url, config_data)
        .then(function (companyValidation) {
            if (companyValidation.data.error) {
              $scope.validated_company_id = '';
              $scope.selected_company_name = companyValidation.data.error;
              $scope.selected_company_document = companyValidation.data.error;
            }
            else if (companyValidation.data.result) {
              $scope.validated_company_id = companyValidation.data.company_id;
              $scope.selected_company_name = companyValidation.data.company_name;
              $scope.selected_company_document = companyValidation.data.company_document;
            }

            $scope.companiesBlockedCardsResults = '';
            $scope.message_row_display = {display: 'none'};
          },
          function () {
            // Error obteniendo los datos del REST.
          });
    }
    else {
      $scope.companiesBlockedCardsResults = '';
      $scope.message_row_display = {display: 'none'};
      $scope.validated_company_id = '';
      $scope.selected_company_name = 'No hay datos disponibles';
      $scope.selected_company_document = 'No hay datos disponibles';
    }
  }

  jQuery('#document_number').on('keyup', function () {
    var document_type = jQuery('#document_type').val();
    var document_number = jQuery('#document_number').val();
    validateCompanyDocument(document_type, document_number);
  });

  jQuery('#document_type').on('change', function () {
    var document_type = jQuery('#document_type').val();
    var document_number = jQuery('#document_number').val();
    validateCompanyDocument(document_type, document_number);
  });

  jQuery('#card_name').on('input', function () {
    $scope.searchCard('card_name');
  });
}
