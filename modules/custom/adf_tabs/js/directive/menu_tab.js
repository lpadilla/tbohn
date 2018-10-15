myApp.directive('ngMenuTab', ['$http', ngMenuTab]);


function ngMenuTab($http) {
  var directive = {
    restrict: 'EA',
    controller: MenuTabController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_menu_tab];
    scope.currentView_menu=config.currentView;
    scope.url_menu=config.url;

  }
}

MenuTabController.$inject = ['$scope', '$http', '$location'];

function MenuTabController($scope, $http, $location) {
  $scope.insertLog=function (option,currentView) {
     $scope.currentView_menu=currentView;
     jQuery('.card-call').slideDown()

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
        url: $scope.url_menu
      });
    });
  }
  $scope.hideCards = function() {
    jQuery('.card-call').fadeOut("fast")
  }

 }
