myApp.directive('ngUsersList', ['$http', ngUsersList]);

function ngUsersList($http) {
	var directive = {
		restrict: 'EA',
		controller: UsersListController,
		link: linkFunc
	};

	return directive;

	function linkFunc(scope, el, attr, ctrl) {
		var config = drupalSettings.usersListBlock[scope.uuid_data_ng_users_list];
		scope.show_mesagge_data_users_list = "";

		retrieveInformation(scope, config, el);

		scope.apiIsLoading = function () {
			return $http.pendingRequests.length > 0;
		};
		scope.$watch(scope.apiIsLoading, function (v) {
			if (v == false) {
				jQuery(el).parents("section").fadeIn(400);
				if (scope.usersList.error) {
					jQuery("div.actions", el).hide();
				}
			}
		});
	}

	function retrieveInformation(scope, config, el) {

		if (scope.resources.indexOf(config.url) == -1) {
			$http.get('/rest/session/token').then(function (resp) {
				//console.log(resp.data);
				var parameters = {
					fields: config.fields,
					pager: config.config_pager
				};

				$http({
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
						'X-CSRF-Token': resp.data
					},
					data: parameters,
					url: config.url
				}).then(function successCallback(response) {
					// this callback will be called asynchronously
					if (response.data.error) {
						scope.show_mesagge_data_users_list = response.data.message;
						scope.alertas_servicios_users_list();
					} else {
						// when the response is available
						scope.usersList = response.data;

						var num_logs = scope.usersList.length;
						//console.log(auditlogs.length);
						scope.num_logs = num_logs;
						var num_rows = config.config_pager['number_rows_pages'];
						scope.itemsPerPage = config.config_pager['number_rows_pages'];
						var gap = 1;
						gap += Math.floor(num_logs / num_rows);
						scope.gap = gap;
						scope.groupToPages();
					}
				}, function errorCallback(response) {
					console.log(response);
					console.log('error obteniendo el servicio metodo post');
				});

			});
		}
	}
}

UsersListController.$inject = ['$scope', '$http'];

function UsersListController($scope, $http) {
	// Init vars
	if (typeof $scope.usersList == 'undefined') {
		$scope.usersList = "";
		$scope.usersList.error = false;
	}

	if (typeof $scope.resources == 'undefined') {
		$scope.resources = [];
	}

	$scope.groupedItems = [];
	$scope.pagedItems = [];
	$scope.currentPage = 0;

	//Function por filter info
	$scope.filterUsers = function () {
		//Get config
		var config = drupalSettings.usersListBlock[$scope.uuid_data_ng_users_list];

		//Get value filters
		var filters = {};
		for (filter in config['filters']) {
			if (!$scope[filter] == '' || !$scope[filter] === undefined) {
				filters[filter] = $scope[filter];
			}
		}

		$http.get('/rest/session/token').then(function (resp) {
			var parameters = {
				fields: config.fields,
				filters: filters,
				pager: config.config_pager,
			};

			$http({
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					'X-CSRF-Token': resp.data
				},
				data: parameters,
				url: config.url
			}).then(function successCallback(response) {
				// this callback will be called asynchronously
				if (response.data.error) {
					$scope.show_mesagge_data_users_list = response.data.message;
					$scope.alertas_servicios_users_list();
				} else {
					// when the response is available
					$scope.usersList = response.data;
					var num_logs = $scope.usersList.length;
					//console.log(auditlogs.length);
					$scope.num_logs = num_logs;

					var num_rows = config.config_pager['number_rows_pages'];
					$scope.itemsPerPage = config.config_pager['number_rows_pages'];
					var gap = 1;
					gap += Math.floor(num_logs / num_rows);
					$scope.gap = gap;
					$scope.currentPage = 0;
					$scope.groupToPages();
				}
			}, function errorCallback(response) {
				console.log('error obteniendo el servicio metodo post');
			});

		});
	}


	$scope.orderReverse = function () {
		$scope.usersList = $scope.usersList.reverse();
		$scope.groupToPages();
	}

	$scope.orderByProperty = function (propertyName, event) {

		if (propertyName == 'name') {
			propertyName = 'full_name';
		}
		if (propertyName == 'roles_target_id') {
			propertyName = 'user_role';
		}

		if (jQuery(event.target).hasClass('asc')) {
			jQuery(event.target).removeClass('asc').addClass('desc');
			$scope.usersList = $scope.usersList.sort(function (a, b) {
				if (propertyName == 'company_name') {
					var o1 = a[propertyName][0] || '';
					var o2 = b[propertyName][0] || '';
				} else {
					var o1 = a[propertyName] || '';
					var o2 = b[propertyName] || '';
				}
				o1 = o1.toLowerCase();
				o2 = o2.toLowerCase();
				if (o1 < o2) return 1;
				if (o1 > o2) return -1;

				return 0;
			});

		} else {
			jQuery(event.target).removeClass('desc').addClass('asc');
			$scope.usersList = $scope.usersList.sort(function (a, b) {
				if (propertyName == 'company_name') {
					var o1 = a[propertyName][0] || '';
					var o2 = b[propertyName][0] || '';
				} else {
					var o1 = a[propertyName] || '';
					var o2 = b[propertyName] || '';
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

	// calculate page in place
	$scope.groupToPages = function () {
		$scope.pagedItems = [];
		for (var i = 0; i < $scope.usersList.length; i++) {
			if (i % $scope.itemsPerPage === 0) {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.usersList[i]];
			} else {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.usersList[i]);
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

	$scope.usersListClear = function () {
		jQuery('#create-users').trigger("reset");
	}

	//Reset filters
	jQuery('.click-filter-reset').click(function () {
		//reset filters
		var config = drupalSettings.usersListBlock[$scope.uuid_data_ng_users_list];

		//Get value filters
		var parameters = {};
		for (filter in config['filters']) {
			$scope[filter] = '';
		}

		$scope.filterUsers();
	});

	//Show message service
	$scope.alertas_servicios_users_list = function () {
		jQuery(".block-users-list .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_users_list + '</p></div>');
		$html_mensaje = jQuery('.block-users-list .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery(".block-users-list .messages-only .text-alert .txt-message").remove();

		jQuery('.messages .close').on('click', function () {
			jQuery('.messages').hide();
		});
	}
}
