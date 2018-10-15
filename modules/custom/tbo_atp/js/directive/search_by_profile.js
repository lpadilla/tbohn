myApp.directive('ngSearchByProfile', ['$http', ngSearchByProfile]);

function ngSearchByProfile($http) {
	var directive = {
		restrict: 'EA',
		controller: CompaniesListController,
		link: linkFunc
	};

	return directive;

	function linkFunc(scope, el, attr, ctrl) {
		var config = drupalSettings.searchByProfileBlock[scope.uuid_data_ng_search_by_profile];
		scope[config['uuid']] = config;
		scope.quantiyScrollProfile = Number(config['scroll']);
		scope.textBtnDetailNormal = config['text_btn_detail_normal'];
		scope.textBtnDetailExpanded = config['text_btn_detail_expanded'];
		scope.show_mesagge_data_search_profile = '';
		scope.perfiles = {};
		scope.auxScroll = {};

		//Get data
		retrieveInformation(scope, config, el);

		scope.segment = 0;
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

		scope.segment = function () {
			scope.both = ((scope.printed_checked !== undefined && scope.printed_checked != false) && (scope.digital_checked !== undefined && scope.digital_checked != false) ) ? "Ambos" : undefined;
			scope.label_segment = (scope.both !== undefined) ? scope.both : ((scope.printed_checked === true || scope.printed_checked == 'impresa') ? 'Impreso' : ((scope.digital_checked === true || scope.digital_checked == 'digital') ? 'Digital' : ''));

			if (scope.label_segment !== undefined && scope.label_segment != '') {
				scope.event = 'TBO - Tipo de Envío';
				scope.properties = '{"category":"Envío de Factura","label":"' + scope.label_segment + ' - ' + scope.environment + '"}';
			} else {
				scope.event = '';
				scope.properties = '';
			}

			if (scope.details !== undefined && scope.details != '') {
				scope.event_detail = 'TBO - Tipo de Factura';
				var label = (scope.details == 'SUMMARY') ? 'Resumida' : 'Detallada';
				scope.properties_detail = '{"category":"Envío de Factura","label":"' + label + ' - ' + scope.environment + '"}';
			}
		}

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
			// Add key for this display
			var parameters = {};
			parameters['p1'] = getParameterByName('p1');
			var config_data = {
				params: parameters,
				headers: {'Accept': 'application/json'}
			};
			$http.get(config.url, config_data)
				.then(function (resp) {
						if (resp.data.error) {
							scope.show_mesagge_data_search_profile = resp.data.message;
							scope.alertas_search_by_profile();
						} else {
							scope.profiles = resp.data.profiles;
							scope.nameClient = resp.data.client;
							scope.contract_profile = resp.data.contract;
							scope.alldata = resp.data.profiles;

							//Scroll
							scope.invoices = scope.scroll();
							scope.auxScroll = resp.data.profiles;
						}
						//scope.segment();
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

	//Function to load scroll
	$scope.scroll = function (size) {
		var aux = [];
		scroll = {};

		if (size === undefined) {
			var quantity = $scope.quantiyScrollProfile;
		} else {
			var quantity = size + $scope.quantiyScrollProfile;
		}

		if (quantity > 0) {
			var slice = quantity;
			if (quantity > $scope.profiles.length) {
				slice = $scope.profiles.length;
			}
			aux.splice(0, 0, $scope.profiles.slice(0, slice));
			scroll[0] = aux[0];
		}

		return scroll;
	}

	//Scroll Aux
	$scope.scrollAux = function (size) {
		var aux = [];
		scroll = {};

		if (size === undefined) {
			var quantity = $scope.quantiyScrollProfile;
		}
		else {
			var quantity = size + $scope.quantiyScrollProfile;
		}

		if (quantity > 0) {
			var slice = quantity;
			if (quantity > $scope.auxScroll.length) {
				slice = $scope.auxScroll.length;
			}
			aux.splice(0, 0, $scope.auxScroll.slice(0, slice));
			scroll[0] = aux[0];
		}

		return scroll;
	}

	//Show detail
	$scope.showDetail = function ($event, nameProfile, contractProfile) {
		var getClass = $event.target.className;
		var getParentClass = jQuery($event.target).closest('.atp-consumption_detail');
		var getId = $event.target.id;
		var validateClass = getClass.search("collapse");
		if (validateClass == -1) {
			//Add and remove class to a
			jQuery('#' + getId).removeClass("expanded");
			jQuery('#' + getId).addClass("collapse");
			jQuery('#' + getId).text($scope.textBtnDetailNormal);
			//Add and remove class to div
			jQuery('.' + getId).removeClass("expanded");
			jQuery('.' + getId).addClass("collapse");

			getParentClass.removeClass("expanded");
			getParentClass.addClass("collapse");

		} else {
			//Add and remove class to a
			jQuery('#' + getId).removeClass("collapse");
			jQuery('#' + getId).addClass("expanded");

			//Add and remove class to div
			jQuery('.' + getId).removeClass("collapse");
			jQuery('.' + getId).addClass("expanded");

			getParentClass.removeClass("collapse");
			getParentClass.addClass("expanded");

			jQuery('#' + getId).text($scope.textBtnDetailExpanded);

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
	$scope.alertas_search_by_profile = function () {
		jQuery(".block-search-by-profile .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_search_profile + '</p></div>');
		$html_mensaje = jQuery('.block-search-by-profile .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery(".block-search-by-profil .messages-only .text-alert .txt-message").remove();

		jQuery('.messages .close').on('click', function () {
			jQuery('.messages').hide();
		});
	}
}
