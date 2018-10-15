myApp.directive('ngManageUsersTigoAdmin', ['$http', ngManageUsersTigoAdmin]);

function ngManageUsersTigoAdmin($http) {
	var directiveOpt = {
		restrict: 'EA',
		controller: GetUserListController,
		link: getLink
	};

	return directiveOpt;

	function getLink(scope, el) {
		var myConfigs = drupalSettings.TigoAdminListBlock[scope.uuid_data_ng_manage_users_tigo_admin];
		scope.id_user_tigo_admin = 0;
		scope.show_mesagge_data_talb = "";

		getRegister(myConfigs);

		//Load Tigo Admins data
		function getRegister(myConfigs) {
			//Define Params to load data
			var params = {
				filters: myConfigs.filters,
				fields: myConfigs.fields,
				config_pager: myConfigs.config_pager,
				opt: 1,
			};

			//get access token
			$http.get('/rest/session/token').then(function (res) {
				//Get Data
				$http({
					method: 'POST',
					url: myConfigs.url,
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
						'X-CSRF-Token': res.data,
					},
					data: params,
				}).then(function (response) {
					if (response.data.error) {
						scope.show_mesagge_data_talb = response.data.message;
						scope.alertas_servicios_manage_users_tigo_admin();
					} else {
						//Define $vars to print on a Table
						scope.response = response.data;
						var num_tigoUsers = res.data.length;
						var num_rows = myConfigs.config_pager['number_rows_pages'];
						scope.itemsPerPage = num_rows;
						var gap = 1;
						gap += Math.floor(num_tigoUsers / num_rows);
						scope.gap = gap;
						scope.groupToPages();

						scope.numEnterprises = new Array();
						scope.response.forEach(function (item, index) {
							var checkVal = false;
							if (scope.response[index].status == 1) {
								checkVal = true;
							}
							scope.numEnterprises[item.uid] = item.numEmpresa;
							scope.response[index].checkVal = checkVal;
						});
					}
				})
			});
		}
	}
}

GetUserListController.$inject = ['$scope', '$http', '$window'];

function GetUserListController($scope, $http, $window) {

	$scope.pagedItems = [];
	$scope.currentPage = 0;

	//Filter table
	$scope.filterTable = function () {

		var myConfigs = drupalSettings.TigoAdminListBlock[$scope.uuid_data_ng_manage_users_tigo_admin];

		var package = {};
		for (filter in myConfigs['filters']) {
			if (!$scope[filter] == '' || !$scope[filter] === undefined) {
				package[filter] = $scope[filter];
			}
		}

		function isEmptyJSON(obj) {
			for (var i in obj) {
				return false;
			}
			return true;
		}

		var valOpt = '';
		if (isEmptyJSON(package)) {
			var valOpt = 1;
		} else {
			valOpt = 2;
		}

		//Params to send PHP filter function
		var params = {
			fields: myConfigs.fields,
			config_pager: myConfigs.config_pager,
			filters_data: package,
			opt: valOpt,
			enterprises: $scope.numEnterprises,
			path: window.location.pathname
		};

		if (package != '') {
			params.filters_data = package;
		}

		$http.get('/rest/session/token').then(function (res) {
			$http({
				method: 'POST',
				url: myConfigs.url,
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					'X-CSRF-Token': res.data,
				},
				data: params,
			}).then(function (response) {
				if (response.data.error) {
					$scope.show_mesagge_data_talb = response.data.message;
					$scope.alertas_servicios_manage_users_tigo_admin();
				} else {
					//print Data on a table
					$scope.response = response.data;
					$scope.groupToPages();

					$scope.response.forEach(function (item, index) {
						var checkVal = false;
						if ($scope.response[index].status == 1) {
							checkVal = true;
						}
						$scope.response[index].checkVal = checkVal;

					});
				}
			})
		})
	}

	//Enable / Disable Tigo Admin
	$scope.disableAdmin = function (key, numEnterprises, disable_admin, enter_number, status, name) {

		//Validate enterprises Number
		if (numEnterprises > 0 && status == 1) {
			$scope.id_user_tigo_admin = parseInt(disable_admin);
			jQuery('#' + key).prop('checked', true);
			jQuery('#showDisableMessage').modal('open');
		} else {
			var params = {
				disable_admin: disable_admin,
				enter_number: enter_number,
				status: status,
				name: name,
				opt: 3
			};

			var myConfigs = drupalSettings.TigoAdminListBlock[$scope.uuid_data_ng_manage_users_tigo_admin];
			$http.get('/rest/session/token').then(function (response) {
				$http({
					method: 'POST',
					url: myConfigs.url,
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
						'X-CSRF-Token': response.data,
					},
					data: params
				}).then(function (response) {
					if (response.data.error) {
						$window.location.reload();
					} else {
						$scope.actvResponse = response.data;
						$scope.response[key].status = $scope.actvResponse[status];

						if ($scope.actvResponse['success'] == 'success') {
							$window.location.reload();
						}
					}
				});
			});
		}
	};

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

	$scope.reverseTable = function () {
		$scope.response = $scope.response.reverse();
		$scope.groupToPages();

	};

	// calculate page in data
	$scope.groupToPages = function () {
		$scope.pagedItems = [];
		for (var i = 0; i < $scope.response.length; i++) {
			if (i % $scope.itemsPerPage === 0) {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.response[i]];
			} else {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.response[i]);
			}
		}
		$scope.currentPage = 0;
	};

	$scope.not_disable = function () {
	};

	//Show message service
	$scope.alertas_servicios_manage_users_tigo_admin = function () {
		jQuery(".manage-users-tigo-admin .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_talb + '</p></div>');
		$html_mensaje = jQuery('.manage-users-tigo-admin .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery(".manage-users-tigo-admin .messages-only .text-alert .txt-message").remove();

		jQuery('.messages .close').on('click', function () {
			jQuery('.messages').hide();
		});
	}

	//Reser filters
	jQuery('.click-filter-reset').click(function () {
		//reset filters
		var config = drupalSettings.TigoAdminListBlock[$scope.uuid_data_ng_manage_users_tigo_admin];

		//Get value filters
		var parameters = {};
		for (filter in config['filters']) {
			$scope[filter] = '';
		}

		$scope.filterTable();
	});

	$scope.cancelModal = function () {
		$scope.show_mesagge_data_talb = Drupal.t('El usuario no puede desactivarse debido a que tiene empresas asignadas');
		$scope.alertas_servicios_manage_users_tigo_admin();
	}
}