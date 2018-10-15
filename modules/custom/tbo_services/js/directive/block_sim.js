/**
 * @file
 * Implements directive ngBlockSim.
 */

myApp.directive('ngBlockSim',[ '$http', ngBlockSim]);

function ngBlockSim($http) {
  return {
    restrict: 'EA',
    controller: BlockSimController,
    link: linkFunc
  };

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_block_sim];

    $http({
      method: 'GET',
      url: config.url
    })
    .then(function (resp) {
      scope.blockSimData = resp.data;
    })

    scope.$watch('reasonBlockSim', function (reasonBlockSim) {
      if (reasonBlockSim == 'lost') {
        scope.stateButtonBlockSim = 0;
      }
      else {
        scope.stateButtonBlockSim = 1;
      }
    });
  }
}

BlockSimController.$inject = ['$scope', '$http'];

function BlockSimController($scope, $http) {
  $scope.reasonBlockSim = '';

  // Block Sim Card.
  $scope.blockSim = function () {
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_block_sim];
    var messageError = Drupal.t('Ha ocurrido un error.<br>Se ha presentado una falla en la comunicación con el servicio @serviceName, por favor intente más tarde.');
    messageError = messageError.replace('@serviceName', 'tolBlockUnlock');

    var parameters = {
      document: $scope.blockSimData.document,
      documentType: $scope.blockSimData.documentType,
      msisdn: $scope.blockSimData.msisdn,
      company: $scope.blockSimData.company,
      companyDocument: $scope.blockSimData.companyDocument,
      companyDocumentType: $scope.blockSimData.companyDocumentType,
      contractId: $scope.blockSimData.contractId,
    };

    if ($scope.stateButtonBlockSim == 0) {
      // Disabled button 'Aceptar'.
      $scope.stateButtonBlockSim = 1;

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
              if (!response.data.error) {
                $scope.class_modal = 'alert-success';
              }
              else {
                $scope.class_modal = 'alert-danger';
              }

              $scope.state_modal = response.data.state_modal;

              $scope.document = {
                label: $scope.blockSimData.companyDocumentType,
                data: $scope.blockSimData.document
              };

              $scope.user = {
                label: config.pop_fields.user.label,
                data: response.data.userName
              };

              $scope.line_number = {
                label: config.pop_fields.line_number.label,
                data: $scope.blockSimData.msisdn
              };

              if ($scope.line_number.data !== undefined || $scope.line_number.data != '') {
                $scope.line_number.data = $scope.line_number.data.trim();
                $scope.line_number.data = "(" + $scope.line_number.data.substring(0, 3) + ") " + $scope.line_number.data.substring(3, 6) + "-" + $scope.line_number.data.substring(6, 10);
              }

              $scope.enterprise = {
                label: config.pop_fields.enterprise.label,
                data: $scope.blockSimData.company,
              };

              $scope.detail = {
                label: config.pop_fields.detail.label,
                data: response.data.detail
              };

              $scope.date_change = {
                label: config.pop_fields.date_change.label,
                data: response.data.date
              };

              $scope.hour = {
                label: config.pop_fields.hour.label,
                data: response.data.hour
              };

              $scope.description = {
                label: config.pop_fields.description.label,
                data: response.data.description
              };

              $scope.clearFieldsBlockSim();
              $scope.stateButtonBlockSim = 0;

              angular.element('#block-sim-confirm').modal({
                dismissible: true,
                complete: function() {
                  if (!response.data.error) {
                    location.reload(true);
                  }
                }
              });
              angular.element('#block-sim-confirm').modal('open');
            },
            function errorCallback(response){
              $scope.showMesaggeData = messageError;
              $scope.clearFieldsBlockSim();
              $scope.alertBlockSim();
              $scope.stateButtonBlockSim = 0;
            }
          );
        });
    }
  };

  // Clear fields.
  $scope.clearFieldsBlockSim = function () {
    $scope.reasonBlockSim = '';
  };

  // Show message service.
   $scope.alertBlockSim = function () {
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
      '   <div class="txt-message"><p>' + $scope.showMesaggeData + '</p></div>' +
      ' </div>' +
      '</div>';

    jQuery('.main-top').empty();
    jQuery('.main-top').append(message);
    jQuery('html, body').animate({ scrollTop: 0 }, 750);

    jQuery('.messages .close').on('click', function () {
      jQuery('.main-top').empty();
    });
  }
}
