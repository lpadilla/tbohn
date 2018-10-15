/**
 * @file
 * Configuration of behaviour, for the "Admin Cards Access" Card.
 */

myApp.directive('ngAdminCardsAccess', ['$http', ngAdminCardsAccess]);

function ngAdminCardsAccess($http) {
  var directive = {
    restrict: 'EA',
    controller: AdminCardsAccessController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_admin_cards_access];
    scope.show_message_data = "";
    retrieveInformation(scope, config, el);

    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };

    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        if (scope.cardsAccessResults.error) {
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

    // Modal configuration.
    jQuery('#modal-save-cards-access-permissions').modal({
        dismissible: true,
        opacity: .5,
        inDuration: 300,
        outDuration: 200,
        startingTop: '4%',
        endingTop: '10%'
      }
    );

    scope.message_row_display = {display: 'none'};
    scope.modalLabelCompanyName = '';
    scope.activateAllSwitchedOn = false;
    scope.inactivateAllSwitchedOn = false;
    scope.suggestionsAjax = [];
    scope.selectedIndexAjax = -1;
    scope.cardAccessIds = [];

    scope.resultClickedAjax = function (index, field) {
      scope[field] = scope.suggestionsAjax[index].name;
      scope.suggestionsAjax = [];
    };

    scope.suggestions = [];
    scope.selectedIndex = -1; // Currently selected suggestion index.

    jQuery('#card_access_status').prop('disabled', true);
    jQuery('#card_access_status').material_select();
  }

  function retrieveInformation(scope, config, el) {
    if (scope.resources.indexOf(config.url) == -1) {
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
}

AdminCardsAccessController.$inject = ['$scope', '$http'];

function AdminCardsAccessController($scope, $http) {
  // Init vars.
  if (typeof $scope.cardsAccessResults == 'undefined') {
    $scope.cardsAccessResults = "";
    $scope.cardsAccessResults.error = false;
  }

  if (typeof $scope.resources == 'undefined') {
    $scope.resources = [];
  }

  // Function por filter info.
  $scope.filterCardsAccess = function () {
    // Get config.
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_admin_cards_access];

    // Get value filters.
    var parameters = {};
    if (!$scope['validated_company_id'] == '' || !$scope['validated_company_id'] === undefined) {
      parameters['validated_company_id'] = $scope['validated_company_id'];

      if (!$scope['selected_block_id'] == '' || !$scope['selected_block_id'] === undefined) {
        parameters['selected_block_id'] = $scope['selected_block_id'];
      }

      if (!$scope['card_access_status'] == '' || !$scope['card_access_status'] === undefined) {
        parameters['card_access_status'] = $scope['card_access_status'];
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
              $scope.cardsAccessResults = resp.data;
              jQuery('#btn_guardar_configuraciones').removeClass('disabled');
            }
            else {
              $scope.message_row_display = {display: 'block'};
              $scope.cardsAccessResults = '';
              jQuery('#btn_guardar_configuraciones').addClass('disabled');
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
  $scope.clearFiltersAdminCardsAccess = function () {
    $scope.document_number = '';
    $scope.validated_company_id = '';
    $scope.selected_block_id = '';
    $scope.card_name = '';
    $scope.company_name = '';
    $scope.company_document = '';
    disableCardNameField();
    disableCardStatusField();

    jQuery('#document_type option[value=""]').prop('selected', true);
    jQuery('#document_type').material_select();

    jQuery('#card_access_status option[value=""]').prop('selected', true);
    jQuery('#card_access_status').material_select();

    jQuery('#btn-filter-cards-access').addClass('disabled');

    // Clean the results table.
    $scope.cardsAccessResults = '';
    $scope.message_row_display = {display: 'none'};
  };

  $scope.orderReverse = function () {
    $scope.cardsAccessResults = $scope.cardsAccessResults.reverse();
    $scope.groupToPages();
  };

  // Declare vars and ordering function.
  $scope.predicate = 'attraction';
  $scope.reverse = false;

  $scope.order = function (predicate) {
    $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
    $scope.predicate = predicate;
  };

  // Show message service.
  $scope.cardAccessAlert = function () {
    jQuery(".block-adminCardsAccessBlock .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_message_data + '</p></div>');
    $html_mensaje = jQuery('.block-adminCardsAccessBlock .messages-only').html();
    jQuery('.main-top').append('<div class="messages clearfix messages--danger alert ' + $scope.alertType + '" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

    jQuery(".block-adminCardsAccessBlock .messages-only .text-alert .txt-message").remove();

    jQuery('.messages .close').on('click', function () {
      jQuery('.messages').hide();
    });

    jQuery('html, body').animate({scrollTop: 0}, 737);
  };

  $scope.searchCard = function (key_data) {
    // Get config.
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_admin_cards_access];

    var cardName = jQuery('#card_name').val();
    if (!(cardName == '') && !(cardName == undefined)) {
      $scope.selected_card_name = '';
      $scope.selected_block_id = '';
      $scope.cardsAccessResults = [];

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
      $scope.cardsAccessResults = [];
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
    $scope['selected_block_id'] = $scope.suggestions[index].block_id;
    $scope.suggestions = [];
  };

  $scope.saveCardAccessChangesAlert = function () {
    // Collect the changes in array.
    $scope.cardAccessIds = getAllCardAccessStatus();
    if ($scope.cardAccessIds.length > 0) {
      jQuery('#modal-save-cards-access-permissions').modal('open');
    }
  };

  $scope.saveCardAccessChanges = function () {
    // Get config.
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_admin_cards_access];

    if (typeof $scope.cardAccessIds !== 'undefined' && $scope.cardAccessIds.length > 0) {
      // Send the save request.
      $http.get('/rest/session/token').then(function (resp) {
        var parameters = {};
        parameters['cards_access_info'] = $scope.cardAccessIds;
        $http({
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': resp.data
          },
          data: parameters,
          url: config.url
        })
          .then(function successCallback(response) {
              // This callback will be called asynchronously
              // when the response is available.
              $scope.show_message_data = Drupal.t('Se han guardado los ' +
                'cambios de acceso a las funcionalidades consultadas para la ' +
                'empresa @company_name', {'@company_name': $scope.company_name});
              $scope.alertType = 'alert-success';
              $scope.cardAccessAlert();
            },
            function errorCallback(response) {
              // Called asynchronously if an error occurs
              // or server returns response with an error status.
              $scope.show_message_data = Drupal.t('No se pudieron realizar los ' +
                'cambios, por favor intente más tarde.');
              $scope.alertType = 'alert-danger';
              $scope.cardAccessAlert();
            });
      });
    }
  };

  $scope.modalAceptSaveCardsAccess = function ($event) {
    $event.preventDefault();
    $scope.saveCardAccessChanges();
    jQuery('#modal-save-cards-access-permissions').modal('close');
  };

  $scope.modalCancelSaveCardsAccess = function ($event) {
    $event.preventDefault();
    jQuery('#modal-save-cards-access-permissions').modal('close');
  };

  function enableCardNameField() {
    // Activate the "Card Name" field.
    jQuery('#card_name').removeClass('disabled');
    jQuery('#card_name').prop('disabled', false);
  }

  function disableCardNameField() {
    // Invalidate the "Card Name" field.
    jQuery('#card_name').addClass('disabled');
    jQuery('#card_name').prop('disabled', true);
  }

  function enableCardStatusField() {
    // Activate the "Card Access Status" field.
    jQuery('#card_access_status').removeClass('disabled');
    jQuery('#card_access_status').prop('disabled', false);
    jQuery('#card_access_status').material_select();
  }

  function disableCardStatusField() {
    // Invalidate the "Card Access Status" field.
    jQuery('#card_access_status').addClass('disabled');
    jQuery('#card_access_status').prop('disabled', true);
    jQuery('#card_access_status').material_select();
  }

  function validateCompanyDocument(document_type, document_number) {
    // First we clean the variables.
    $scope.validated_company_id = '';
    $scope.company_name = '';
    $scope.company_document = '';
    $scope.cardsAccessResults = '';

    if (document_number != '' && document_number != undefined &&
      document_type != '' && document_type != undefined) {
      // Get config.
      var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_admin_cards_access];

      // Add key for this display.
      var parameters = {};

      parameters['config_columns'] = config.uuid;
      parameters['config_name'] = config.config_name;
      parameters['document_number'] = document_number;
      parameters['document_type'] = document_type;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      $http.get(config.url, config_data)
        .then(function (companyValidation) {
            if (companyValidation.data.error) {
              $scope.company_name = companyValidation.data.error;
              $scope.company_document = companyValidation.data.error;

              disableCardNameField();
              disableCardStatusField();
              jQuery('#btn-filter-cards-access').addClass('disabled');
            }
            else if (companyValidation.data.result) {
              $scope.validated_company_id = companyValidation.data.company_id;
              $scope.company_name = companyValidation.data.company_name;
              $scope.company_document = companyValidation.data.company_document;

              enableCardNameField();
              enableCardStatusField();
              jQuery('#btn-filter-cards-access').removeClass('disabled');
            }
          },
          function () {
            // Error getting REST data.
          });
    }
    else {
      disableCardNameField();
      disableCardStatusField();
      jQuery('#btn-filter-cards-access').addClass('disabled');
    }
  }

  function activateAllCardsAccess() {
    jQuery('input.switch-card-access').each(function (index) {
      jQuery(this).prop('checked', true);
    });
  }

  function inactivateAllCardsAccess() {
    jQuery('input.switch-card-access').each(function (index) {
      jQuery(this).prop('checked', false);
    });
  }

  function getAllCardAccessStatus() {
    var cardsAccessIds = [];
    jQuery('input.switch-card-access').each(function (index) {
      var originalStatus = jQuery(this).attr('original-status');
      var newStatus = jQuery(this).prop('checked');
      if (newStatus != originalStatus) {
        // Save the this Id.
        cardsAccessIds.push({
          'id': jQuery(this).attr('id-card-access'),
          'status': jQuery(this).prop('checked')
        });
      }
    });

    return cardsAccessIds;
  }

  jQuery('#document_number').on('keyup', function () {
    jQuery('#btn-filter-cards-access').addClass('disabled');
    var document_type = jQuery('#document_type').val();
    var document_number = jQuery('#document_number').val();
    validateCompanyDocument(document_type, document_number);
  });

  jQuery('#document_type').on('change', function () {
    jQuery('#btn-filter-cards-access').addClass('disabled');
    var document_type = jQuery('#document_type').val();
    var document_number = jQuery('#document_number').val();
    validateCompanyDocument(document_type, document_number);
  });

  jQuery('#activate_all_cards').on('click', function () {
    var activateAllSwitchedOn = jQuery(this).is(':checked');
    var inactivateAllSwitchedOn = jQuery('#inactivate_all_cards').is(':checked');

    if (activateAllSwitchedOn) {
      activateAllCardsAccess();
      if (inactivateAllSwitchedOn) {
        jQuery('#inactivate_all_cards').prop('checked', false);
      }
    }
  });

  jQuery('#inactivate_all_cards').on('click', function () {
    var inactivateAllSwitchedOn = jQuery(this).is(':checked');
    var activateAllSwitchedOn = jQuery('#activate_all_cards').is(':checked');

    if (inactivateAllSwitchedOn) {
      inactivateAllCardsAccess();
      if (activateAllSwitchedOn) {
        jQuery('#activate_all_cards').prop('checked', false);
      }
    }
  });

  jQuery('#card_name').on('input', function () {
    $scope.searchCard('card_name');
  });
}
