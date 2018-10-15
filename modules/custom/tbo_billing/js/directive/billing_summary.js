myApp.directive('ngBillingSummary', ['$http', ngBillingSummary]);


function ngBillingSummary($http) {
  var directive = {
    restrict: 'EA',
    controller: BillingSummaryController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_billing_summary];
    retrieveInformation(scope, config, el);
    var orderName = 0;
    var orderAdmin = 0;

    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };
    scope.movilshowWithService = true;
    scope.movilshowWithNotService = false;
    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        if (typeof scope.summary !== 'undefined') {
          if (scope.summary.error) {
            jQuery("div.actions", el).hide();
          }
        }
      }
    });
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
      $http.get(config.url, config_data)
        .then(function (resp) {
          if (resp.data.error) {
            scope.show_mesagge_data_billing_sumary_movil = resp.data.message;;
            scope.alertas_servicios_movil();
          } else {
            scope.summary = resp.data;
            //Set segment track
            scope.segmet_value = scope.summary.segment_amount;
            if(scope.segmet_value !== undefined || scope.segmet_value != '') {
              jQuery(".segment-load.movil").attr('data-segment-load', 1);
            }
          }
        }, function () {
          scope.show_mesagge_data_billing_sumary_movil = "Error obteniendo los datos de movil";
          scope.alertas_servicios_movil();
        });
    }
  }
}

BillingSummaryController.$inject = ['$scope', '$http'];

function BillingSummaryController($scope, $http) {
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

  //Show message service
  $scope.alertas_servicios_movil = function () {
    jQuery(".block-billing-summary-message .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_billing_sumary_movil + '</p></div>');
    $html_mensaje = jQuery('.block-billing-summary-message .messages-only ').html();
    jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger">' + $html_mensaje + '</div>');

    jQuery(".block-billing-summary-message .messages-only .text-alert .txt-message").remove();

    jQuery('.messages .close').on('click', function() {
      jQuery('.messages').hide();
    });
  }

}