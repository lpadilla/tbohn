myApp.directive('ngInvoicesHistory', ['$http', ngInvoicesHistory]);

function ngInvoicesHistory($http) {
	var directive = {
		restrict: 'EA',
		controller: InvoicesHistoryController,
		link: linkFunc
	};

	return directive;

	function linkFunc(scope, el, attr, ctrl) {
		var config = drupalSettings.invoicesHistoryBlock[scope.uuid_data_ng_invoices_history];
		scope.environment_history = config['environment'];
		scope.show_mesagge_data = "";

		//Cambia el modelo cuando cambia el elemento seleccionado
		jQuery(el).change(function () {
			jQuery("#billing-select-enterprise").material_select();
			jQuery("#billing-select-contract").material_select();
		});

		//generateEnterprise(scope, config, el);

		scope.invoicesHistory = [];
		scope.contracts = [];
		scope.currentEnterprise = {'document_number': 1, 'name': 'Seleccione'};
		scope.result = config['environment'];

		getContractData(scope, config, el);

		scope.apiIsLoading = function () {
			return $http.pendingRequests.length > 0;
		};

		//Add apiIsLoading
		scope.$watch(scope.apiIsLoading, function (v) {
			jQuery("#billing-select-enterprise").material_select();
			jQuery("#billing-select-contract").material_select();
			if (v == false) {
				jQuery(el).parents("section").fadeIn(400);
				if (scope.invoicesHistory.error) {
					jQuery("div.actions", el).hide();
				}
			}
		});
	}

	function getContractData(scope, config, el) {
		scope.invoicesHistory = [];
		scope.pagedItems = [];
		scope.itemsPerPage = 0;
		var parameters = {};
		parameters['getDataFinish'] = 'yes';

		var config_data = {
			params: parameters,
			headers: {'Accept': 'application/json'}
		};
		$http.get(config.url, config_data)
			.then(function (resp) {
				if (resp.data.error) {
					scope.show_mesagge_data = resp.data.message;
					scope.alertas_servicios_history();
				}
				else {
					scope.invoicesHistory = resp.data;
					var num_companies = scope.invoicesHistory.length;
					console.log(config.config_pager);
					var num_rows = config.config_pager['number_rows_pages'];
					scope.itemsPerPage = num_rows;
					var gap = 1;
					gap += Math.floor(num_companies / num_rows);
					scope.gap = gap;
					scope.currentPage = 0;
					scope.groupToPages();
				}
			}, function () {
				console.log("Error obteniendo los datos");
			});
	}

}

InvoicesHistoryController.$inject = ['$scope', '$http'];

function InvoicesHistoryController($scope, $http) {
	// Init vars
	if (typeof $scope.invoicesHistory == 'undefined') {
		$scope.invoicesHistory = "";
		$scope.invoicesHistory.error = false;
	}

	if (typeof $scope.resources == 'undefined') {
		$scope.resources = [];
	}

	$scope.groupedItems = [];
	$scope.pagedItems = [];
	$scope.currentPage = 0;

	$scope.orderReverse = function () {
		$scope.invoicesHistory = $scope.invoicesHistory.reverse();
		$scope.groupToPages();
	}

	// calculate page in place
	$scope.groupToPages = function () {
		$scope.pagedItems = [];
		for (var i = 0; i < $scope.invoicesHistory.length; i++) {
			if (i % $scope.itemsPerPage === 0) {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.invoicesHistory[i]];
			} else {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.invoicesHistory[i]);
			}
		}
	};

	// calculate page in place
	$scope.test = function () {
		jQuery('.dropdown-button').dropdown({

				inDuration: 300,
				outDuration: 225,
				constrainWidth: false, // Does not change width of dropdown to that of the activator
				hover: true, // Activate on hover
				gutter: 0, // Spacing from edge
				belowOrigin: false, // Displays dropdown below the button
				alignment: 'left', // Displays dropdown with edge aligned to the left of button
				stopPropagation: false // Stops event propagation
			}
		);
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

	//Declare vars and function for ordering
	$scope.predicate = 'attraction';
	$scope.reverse = false;
	$scope.order = function (predicate) {
		$scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
		$scope.predicate = predicate;
	};

	$scope.myFunc = function ($event, url) {
		$event.preventDefault();
		jQuery('#modal_history').load(url).dialog('open');
	};

	$scope.downloadDetail = function ($event) {
		$event.preventDefault();
	}

	//Show message service
	$scope.alertas_servicios_history = function () {
		jQuery(".historical_invoice .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
		$html_mensaje = jQuery('.historical_invoice .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery('.messages .close').on('click', function() {
			jQuery('.messages').hide();
		});
	}

}
