myApp.directive('ngReAssignBetweenUsersTigoAdmin', ['$http', ngReAssignBetweenUsersTigoAdmin]);

function ngReAssignBetweenUsersTigoAdmin($http) {
	var directive = {
		restrict: 'EA',
		controller: CompaniesTigoAdminController,
		link: linkFunc
	};

	return directive;

	function linkFunc(scope, el, attr, ctrl) {
		var config = drupalSettings.ReAssingBusinessBetweenUsersTigoAdminBlock[scope.uuid_data_ng_re_assign_between_users_tigo_admin];
		scope.show_mesagge_data = '';
		retrieveInformation(scope, config, el);

		scope.apiIsLoading = function () {
			return $http.pendingRequests.length > 0;
		};
		scope.$watch(scope.apiIsLoading, function (v) {
			if (v == false) {

				if (scope.companiesList.error) {
					jQuery("div.actions", el).hide();
				}
			}
		});

	}

	function retrieveInformation(scope, config, el) {
		if (scope.resources.indexOf(config.url) == -1) {
			//Add key for this display
			var parameters = {};
			parameters['config_columns'] = config.uuid;
			parameters['config_name'] = config.config_name;
			var config_data = {
				params: parameters,
				headers: {'Accept': 'application/json'},
			};

			$http.get(config.url, config_data)
				.then(function (resp) {
					if (resp.data.error) {
						scope.show_mesagge_data = resp.data.message;
						scope.alertas_re_assing_business_users_tigo_admin();
					} else {
						var rta = resp.data;
						scope.companiesList = rta.lista;
						var num_companies = rta.num;
						var num_rows = config.config_pager['number_rows_pages'];
						scope.itemsPerPage = num_rows;

						if (typeof num_companies === "undefined") {
							num_companies = 0;
						}
						scope.num_companies = num_companies;
						if (scope.num_companies == 0) {

						} else {
							var gap = 1;
							gap += Math.floor(num_companies / num_rows);
							scope.gap = gap;
							scope.groupToPages();
						}
					}
				}, function () {
					console.log("Error obteniendo los datos");
				});
		}
	}
}
CompaniesTigoAdminController.$inject = ['$scope', '$http'];

function CompaniesTigoAdminController($scope, $http, el) {
	// Init vars

	$scope.tigos = {};

	$scope.selection = [];

	$scope.toggleSelection = function (companiValue) {
		var idx = $scope.selection.indexOf(companiValue);
		if (idx > -1) {
			$scope.selection.splice(idx, 1);
		} else {
			$scope.selection.push(companiValue);
		}
	};


	$scope.selecTigos = function (companiValue, selected) {
		$scope.tigos[companiValue] = selected;
	};

	$scope.seleccionado = function (companiValue, selected) {

		if ($scope.tigos[companiValue] && $scope.tigos[companiValue] == selected) {
			return 'selected';
		} else {
			return '';
		}
	}

	if (typeof $scope.companiesList == 'undefined') {
		$scope.companiesList = "";
		$scope.companiesList.error = false;
	}

	if (typeof $scope.resources == 'undefined') {
		$scope.resources = [];
	}

	$scope.initMaterialSelect = function () {
		jQuery('select').material_select();
	}

	$scope.masivo = function () {
		$tigo = $scope.tigoMSelected;
		for (var i = 0; i < $scope.selection.length; i++) {
			$scope.tigos[$scope.selection[i]] = $tigo;
			var select = jQuery('select#company-' + $scope.selection[i]);
			select.val($tigo);
			select.material_select();
		}
		jQuery('a.modal-close').click();
		$scope.submit();
	}

	$scope.initModal = function () {
		jQuery('.modal').modal({
			dismissible: true, // Modal can be dismissed by clicking outside of the modal
			opacity: .5, // Opacity of modal background
			inDuration: 300, // Transition in duration
			outDuration: 200, // Transition out duration
			startingTop: '4%', // Starting top style attribute
			endingTop: '10%', // Ending top style attribute
		});
	}

	$scope.groupedItems = [];
	$scope.pagedItems = [];
	$scope.currentPage = 0;

	// calculate page in place
	$scope.groupToPages = function () {
		$scope.pagedItems = [];
		for (var i = 0; i < $scope.companiesList.length; i++) {
			if (i % $scope.itemsPerPage === 0) {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.companiesList[i]];
			} else {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.companiesList[i]);
			}
		}
	};

	$scope.submit = function () {
		var config = drupalSettings.ReAssingBusinessBetweenUsersTigoAdminBlock[$scope.uuid_data_ng_re_assign_between_users_tigo_admin];
		var data = {};
		for (var i = 0; i < $scope.selection.length; i++) {
			if (typeof $scope.tigos[$scope.selection[i]] === "undefined" || $scope.tigos[$scope.selection[i]] == "") {
				jQuery('.mensaje-error').text(Drupal.t('Debe seleccionar un usuario'));
				return;
			}
			data[$scope.selection[i]] = $scope.tigos[$scope.selection[i]];
		}

		if (Object.keys(data).length > 0) {
			$http.get('/rest/session/token').then(function (resp) {
				$http({
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
						'X-CSRF-Token': resp.data
					},
					data: data,
					url: config.url
				}).then(function successCallback(response) {
					if (response.status == 200) {
						location.reload();
					}
				}, function errorCallback(response) {
					console.log('error obteniendo el servicio metodo post');
				});
			});
		} else {
			jQuery('.mensaje-error').text(Drupal.t('Debe seleccionar las empresas a reasignar'));
			jQuery('.modal-close').trigger('click');
		}
	}

	//Function por filter info
	$scope.filterCompanies = function () {
		//Get config
		var config = drupalSettings.ReAssingBusinessBetweenUsersTigoAdminBlock[$scope.uuid_data_ng_re_assign_between_users_tigo_admin];

		//Add key for this display
		var parameters = {};
		parameters['config_columns'] = config.uuid;
		parameters['config_name'] = config.config_name;

		for (filter in config['filters']) {
			if (!$scope[filter] == '' || !$scope[filter] === undefined) {
				parameters[filter] = $scope[filter];
			}
		}

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
					$scope.alertas_re_assing_business_users_tigo_admin();
				} else {
					$scope.companiesList = resp.data.lista;
					var num_companies = $scope.companiesList.length;
					var num_rows = config.config_pager['number_rows_pages'];
					$scope.itemsPerPage = num_rows;
					var gap = 1;
					gap += Math.floor(num_companies / num_rows);
					$scope.gap = gap;
					$scope.currentPage = 0;
					$scope.groupToPages();
				}
			}, function () {
				console.log("Error obteniendo los datos");
			});
	}

	$scope.orderByProperty = function (propertyName, event) {

		if (propertyName == 'user_name') {
			propertyName = 'full_name';
		}

		if (jQuery(event.target).hasClass('asc')) {
			jQuery(event.target).removeClass('asc').addClass('desc');
			$scope.companiesList = $scope.companiesList.sort(function (a, b) {
				if (propertyName == 'name' || propertyName == 'full_name') {
					var o1 = a[propertyName] || '';
					var o2 = b[propertyName] || '';
				} else {
					return;
				}
				o1 = o1.toLowerCase();
				o2 = o2.toLowerCase();
				if (o1 < o2) return 1;
				if (o1 > o2) return -1;

				return 0;
			});

		} else {
			jQuery(event.target).removeClass('desc').addClass('asc');
			$scope.companiesList = $scope.companiesList.sort(function (a, b) {
				if (propertyName == 'name' || propertyName == 'full_name') {
					var o1 = a[propertyName] || '';
					var o2 = b[propertyName] || '';
				} else {
					return;
				}
				o1 = o1.toLowerCase();
				o2 = o2.toLowerCase();
				if (o1 < o2) return -1;
				if (o1 > o2) return 1;
				return 0;
			});
		}
		$scope.groupToPages();
	}

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
	$scope.reverse = false;
	$scope.order = function (predicate) {
		$scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
		$scope.predicate = predicate;
	};

	//Show message service
	$scope.alertas_re_assing_business_users_tigo_admin = function () {
		jQuery(".block-re-assign-between-users-tigo-admin .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
		$html_mensaje = jQuery('.block-re-assign-between-users-tigo-admin .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery(".block-re-assign-between-users-tigo-admin .messages-only .text-alert .txt-message").remove();

		jQuery('.messages .close').on('click', function() {
			jQuery('.messages').hide();
		});
	}

	jQuery('.click-filter-reset').click(function () {
		//reset filters
		var config = drupalSettings.ReAssingBusinessBetweenUsersTigoAdminBlock[$scope.uuid_data_ng_re_assign_between_users_tigo_admin];

		//Get value filters
		var parameters = {};
		for (filter in config['filters']) {
			$scope[filter] = '';
		}

		$scope.filterCompanies();
	});

}