myApp.directive('ngAssociatedLines', ['$http', ngAssociatedLines]);

function ngAssociatedLines($http) {
	var directive = {
		restrict: 'EA',
		controller: CompaniesListController,
		link: linkFunc
	};

	return directive;

	function linkFunc(scope, el, attr, ctrl) {
		var config = drupalSettings.associatedLinesBlock[scope.uuid_data_ng_associated_lines];
		scope[config['uuid']] = config;
		scope.show_mesagge_data_associated_lines = '';
		scope.empty_lines = false;
		scope.perfiles = {};
		scope.auxScroll = {};

		// Get data
		retrieveInformation(scope, config, el);

		scope.apiIsLoading = function () {
			return $http.pendingRequests.length > 0;
		};
		scope.$watch(scope.apiIsLoading, function (v) {
			if (v == false) {
				if (scope.environment == 'fijo') {
					jQuery("label[for='address']").addClass("active");
					jQuery("label[for='city']").addClass("active");
				}
				jQuery(el).parents("section").fadeIn(400);
				if (scope.companiesList.error) {
					jQuery("div.actions", el).hide();
				}
			}
		});

		//Infinite scroll
		scope.loadMore = function () {
			if (scope.auxScroll.length > 0) {
				var sizeInvoice = scope.invoices[0].length;
				scope.invoices = scope.scrollAux(sizeInvoice);
			} else if (typeof scope.perfiles[1] != 'undefined') {
				scope.invoices = scope.scroll(sizeInvoice);
			}
		}

	}

	/**
	 * @param String name
	 * @return String
	 */
	function getParameterByName(name) {
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			results = regex.exec(location.search);
		return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}

	/**
	 * Get data
	 *
	 * @param scope
	 * @param config
	 * @param el
	 */
	function retrieveInformation(scope, config, el) {
		if (scope.resources.indexOf(config.url) == -1) {
			//Get parameter NIT
			var p1 = getParameterByName('p1');
			var p2 = getParameterByName('p2');
			scope.p1 = p1;
			scope.p2 = p2;

			//Add key for this display
			var parameters = {};
			parameters['p1'] = p1;
			parameters['p2'] = p2;
			var config_data = {
				params: parameters,
				headers: {'Accept': 'application/json'}
			};
			$http.get(config.url, config_data)
				.then(function (resp) {
						if (resp.data.error) {
							scope.show_mesagge_data_associated_lines = resp.data.message;
							scope.alertas_associated_lines();
						}
						else {
							scope.header_associated = resp.data.header;
							scope.nameClient = resp.data.header.client;
							if (resp.data.lines.length == 0) {
								scope.empty_lines = true;
							}
							else {
								scope.lines = resp.data.lines;
								if (isMobile.phone) {
									var lines_mobile = [];
									for (i = 0; i < scope.lines.length; i++) {
										for (j = 0; j < scope.lines[i].length; j++) {
											lines_mobile.push(scope.lines[i][j]);
										}
									}

									scope.lines = lines_mobile;
								}

								var num_associated_lines = scope.lines.length;
								var num_rows = config.config_pager['number_rows_pages'];
								scope.itemsPerPage = config.config_pager['number_rows_pages'];
								var gap = 1;
								gap += Math.floor(num_associated_lines / num_rows);
								scope.gap = gap;
								scope.currentPage = 0;
								scope.groupToPages();
							}
						}
					},
					function () {
						console.log("Error obteniendo los datos");
					}
				);
		}
	}
}

CompaniesListController.$inject = ['$scope', '$http', '$timeout'];

function CompaniesListController($scope, $http, $timeout) {

	// Init vars
	if (typeof $scope.companiesList == 'undefined') {
		$scope.companiesList = "";
		$scope.companiesList.error = false;
	}

	if (typeof $scope.resources == 'undefined') {
		$scope.resources = [];
	}

	$scope.groupedItems = [];
	$scope.pagedItems = [];
	$scope.currentPage = 0;

	// calculate page in place
	$scope.groupToPages = function () {
		$scope.pagedItems = [];
		for (var i = 0; i < $scope.lines.length; i++) {
			if (i % $scope.itemsPerPage === 0) {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.lines[i]];
			} else {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.lines[i]);
			}
		}
	};

	// range
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

	//Show detail
	$scope.showDetail = function ($event, nameProfile, contractProfile) {
		var getClass = $event.target.className;
		var getParentClass = jQuery($event.target).closest('.atp-consumption_detail');
		var getId = $event.target.id;
		var validateClass = getClass.search("collapse");
		if (validateClass == -1) {
			//Add and remove class to a
			jQuery('#'+getId).removeClass("expanded");
			jQuery('#'+getId).addClass("collapse");
			//Add and remove class to div
			jQuery('.'+getId).removeClass("expanded");
			jQuery('.'+getId).addClass("collapse");

			getParentClass.removeClass("expanded");
			getParentClass.addClass("collapse");

		}
		else {
			//Add and remove class to a
			jQuery('#'+getId).removeClass("collapse");
			jQuery('#'+getId).addClass("expanded");

			//Add and remove class to div
			jQuery('.'+getId).removeClass("collapse");
			jQuery('.'+getId).addClass("expanded");

			getParentClass.removeClass("collapse");
			getParentClass.addClass("expanded");

			//Save audit log
			$scope.saveAuditLog($scope[$event.target.name], nameProfile, contractProfile);
		}
	}

	//function to save audit log
	$scope.saveAuditLog = function (config, nameProfile, contractProfile) {
		var params = {
			'nameProfile': nameProfile,
			'contractProfile': contractProfile
		};

		jQuery('.preloadingContainer').remove();
		if ($scope.resources.indexOf(config.url) == -1) {
			$http.get('/rest/session/token').then(function (resp) {
				$http({
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'Accept': 'application/json',
						'X-CSRF-Token': resp.data
					},
					data: params,
					url: config.url
				}).then(function successCallback(response) {
				}, function errorCallback(response) {
				});
			});
		}
	}

	//Show message exception
	$scope.alertas_associated_lines = function () {
		jQuery(".associated-lines-block .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_associated_lines + '</p></div>');
		$html_mensaje = jQuery('.associated-lines-block .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery(".associated-lines-block .messages-only .text-alert .txt-message").remove();

		jQuery('.messages .close').on('click', function () {
			jQuery('.messages').hide();
		});
	}
}
