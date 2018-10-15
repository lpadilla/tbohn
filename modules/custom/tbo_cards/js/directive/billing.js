
myApp.directive('ngBillingInfo', ['$http', 'apiBatch', 'dataCollector', ngBillingInfo]);


function ngBillingInfo($http, apiBatch, dataCollector) {
  var directive = {
    restrict: 'EA',
    controller: BillingInfoController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    //var config = drupalSettings.billingBlock[scope.uuid];
    //retrieveInformation(scope, config, el);
    //tigoApiBatch.init("get_mobile_info", "8902889404");

    var search_key = {};
    search_key = {
      key : 890903407,
      document_type : 'NIT'
    };


    var api = new apiBatch("get_portfolio_movil_data", search_key);

    api.init();

    scope.dataCollector = function () {
      //console.log(dataCollector.getData());
      return dataCollector.getData();
    }
    scope.$watch(scope.dataCollector, function(v){
      console.log('esta es la data');
      console.log(v);
      scope.billinginfo = v;
    });
    scope.apiIsLoading = function() {
      return $http.pendingRequests.length > 0;
    };
    scope.$watch(scope.apiIsLoading, function(v) {
      if (v == false) {

        if(scope.billinginfo.error){
          jQuery("div.actions", el).hide();
        }else{
          jQuery(el).parents("section").fadeIn(400);
        }
      }
    });

  }

  function retrieveInformation(scope, config, el) {
    if ( scope.resources.indexOf(config.url) == -1){
      scope.resources.push(config.url);
      $http.get(config.url)
        .then(function (resp) {
          //console.log(resp.data);
          scope.billinginfo = resp.data;
          if(resp.data['showPayBtn']==false){
            document.getElementById("action_Pagar").outerHTML='';
          }
          formatDateMoments();
        }, function (resp){
          scope.billinginfo.error = true;
          //console.log(resp);
          drupal_set_message(Drupal.t("En este momento no podemos obtener tu información de facturación, intenta de nuevo mas tarde"), "error");
        });
    }
  }


}

BillingInfoController.$inject = ['$scope'];

function BillingInfoController($scope) {
  // Init vars
  if (typeof $scope.billinginfo == 'undefined') {
    $scope.billinginfo = {
      "balance": "$ -----",
      "startDate": "-- -- --",
      "endDate": "-- -- --",
      "expiration": "-- -- --",
      "alert": "",
      "lastAmount": "$ -----",
      "invoiceNumber": "-----"
    };
  }

  if (typeof $scope.resources == 'undefined') {
    $scope.resources = [];
  }


}
