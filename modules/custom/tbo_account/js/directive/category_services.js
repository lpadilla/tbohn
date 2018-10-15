myApp.directive('ngCategoryServices', ['$http', ngCategoryServices]);

function ngCategoryServices($http) {

	var directive = {
		restrict: 'EA',
		controller: CategoryServicesController,
		link: linkFunc
	};

	return directive;

	function linkFunc(scope, el, attr, ctrl) {
		var config = drupalSettings.CategoryServicesBlock[scope.uuid_data_ng_category_services];
		scope.show_mesagge_data_category_services = "";

		retrieveInformation(scope, config, el);

		scope.apiIsLoading = function () {
			return $http.pendingRequests.length > 0;
		};
		scope.$watch(scope.apiIsLoading, function (v) {
			if (v == false) {
				jQuery(el).parents("section").fadeIn(400);
				if (scope.category_services.error) {
					jQuery("div.actions", el).hide();
				}
			}
		});
	}

	/**
	 *
	 * @param scope
	 * @param config
	 * @param el
	 * @param uuid
	 */
	function retrieveInformation(scope, config, el) {
		if (scope.resources.indexOf(config.url) == -1) {
			$http.get(config.url)
				.then(function (resp) {
					scope.category_services = resp.data;
					if (resp.data.error) {
						scope.show_mesagge_data_category_services = resp.data.message;
						scope.alertas_servicios_category_services();
					}
				}, function (resp) {
					scope.category_services.error = true;
					scope.show_mesagge_data = Drupal.t("En este momento no podemos obtener la <strong>información de sus servicios</strong>, intenta de nuevo mas tarde."), "error", config['uuid'];
					scope.alertas_servicios();
				});

			$http.get('/tboapi/category_services/list/services_account/' + config['uuid'] + '?_format=json')
				.then(function (resp) {
					//Set segment $var's
					scope.ident = resp.data.userId;
					scope.send = resp.data.send;
					delete resp.data.send;
					delete resp.data.userId;

					scope.portfolio = resp.data;

					if (scope.send == 0) {
						scope.trait = 'assocProduct:{';
						angular.forEach(scope.portfolio, function (value, key) {
							scope.trait = scope.trait + value.productName + '/';
						});
						scope.trait = scope.trait.replace(/\/$/, '') + '}';
						jQuery('.categories').attr('data-segment-load', 1);
					}

				}, function (resp) {
					scope.category_services.error = true;
					scope.show_mesagge_data = Drupal.t("En este momento no podemos obtener la <strong>información de sus servicios</strong>, intenta de nuevo mas tarde."), "error", config['uuid'];
					scope.alertas_servicios();
				});
		}
	}

}

CategoryServicesController.$inject = ['$scope', '$http'];

function CategoryServicesController($scope, $http) {
	// Init vars
	if (typeof $scope.category_services == 'undefined') {
		$scope.category_services = [];
	}

	if (typeof $scope.resources == 'undefined') {
		$scope.resources = [];
	}

	$scope.category_show = function (category) {
		var obj = drupalSettings.CategoryServicesBlock[$scope.uuid_data_ng_category_services].table_fields;
		var category_show = 1;
		return category_show;
	};


// /get_category_services_account
	$scope.category_with_service = function (category) {
		var category_with_service = 0;
		if ($scope.portfolio !== undefined) {
			var obj_portfolio = $scope.portfolio;
			var obj_categories = $scope.category_services;

			Object.keys(obj_portfolio).forEach(function (key) {
				if (key === category) {
					Object.keys(obj_portfolio).forEach(function (key_2) {
						//if(obj_portfolio[key_2].productId === obj_categories[key].parameter) {
						category_with_service = 1;
						//}
					});
				}
			});
		}

		return category_with_service;
	}


	//open invitation popup
	$scope.getDataInvitationPopup = function ($event, category) {
		jQuery('.preloading-category-services').attr('style', 'display: block !important');
		jQuery('.preloading-category-services .preloadingData').css('display', 'flex');

		$event.preventDefault();
		$scope.select_category = category;

		jQuery('#modal').load('/invitacion-popup/' + category + ' #container_invitation', function () {
			// agregar clase de popup a los links que tengan tipo target _popup
			jQuery('#modal .buttons > a').each(function () {
				if (jQuery(this).attr("target") === '_popup') {
					jQuery(this).addClass('popup');
				}
			});

			jQuery('#modal').dialog('open');
		});
	}

	// Show message service
	$scope.alertas_servicios_category_services = function () {
		jQuery(".block-category-services-message .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_category_services + '</p></div>');
		$html_mensaje = jQuery('.block-category-services-message .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery(".block-category-services-message .messages-only .text-alert .txt-message").remove();

		jQuery('.messages .close').on('click', function () {
			jQuery('.messages').hide();
		});
	}
}
