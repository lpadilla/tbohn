myApp.directive('ngManageCreditCards', ['$http', ngManageCreditCards]);


function ngManageCreditCards($http) {
  var directive = {
    restrict: 'EA',
    controller: ManageCreditCardsController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    var config = drupalSettings.b2bBlock[scope.uuid];
    scope.config = config;
    retrieveInformation(scope, config, el);
    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };
    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        if (scope.cardsCollection.error) {
          jQuery("div.actions", el).hide();
        }
      }
    });

    scope.index = '';

    scope.setIndex = function (number, name, card) {
      // document.getElementById('modal-close-button').click();
      scope.index = number;
      scope.name = name;
      scope.card = card;
      //document.getElementById('modal-open-button').click();
    }

    scope.deleteCard = function (number) {
			if (scope.resources.indexOf(config.url) == -1) {
				var parameters = {};
				parameters['type'] = 'deleteCard';
				parameters['number'] = scope.index;
				parameters['name'] = scope.name;
				parameters['card'] = scope.card;
				var config_data = {
					params: parameters,
					headers: {'Accept': 'application/json'}
				};

				$http.get(config.url, config_data)
					.then(function (response) {
						window.location.reload();
						jQuery(el).parents("section").fadeIn('slow');
					}, function () {
						console.log("Error obteniendo los datos borrado");
					});
			}
		}
  }

  function retrieveInformation(scope, config, el) {
		if (scope.resources.indexOf(config.url) == -1) {
			//Add key for this display
			var parameters = {};
			parameters['type'] = 'getCards';
			var config_data = {
				params: parameters,
				headers: {'Accept': 'application/json'}
			};
			$http.get(config.url, config_data)
				.then(function (resp) {
					scope.cardsCollection = resp.data;
					jQuery(el).parents("section").fadeIn('slow');
				}, function () {
					console.log("Error obteniendo los datos");
				});
		}
	}
}

ManageCreditCardsController.$inject = ['$scope', '$http'];

function ManageCreditCardsController($scope, $http) {
  // Init vars
  if (typeof $scope.cardsCollection == 'undefined') {
    $scope.cardsCollection = "";
    $scope.cardsCollection.error = false;
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
}