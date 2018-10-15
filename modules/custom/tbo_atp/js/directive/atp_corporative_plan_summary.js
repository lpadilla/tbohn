myApp.directive('ngAtpCorporativePlanSummary', ['$http', '$rootScope', ngAtpCorporativePlanSummary]);

function ngAtpCorporativePlanSummary($http, $rootScope) {
  var directive = {
    restrict: 'EA',
    controller: AtpCorporativePlanSummaryController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.AtpCorporativePlanSummaryBlock[scope.uuid];
    scope.environment = config['environment'];

    $rootScope.$emit('loadFunctions' , {
      name: 'reloadDataPlanSummary'
    });

    scope.reloadDataPlanSummary = function(data) {
      scope.contractId = data.select.contract;
      getData(scope, config, el, data.select.accountId);
    };

    $rootScope.$on('reloadDataPlanSummary', function(event, data) {
      scope.reloadDataPlanSummary(data);
    });
  }

  function getData(scope, config, el, accountId) {

    scope.accountId = accountId;
    var parameters = {
      accountId: accountId
    };
    var config_data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };
    $http.get(config.url, config_data)
      .then(function (resp) {
        jQuery(".main-top .our-global-messages").remove();
        if ( resp.data.error == true ) {
          // We validate when the service returns an empty message.
          if ( resp.data.message == '' ) {
            // We use the default message found in Config.
            scope.show_message_data = config.message_error_404_default;
          }
          else {
            scope.show_message_data = resp.data.message;
          }

          scope.showErrorMessage();

          // We update the Corportative Plan Summary Card fields.
          // Se comenta porque el boton no esta deshabilitado cuando hay un error y se esta hiendo a la busquedad por perfil sin parametro en la url
          //scope.accountId = '';
          scope.totalValuePlan = '$0.00';
          scope.servicesAmountPlan = '0';
          scope.linesAmountPlan = '0';
          scope.cycle = 'No disponible';
          scope.profilesCount = '0';
          scope.minimumRank = '$0.00';
          scope.maximumRank = '$0.00';
        }
        else {
          // We update the Corportative Plan Summary Card fields.
          scope.accountId = resp.data.accountId;
          scope.totalValuePlan = resp.data.totalValuePlan;
          scope.servicesAmountPlan = resp.data.servicesAmountPlan;
          scope.linesAmountPlan = resp.data.linesAmountPlan;
          scope.cycle = resp.data.cycle;
          scope.profilesCount = resp.data.profilesCount;
          scope.minimumRank = resp.data.minimumRank;
          scope.maximumRank = resp.data.maximumRank;
        }
      },
      function () {
        console.log("Error obteniendo los datos");
      });
  }
}

AtpCorporativePlanSummaryController.$inject = ['$scope', '$http'];

function AtpCorporativePlanSummaryController($scope, $http) {

  $scope.saveAuditLog = function (urlRedirect) {
    var config = drupalSettings.AtpCorporativePlanSummaryBlock[$scope.uuid];

    // Save the audit log - Click on Details button.
    var params = {
      params: {
        log_details:1,
        contract: $scope.contractId
      }
    };

    $http.get(config.url, params).then(function (resp) {
      // We redirect to the Corporative Plan Details.
      var fullUrlRedirect = urlRedirect + $scope.accountId;

      location.href = fullUrlRedirect;
    });
  };

  // Show message with error.
  $scope.showErrorMessage = function () {
    var messageParams = {
      type: 'danger',
      message: $scope.show_message_data,
      deleteOthersInThisPos: true
    };
    $scope.setMessage(messageParams);

    jQuery('.messages .close').on('click', function() {
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

  // Quita los mensajes
  // removeMessages({ selector: '#modal2' })
  $scope.removeMessages = function (options) {
    options = jQuery.extend({
      // These are the defaults.
      selector: ".main-top",
    }, options);

    jQuery(options.selector).parent().find('.our-global-messages').remove();
  };
}
