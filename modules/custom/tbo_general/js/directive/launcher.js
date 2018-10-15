myApp.directive('ngLauncher', ['$http', ngLauncher]);


function ngLauncher($http) {
  var directive = {
    restrict: 'EA',
    controller: LauncherController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_launcher];
    scope.launcherCategory = config.category;
    scope.url_launcher=config.url;
    scope.launcher_category=config.categoryExist;
  }
}

LauncherController.$inject = ['$scope', '$http', '$location'];

function LauncherController($scope, $http, $location) {
  $scope.insertLog=function (option) {
    if($scope.launcher_category){
      $http.get('/rest/session/token').then(function(resp) {
        var parameters = {};
        parameters['option']=option;
        $http({
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json' ,
            'X-CSRF-Token': resp.data
          },
          data: parameters,
          url: $scope.url_launcher
        });
      });
    }

  }
}