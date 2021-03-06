myApp.directive('ngCompaniesManage', ['$http', ngCompaniesManage]);

function ngCompaniesManage($http) {
	var directive = {
		restrict: 'EA',
		controller: CompaniesListController,
		link: linkFunc
	};

	return directive;

	/**
	 * @param scope
	 * @param el
	 * @param attr
	 * @param ctrl
	 */
	function linkFunc(scope, el, attr, ctrl) {
		var config = drupalSettings.companiesManageBlock[scope.uuid_data_ng_companies_manage];
		scope.show_mesagge_data = "";
		retrieveInformation(scope, config, el);

		scope.apiIsLoading = function () {
			return $http.pendingRequests.length > 0;
		};
		scope.$watch(scope.apiIsLoading, function (v) {
			if (v == false) {
				jQuery(el).parents("section").fadeIn(400);
				if (scope.companiesList.error) {
					jQuery("div.actions", el).hide();
				}
			}
		});

		scope.order = 'ASC';
		scope.suggestions = [];
		scope.selectedIndex = -1; //currently selected suggestion index
		scope.search = function (key_data) {
			console.log(scope[key_data]);
			if (!scope[key_data] == '' || !scope[key_data] == 'undefined') {
				console.log(config.url + '&autocomplete=' + scope[key_data]);
				$http.get(config.url + '&autocomplete=' + scope[key_data]).success(function (data) {
					scope.suggestions = data;
					scope.selectedIndex = -1;
				});
			} else {
				scope.suggestions = [];
			}
		}
		scope.checkKeyDown = function (event, field) {
			if (event.keyCode === 40) {//down key, increment selectedIndex
				event.preventDefault();
				if (scope.selectedIndex + 1 !== scope.suggestions.length) {
					scope.selectedIndex++;
				}
			}
			else if (event.keyCode === 38) { //up key, decrement selectedIndex
				event.preventDefault();
				if (scope.selectedIndex - 1 !== -1) {
					scope.selectedIndex--;
				}
			}
			else if (event.keyCode === 13) { //enter pressed
				scope.resultClicked(scope.selectedIndex, field);
			}
			else {
				scope.suggestions = [];
			}
		}
		scope.resultClicked = function (index, field) {
			scope[field] = scope.suggestions[index].name;
			scope.suggestions = [];
		}

		// calculate page in data
		scope.groupToPages = function () {
			scope.pagedItems = [];
			for (var i = 0; i < scope.companiesList.length; i++) {
				if (i % scope.itemsPerPage === 0) {
					scope.pagedItems[Math.floor(i / scope.itemsPerPage)] = [scope.companiesList[i]];
				} else {
					scope.pagedItems[Math.floor(i / scope.itemsPerPage)].push(scope.companiesList[i]);
				}
			}

			scope.currentPage = 0;
		}
	}

	/**
	 * Implements function retrieveInformation to get init data
	 *
	 * @param scope
	 * @param config
	 * @param el
	 */
	function retrieveInformation(scope, config, el) {
		if (scope.resources.indexOf(config.url) == -1) {
			//Add key for this display
			var parameters = {};
			parameters['config_columns'] = config.uuid;
			parameters['config_name'] = config.config_name;
			var config_data = {
				params: parameters,
				headers: {'Accept': 'application/json'}
			};
			$http.get(config.url, config_data)
				.then(function (resp) {
					if (resp.data.error) {
						scope.show_mesagge_data = resp.data.message;
						scope.alertas_manage_companies();
					} else {
						scope.companiesList = resp.data;
						var num_companies = scope.companiesList.length;
						var num_rows = config.config_pager['number_rows_pages'];
						scope.itemsPerPage = num_rows;
						var gap = 1;
						gap += Math.floor(num_companies / num_rows);
						scope.gap = gap;
						scope.groupToPages();
					}
				}, function () {
					console.log("Error obteniendo los datos");
				});
		}
	}
}

CompaniesListController.$inject = ['$scope', '$http'];

/**
 * Implements function to controller
 *
 * @param $scope
 * @param $http
 * @constructor
 */
function CompaniesListController($scope, $http) {
	// Init vars
	if (typeof $scope.companiesList == 'undefined') {
		$scope.companiesList = "";
		$scope.companiesList.error = false;
	}

	if (typeof $scope.resources == 'undefined') {
		$scope.resources = [];
	}

	//variables to paginate
	$scope.groupedItems = [];
	$scope.pagedItems = [];
	$scope.currentPage = 0;

	//Function por filter info
	$scope.filterCompanies = function () {
		//Get config
		var config = drupalSettings.companiesManageBlock[$scope.uuid_data_ng_companies_manage];
		//Get value filters
		var parameters = {};
		for (filter in config['filters']) {
			if (!$scope[filter] == '' || !$scope[filter] === undefined) {
				parameters[filter] = $scope[filter];
			}
		}

		parameters['config_columns'] = config.uuid;
		parameters['config_name'] = config.config_name;
		parameters['filter'] = 'yes';

		//Add config to url
		var config_data = {
			params: parameters,
			headers: {'Accept': 'application/json'}
		};

		//Get Data For Filters;
		$http.get(config.url, config_data)
			.then(function (resp) {
				if (resp.data.error) {
					$scope.show_mesagge_data = resp.data.message;
					$scope.alertas_manage_companies();
				} else {
					$scope.companiesList = resp.data;
					var num_companies = $scope.companiesList.length;
					var num_rows = config.config_pager['number_rows_pages'];
					$scope.itemsPerPage = num_rows;
					var gap = 1;
					gap += Math.floor(num_companies / num_rows);
					$scope.gap = gap;
					$scope.groupToPages();
				}
			}, function () {
				console.log("Error obteniendo los datos");
			});
	}

	//function to order data
	$scope.orderReverse = function (column) {
		var columns = column;
		console.log(String(column));
		if (column == 'name') {
			if ($scope.order == 'ASC') {
				$scope.companiesList.sort(function (a, b) {
					var textA = b.name.toLowerCase();
					var textB = a.name.toLowerCase();
					return textA < textB ? -1 : textA > textB ? 1 : 0;
				});
				$scope.order = 'DESC';
			} else {
				$scope.companiesList.sort(function (a, b) {
					//return String(a.name) - String(b.name)
					var textA = a.name.toLowerCase();
					var textB = b.name.toLowerCase();
					return textA < textB ? -1 : textA > textB ? 1 : 0;
				});
				$scope.order = 'ASC';
			}
		} else if (column == 'document_number') {
			console.log('entre a docu');
			if ($scope.order == 'ASC') {
				$scope.companiesList.sort(function (a, b) {
					var textA = b.document_number.toLowerCase();
					var textB = a.document_number.toLowerCase();
					return textA < textB ? -1 : textA > textB ? 1 : 0;
				});
				$scope.order = 'DESC';
			} else {
				$scope.companiesList.sort(function (a, b) {
					//return String(a.name) - String(b.name)
					var textA = a.document_number.toLowerCase();
					var textB = b.document_number.toLowerCase();
					return textA < textB ? -1 : textA > textB ? 1 : 0;
				});
				$scope.order = 'ASC';
			}
		} else {
			if ($scope.order == 'ASC') {
				$scope.companiesList.sort(function (a, b) {
					return b[column] - a[column]
				});
				$scope.order = 'DESC';
			} else {
				$scope.companiesList.sort(function (a, b) {
					return a[column] - b[column]
				});
				$scope.order = 'ASC';
			}
		}

		$scope.groupToPages();
	}

	//functions to paginate
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

	$scope.setPage = function () {
		$scope.currentPage = this.n;
	};

	//Declare vars and function for ordering
	$scope.predicate = 'attraction';

	//open modal to change status company
	$scope.doIfChecked = function (value, $event) {
		var name = $event.target.name;
		var change_name = name.replace(/ /g, "|");
		if (value == false) {
			$event.target.checked = true;
		}
		else {
			$event.target.checked = false;
		}
		jQuery('#modal').load('/account/manage/message/changeState/' + $event.target.id + '/' + change_name + window.location.pathname + '/' + value).dialog('open');
	}

	//open modal to delete company
	$scope.deleteCompany = function ($event, document_number, name) {
		$event.preventDefault();
		var change_name = name.replace(/ /g, "|");
		jQuery('#modal').load('/account/manage/message/deleteCompany/' + document_number + '/' + change_name + window.location.pathname).dialog('open');
	}

	//Show message service
	$scope.alertas_manage_companies = function () {
		jQuery(".block-manage-companies .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
		$html_mensaje = jQuery('.block-manage-companies .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery(".block-manage-companies .messages-only .text-alert .txt-message").remove();

		jQuery('.messages .close').on('click', function () {
			jQuery('.messages').hide();
		});
	}

	//Reset filters
	jQuery('.click-filter-reset').click(function () {
		//reset filters
		var config = drupalSettings.companiesManageBlock[$scope.uuid_data_ng_companies_manage];

		//Get value filters
		var parameters = {};
		for (filter in config['filters']) {
			$scope[filter] = '';
		}

		$scope.filterCompanies();
	});
}
