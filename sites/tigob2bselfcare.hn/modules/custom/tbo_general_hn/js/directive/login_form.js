myApp.directive('ngLoginForm', ['$http', ngLoginForm]);

function ngLoginForm($http) {
	var directive = {
		restrict: 'EA',
		controller: LoginFormController,
		link: linkFunc
	};

	return directive;

	function linkFunc(scope, el, attr, ctrl) {
    scope.validateLoginForm = function () {
      var user_status = false;
      var pass_status = false;

      if(typeof scope.user !== 'undefined' && scope.user.length > 0){
        user_status = true;
      }

      if(typeof scope.pass !== 'undefined' && scope.pass.length > 0){
        pass_status = true;
      }

      if (user_status && pass_status){
        jQuery('#edit-submit').parent().removeClass('disabled');
      }else {
        jQuery('#edit-submit').parent().addClass('disabled');
      }
    }
	}
}

LoginFormController.$inject = ['$scope', '$http'];

function LoginFormController($scope, $http) {
}