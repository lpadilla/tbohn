myApp.directive('ngTransactionCategory', ['$http', ngTransactionCategory]);


function ngTransactionCategory($http) {
  var directive = {
    restrict: 'EA',
    controller: TransactionCategoryController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    var config = drupalSettings.b2bBlock[scope.uuid_ng_transaction_category];
    scope.currentView=config.currentView;
    scope.url=config.url;

  }
}

TransactionCategoryController.$inject = ['$scope', '$http', '$location'];

function TransactionCategoryController($scope, $http, $location) {
  $scope.insertLog=function (option,launcher,currentView) {
    var parameters = {option:option};
    if(launcher==false){
      $scope.currentView=currentView;
    }
    var config_data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };
    $http.get($scope.url,config_data);
  }

 }