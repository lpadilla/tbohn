myApp.directive('ngAtpContractFilter', ['$http', '$rootScope', ngAtpContractFilter]);

function ngAtpContractFilter($http, $rootScope) {
  return {
    restrict: 'EA',
    controller: atpContractFilerController,
    scope: true,
    link: linkFunc
  };

  function linkFunc(scope, el) {
    var config = drupalSettings.atpContractFilterBlock[scope.uuid_data_ng_atp_contract_filter];

    scope.repository = [];
    $rootScope.$on('loadFunctions', function(event, data) {
      scope.loadFunctionsReload(data);
    });

    scope.loadFunctionsReload = function(data) {
      scope.repository.push(data.name);
    };

    retrieveInformation(scope, config, el);
  }

  // Get information
  function retrieveInformation(scope, config, el) {
    $http.get(config.url).then(function(resp) {
      scope.contracts = resp.data.data;
      scope.enterprise_name = resp.data.name;
      scope.select_contract = resp.data.data[0];
      scope.select = "0";

      scope.repository.forEach(function(value) {
        $rootScope.$emit(value, {
          select: scope.select_contract
        });
      });

    });
  }
}

// Reload materialize select after end ng-repeat of contracts
myApp.directive('ngReloadSelect', function() {
  return function(scope) {
    if(scope.$last) {
      scope.$emit('reloadSelect');
    }
  };
});

atpContractFilerController.$inyect = ['$scope', '$http', '$rootScope', '$timeout'];

function atpContractFilerController($scope, $http, $rootScope, $timeout) {

  $rootScope.$on('reloadSelect', function() {
    $scope.reload();
  });

  // Reload materialize select
  $scope.reload = function() {
    $timeout(function() {
      var id = '#contract_filter_'+$scope.uuid_data_ng_atp_contract_filter;
      jQuery(id).material_select();
    }, 1);
  };
  $scope.disableCmpOnDowload = function(bool) {
    if (bool) {
      var id = '#contract_filter_' + $scope.uuid_data_ng_atp_contract_filter;
      jQuery(id).prop('disabled', true);
      jQuery(id).material_select();
      jQuery('.btn-atp-detail-plan').attr('disabled', 'disabled');
      jQuery('.btn-atp-consultar-linea').removeAttr('href');
      jQuery('.btn-atp-consultar-linea').attr('disabled', 'disabled');
    }
    else {
      var id = '#contract_filter_' + $scope.uuid_data_ng_atp_contract_filter;
      jQuery(id).removeAttr('disabled');
      jQuery(id).material_select();
      jQuery('.btn-atp-detail-plan').removeAttr('disabled');
      jQuery('.btn-atp-consultar-linea').each(function (currentValue, val) {
        jQuery(val).attr('href', jQuery(val).attr('data-href-org'));
      });
    }

  };
  // Download contract invoice detail
  $scope.donwloadDetail = function(type) {

    var config = drupalSettings.atpContractFilterBlock[$scope.uuid_data_ng_atp_contract_filter];
    var params = {
      accountId: $scope.select_contract.accountId,
      contract: $scope.select_contract.contract,
      type: type
    };

    $http.get('/rest/session/token').then(function(resp) {
      $scope.disableCmpOnDowload(true);
      $http({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        url: config.url,
        data: params
      }).then(function(response) {
        if(response.error || !response.data.file_name) {
          $scope.showMessageCorpProfile = (response.error) ? response.error.message_error : config.error_message;
          $scope.showErrorMessage();
        }
        else {
          window.location.href = '/adf_core/download-example/' + response.data.file_name + '/NULL';
        }
        $scope.disableCmpOnDowload(false);
      }, function () {
        scope.disableCmpOnDowload(false);
      });
    });
  };
  // Download contract invoice detail
  $scope.donwloadAccountDetail = function (type) {
    var config = drupalSettings.atpContractFilterBlock[$scope.uuid_data_ng_atp_contract_filter];
    var params = {
      // accountId: '_',
      downloadType: 'account',
      type: type
    };
    $http.get('/rest/session/token').then(function (resp) {
      $scope.onDownload = true;
      $scope.disableCmpOnDowload(true);
      $http({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        url: config.url,
        data: params
      }).then(function (response) {
        if (response.error || !response.data.file_name) {
          $scope.showMessageCorpProfile = (response.error) ? response.error.message_error : config.error_message;
          $scope.showErrorMessage();
        }
        else {
          window.location.href = '/adf_core/download-example/' + response.data.file_name + '/NULL';
        }
        $scope.disableCmpOnDowload(false);
      }, function () {
        $scope.disableCmpOnDowload(false);
        $scope.showErrorMessage("Ha ocurrido un error, inténtelo nuevamente.");
      });

    });
  };

  $scope.showColapsable = function () {

    var $colapsableContent = jQuery('.js-colapsable-content');
    var $colapsableArrow = jQuery('.js-show-colapsable');

    $colapsableContent.toggleClass('expanded');
    $colapsableArrow.toggleClass('collapsed');

  };

  $scope.change_contract = function(contract) {
    $scope.select_contract = $scope.contracts[contract];

    $scope.repository.forEach(function(value) {
      $rootScope.$emit(value, {
        select: $scope.select_contract
      });
    });

    var config = drupalSettings.atpContractFilterBlock[$scope.uuid_data_ng_atp_contract_filter];
    var params = {
      params: {
        log:1,
        contract: $scope.contracts[contract].contract,
        accountId: $scope.contracts[contract].accountId
      }
    };
    $http.get(config.url, params).then(function(resp) {});
  };

  // Show message with error.
  $scope.showErrorMessage = function (message) {
    var messageParams = {
      type: 'danger',
      message: $scope.showMessageCorpProfile,
      deleteOthersInThisPos: true
    };
    if(message){
      messageParams.message = message;
    }
    $scope.setMessage(messageParams);

    jQuery('.messages').on('click', function() {
      jQuery('.messages').hide();
    });
  };

  /**
   * setMessage({ selector: '#modal2', type: '[success|pending|danger]', message: 'Guardado exitoso', deleteOthersInThisPos: true }
   * - Si se deja sin "selector" el lo coloca en el ".main-top", que es el area superior de la pagina donde normalmente vemos los bloques
   * - Si se deja sin 'tipo' el coloca el tipo 'success'
   */
  $scope.setMessage = function (options) {
    options = jQuery.extend({
      // These are the defaults.
      selector: ".main-top",
      type: "success",
      message: "",
      deleteOthersInThisPos: true,
    }, options);

    if (options.deleteOthersInThisPos) {
      jQuery(options.selector).parent().find('.our-global-messages').remove();
    }

    jQuery(options.selector).prepend(
      '<div class="our-global-messages messages clearfix messages--success alert alert-'+options.type+'" role="contentinfo" aria-label="">'+
      ' <button type="button" class="close prefix icon-x-cyan" data-dismiss="alert" aria-hidden="true" onclick="this.parentElement.parentElement.removeChild(this.parentElement)" >'+
      ' <span class="path1"></span><span class="path2"></span>'+
      ' </button>'+
      ' <div class="text-alert">'+
      ' <div class="icon-alert">'+
      ' <span class="icon-1"></span>'+
      ' <span class="icon-2"></span>'+
      ' </div>'+
      ' <div class="txt-message"><p>'+options.message+'</p></div>'+
      ' </div>'+
      '</div>'
    );
  };

}
