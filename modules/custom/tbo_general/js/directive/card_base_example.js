myApp.directive('ngCardBaseExample', ['$http', ngCardBaseExample]);

function ngCardBaseExample($http) {
	var directive = {
		restrict: 'EA',
		controller: CardBaseExampleController,
		link: linkFunc
	};

	return directive;

	function linkFunc(scope, el, attr, ctrl) {
		var config = drupalSettings.CardBaseExampleBlock[scope.uuid];
		scope.environment = config['environment'];

		getData(scope, config, el);

		scope.apiIsLoading = function () {
			return $http.pendingRequests.length > 0;
		};

		//Add apiIsLoading
		scope.$watch(scope.apiIsLoading, function (v) {
			jQuery("#billing-select-enterprise").material_select();
			jQuery("#billing-select-contract").material_select();
			if (v == false) {
				jQuery(el).parents("section").fadeIn(400);
				if (scope.dataExample.error) {
					jQuery("div.actions", el).hide();
				}
			}
		});
	}

	function getData(scope, config, el) {
		scope.dataExample = [];
		scope.pagedItems = [];
		scope.itemsPerPage = 0;
		var parameters = {};

		var config_data = {
			params: parameters,
			headers: {'Accept': 'application/json'}
		};
		$http.get(config.url, config_data)
			.then(function (resp) {
				scope.dataExample = resp.data;
				var num_companies = scope.dataExample.length;
				var num_rows = config.config_pager['number_rows_pages'];
				scope.itemsPerPage = config.config_pager['number_rows_pages'];
				var gap = 1;
				gap += Math.floor(num_companies / num_rows);
				scope.gap = gap;
				scope.currentPage = 0;
				scope.groupToPages();

			}, function () {
				console.log("Error obteniendo los datos");
			});
	}

}

CardBaseExampleController.$inject = ['$scope', '$http'];

function CardBaseExampleController($scope, $http) {
	// Init vars
	if (typeof $scope.dataExample == 'undefined') {
		$scope.dataExample = "";
		$scope.dataExample.error = false;
	}

	if (typeof $scope.resources == 'undefined') {
		$scope.resources = [];
	}

	$scope.groupedItems = [];
	$scope.pagedItems = [];
	$scope.currentPage = 0;

	$scope.orderReverse = function () {
		$scope.dataExample = $scope.dataExample.reverse();
		$scope.groupToPages();
	}

	// calculate page in place
	$scope.groupToPages = function () {
		$scope.pagedItems = [];
		for (var i = 0; i < $scope.dataExample.length; i++) {
			if (i % $scope.itemsPerPage === 0) {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.dataExample[i]];
			} else {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.dataExample[i]);
			}
		}
	};

	$scope.range = function (size, start, end) {
		var ret = [];
		if (size < end) {
			end = size;
		}
		for (var i = 0; i < end; i++) {
			ret.push(i);
		}
		return ret;
	};

	$scope.prevPage = function () {
		if ($scope.currentPage > 0) {
			$scope.currentPage--;
		}
	};

	$scope.nextPage = function () {
		if ($scope.currentPage < $scope.pagedItems.length - 1) {
			$scope.currentPage++;
		}
	};

	$scope.counter = 1;

	$scope.setPage = function () {
		$scope.currentPage = this.n;
	};

	$scope.myFunc = function ($event) {
		$event.preventDefault();
		jQuery('#modal_history').load($event.target.href).dialog('open');
	};

	$scope.downloadDetail = function ($event) {
		$event.preventDefault();
	}
}