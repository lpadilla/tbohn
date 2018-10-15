/**
 * @file
 */

myApp.directive('ngDownloadContract', ['$http', ngDownloadContract]);

function ngDownloadContract($http) {
  var directive = {
    restrict: 'EA',
    controller: downloadContractController,
    link: linkFunc
  };
  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    scope.statusResponse = [];
    scope.statusIni = [];
    scope.urlContract = [];
    scope.firtsDownload = [];
    scope.errorMessage = [];
    scope.contractTitle = [];
    scope.contractTitleRequest = [];
    scope.contractHolder = [];
    scope.trackType = [];
    scope.resultButtom = [];
    var config = drupalSettings.DownloadContractBlock[scope.uuid_ng_download_contract];

    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };
    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
      }
    });

    scope.icon_card = 'default';
    scope.code_lenght = 3;


    scope.downloadContractFunction = function (type_download, uuid) {
      var parameters = {};
      var type = 'fijo';
      number = scope['phone' + uuid];

      if (scope['validationFixed' + uuid] == 'movile') {
        number = number.replace(/\s/g, '');
        number = number.replace(/\D/g, '');
        type = 'móvil';
      }
      else {

      }

      parameters['phone'] = number;
      parameters['type'] = type;

      if (config['use_document'] == 1) {
        parameters['document'] = config['document'];
      }

      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      $http.get(config['url'], config_data)
        .then(function (resp) {
          aux = resp.data;

          if (typeof aux['url'] !== "undefined" || aux['url'] != null) {
            if (type_download == 0) {
              scope.urlContract[uuid] = aux['url'];
              scope.statusResponse[uuid] = 1;
              scope.firtsDownload[uuid] = 1;
            } else {
              //window.open(aux['url'], '_blank');
              location.href = aux['url'];
            }
          }
          else {
            scope.statusResponse[uuid] = 0;
          }
          scope.statusIni[uuid] = 1;
          scope.resultButtom[uuid] =1;
        }, function () {
          scope.statusResponse[uuid] = 0;
          scope.statusIni[uuid] = 1;
          scope.resultButtom[uuid] =1;
        });
    }

    scope.initFunction = function (uuid) {
      scope.statusIni[uuid] = 0;
      scope.statusResponse[uuid] = 0;
      scope.firtsDownload[uuid] = 1;
    }
  }
}

function retrieveInformation(scope, config, el) {
}

downloadContractController.$inject = ['$scope', '$http'];

function downloadContractController($scope, $http) {

  $scope.sendMail = function (type, uuid) {

    if ($scope.firtsDownload[uuid] == 0) {
      $scope.downloadContractFunction(1, uuid);
    } else {
      window.open($scope.urlContract[uuid], '_blank');
    }

    var service_type = 'fijo';
    var data_type = $scope['phone' + uuid];

    if ($scope['validationFixed' + uuid] == 'movile') {
      data_type = data_type.replace(/\s/g, '');
      data_type = data_type.replace(/\D/g, '');
      service_type = 'móvil';
    }

    data_post = {
      type: type,
      service_type: service_type,
      data_type: data_type
    };

    $http.get('/rest/session/token').then(function (resp) {
      // Get Data For Filters;.
      $http({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        data: data_post,
        url: '/tbo-account/download/contract?_format=json'
      }).then(function successCallback(response) {
        data_payment = response.data;
      }, function errorCallback(response) {
        // Called asynchronously if an error occurs
        // or server returns response with an error status.
      });
    });
    $scope.firtsDownload[uuid] = 0;
  }


  // Formato para el número de teléfono.
  $scope.validatePhone = function (uuid) {

    if ($scope['validationFixed' + uuid] == "movile") {
      response = validateFormatPhone($scope['phone' + uuid]);
      $scope['phone' + uuid] = response['phone'];
      $scope['phoneStatus' + uuid] = response['status'];
      $scope['phoneStatus2' + uuid] = response['status2'];

      if ($scope['phoneStatus2' + uuid] == false && $scope['phone' + uuid] != '') {
        $scope.errorMessage[uuid] = Drupal.t('Numero celular invalido');
        jQuery('#phone' + uuid).removeClass('invalid-phone');
        jQuery('#phone' + uuid).removeClass('error');
        jQuery('#phone' + uuid).removeClass('valid-phone');
        jQuery('#phone' + uuid).removeClass('valid');
        jQuery('#phone' + uuid).addClass('invalid-phone');
        jQuery('#phone' + uuid).addClass('error');
      }
      else if ($scope['phoneStatus' + uuid] == false && $scope['phoneStatus2' + uuid] == true && $scope['phone' + uuid] != '') {
        $scope.errorMessage[uuid] = Drupal.t('Debe ingresar 10 dígitos');
        jQuery('#phone' + uuid).removeClass('invalid-phone');
        jQuery('#phone' + uuid).removeClass('error');
        jQuery('#phone' + uuid).removeClass('valid-phone');
        jQuery('#phone' + uuid).removeClass('valid');
        jQuery('#phone' + uuid).addClass('invalid-phone');
        jQuery('#phone' + uuid).addClass('error');
      }
      else if ($scope['phone' + uuid] == '') {
        $scope.errorMessage[uuid] = '';
        jQuery('#phone' + uuid).removeClass('invalid-phone');
        jQuery('#phone' + uuid).removeClass('error');
        jQuery('#phone' + uuid).removeClass('valid-phone');
        jQuery('#phone' + uuid).removeClass('valid');
      }
      else {
        jQuery('#phone' + uuid).removeClass('invalid-phone');
        jQuery('#phone' + uuid).removeClass('error');
        jQuery('#phone' + uuid).removeClass('valid-phone');
        jQuery('#phone' + uuid).removeClass('valid');
        jQuery('#phone' + uuid).addClass('valid-phone');
        jQuery('#phone' + uuid).addClass('valid');
      }
    }

    if ($scope['validationFixed' + uuid] == "fixed") {
      response = validateFormatFixed($scope['phone' + uuid]);
      $scope['phone' + uuid] = response['phone'];
      $scope['phoneStatus' + uuid] = response['status'];

      if ($scope['phoneStatus' + uuid] == false && $scope['phone' + uuid] != '') {
        $scope.errorMessage[uuid] = Drupal.t('Mínimo 4 caracteres');
        jQuery('#phone' + uuid).removeClass('invalid-phone');
        jQuery('#phone' + uuid).removeClass('error');
        jQuery('#phone' + uuid).removeClass('valid-phone');
        jQuery('#phone' + uuid).removeClass('valid');
        jQuery('#phone' + uuid).addClass('invalid-phone');
        jQuery('#phone' + uuid).addClass('error');
      }
      else if ($scope['phone' + uuid] == '') {
        $scope.errorMessage[uuid] = '';
        jQuery('#phone' + uuid).removeClass('invalid-phone');
        jQuery('#phone' + uuid).removeClass('error');
        jQuery('#phone' + uuid).removeClass('valid-phone');
        jQuery('#phone' + uuid).removeClass('valid');
      }
      else {
        jQuery('#phone' + uuid).removeClass('invalid-phone');
        jQuery('#phone' + uuid).removeClass('error');
        jQuery('#phone' + uuid).removeClass('valid-phone');
        jQuery('#phone' + uuid).removeClass('valid');
        jQuery('#phone' + uuid).addClass('valid-phone');
        jQuery('#phone' + uuid).addClass('valid');
      }
    }

    setTimeout(function () {
      var elemento = document.getElementById('phone' + uuid);
      elemento.setSelectionRange($scope['phone' + uuid].length, $scope['phone' + uuid].length);
    }, 100);

    $scope.validateForm(uuid);
  }

  $scope.validateForm = function (uuid) {
    if ($scope['validationFixed' + uuid] == 'fixed') {
      $scope.contractTitle[uuid] = Drupal.t("Contrato") + ' ' + $scope['phone' + uuid];
      $scope.contractTitleRequest[uuid] = Drupal.t("Número de contrato consultado") + ' ' + $scope['phone' + uuid];
      $scope.contractHolder[uuid] = Drupal.t("Ingreso el Número del contrato");
			$scope.trackType = 'fijo';
    }
    else {
      $scope.contractTitle[uuid] = Drupal.t("Número línea") + ' ' + $scope['phone' + uuid];
      $scope.contractTitleRequest[uuid] = Drupal.t("Número línea consultado")+ ' ' + $scope['phone' + uuid];
      $scope.contractHolder[uuid] = Drupal.t("Ingreso el Número de línea");
			$scope.trackType = 'movil';
    }

    var status = 0;
		$scope.resultButtom[uuid] = 0;
    if (typeof $scope['phone' + uuid] !== 'undefined' && $scope['phoneStatus' + uuid]) {
      status = 1;
    }

    if (status == 1) {
      jQuery('#confirm-' + uuid).removeClass('disabled');
    } else {
      jQuery('#confirm-' + uuid).addClass('disabled');
    }
  }

  $scope.resetValueDownload = function (uuid) {
    if ($scope['validationFixed' + uuid] == 'fixed') {
      $scope.contractTitle[uuid] = Drupal.t("Contrato") + ' ' + $scope['phone' + uuid];
      $scope.contractTitleRequest[uuid] = Drupal.t("Número de contrato consultado");
      $scope.contractHolder[uuid] = Drupal.t("Ingreso el Número del contrato");
    }
    else {
      $scope.contractTitle[uuid] = Drupal.t("Número línea") + ' ' + $scope['phone' + uuid];
      $scope.contractTitleRequest[uuid] = Drupal.t("Número línea consultado");
      $scope.contractHolder[uuid] = Drupal.t("Ingreso el Número de línea");
    }

    $scope['phone' + uuid] = '';
		$scope.contractTitle[uuid] = '';
    $scope.errorMessage[uuid] = '';
		$scope.resultButtom[uuid] = 0;
    jQuery('#phone' + uuid).removeClass('invalid-phone');
    jQuery('#phone' + uuid).removeClass('error');
    jQuery('#phone' + uuid).removeClass('valid-phone');
    jQuery('#phone' + uuid).removeClass('valid');
  }

  $scope.placeHolderFunction = function (uuid, placeHolder) {
    if (typeof $scope.contractHolder[uuid] !== 'undefined') {
      return $scope.contractHolder[uuid];
    } else {
      return placeHolder;
    }
  }

  $scope.openChat = function (type) {
    $zopim.livechat.window.show();
  }


  // Function to save audit log.
  $scope.saveAuditLog = function (uuid) {
    var service_type = 'fijo';
    var data_type = $scope['phone' + uuid];

    if ($scope['validationFixed' + uuid] == 'movile') {
      data_type = data_type.replace(/\s/g, '');
      data_type = data_type.replace(/\D/g, '');
      service_type = 'móvil';
    }

    $scope.tracktValidation = function (uuid) {
      if($scope['validationFixed' + uuid] == 'fixed'){
        return 'Fijo';
      }
      else {
        return 'Móvil';
      }
    }

    var params = {
      type: 'log',
      service_type: service_type,
      data_type: data_type
    };

    $http.get('/rest/session/token').then(function (resp) {
      $http({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        data: params,
        url: '/tbo-account/download/contract?_format=json'
      }).then(function successCallback(response) {
      }, function errorCallback(response) {
      });
    });
  }
}
