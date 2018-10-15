myApp.directive('ngInvoiceDelivery', ['$http', ngInvoiceDelivery]);

function ngInvoiceDelivery($http) {
	var directive = {
		restrict: 'EA',
		controller: CompaniesListController,
		link: linkFunc
	};

	return directive;

	function linkFunc(scope, el, attr, ctrl) {
		var config = drupalSettings.b2bBlock[scope.uuid_data_ng_invoice_delivery];
		scope[config['uuid']] = config;
		scope.environment_set_up = config['environment'];
		retrieveInformation(scope, config, el);

		scope.details_model = 'si';
		scope.details_model_no = 'no';
		scope.segment = 0;
		scope.apiIsLoading = function () {
			return $http.pendingRequests.length > 0;
		};
		scope.$watch(scope.apiIsLoading, function (v) {
			if (v == false) {
				if (scope.environment_set_up == 'fijo') {
					jQuery("label[for='address']").addClass( "active" );
					jQuery("label[for='city']").addClass( "active" );
				}
				jQuery(el).parents("section").fadeIn(400);
				if (scope.companiesList.error) {
					jQuery("div.actions", el).hide();
				}
			}
		});

	}

	function retrieveInformation(scope, config, el) {
		if (scope.resources.indexOf(config.url) == -1) {
			scope.url_set_up = config.url;
			//Add key for this display
			var parameters = {};
			var config_data = {
				params: parameters,
				headers: {'Accept': 'application/json'}
			};
			$http.get(config.url, config_data)
				.then(function (resp) {
					if (resp.data.error) {
						scope.show_mesagge_data = resp.data.message;
						scope.alertas_set_up_invoice_delivery();
					} else {
						jQuery('.preloading-set-up').css('display', 'none');
						scope.suggestions = resp.data;
						if (scope.suggestions[0].show_invoice_billing == 'ambas') {
							jQuery('#digital-options-' + config['uuid']).removeClass('ng-hide');
							jQuery('#printed-options-' + config['uuid']).removeClass('ng-hide');
							jQuery('label[for=mail]').addClass('active');
							scope.digital_checked = 'digital';
							scope.printed_checked = 'impresa';
						} else if (scope.suggestions[0].show_invoice_billing == 'digital') {
							jQuery('#digital-options-' + config['uuid']).removeClass('ng-hide');
							jQuery('label[for=mail]').addClass('active');
							scope.digital_checked = 'digital';
						} else if (scope.suggestions[0].show_invoice_billing == 'impresa') {
							jQuery('#printed-options-' + config['uuid']).removeClass('ng-hide');
							scope.printed_checked = 'impresa';
						}
						scope.details = scope.suggestions[0].invoiceDetailOption;
						jQuery(el).parents("section").fadeIn('slow');
					}
        }, function () {
					console.log("Error obteniendo los datos");
				});
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

	$scope.checkedDigital = function (value) {
		$scope.digital_checked = value;

		if (value == true) {
			jQuery("#mail").attr('required',true);
		} else {
			send = undefined;
			jQuery("#mail").attr('required',false);
		}
	}

	$scope.checkedPrinter = function (value) {
		$scope.printed_checked = value;
	}

	$scope.changeDetail = function (value) {
		$scope.details = value;
	}

	$scope.updateMail = function (value) {
		$scope.mail_model = value;
	}

	$scope.reset = function ($event) {
		var config = $scope[$event.target.name];
		if ($scope.suggestions[0].show_invoice_billing == 'ambas') {
			jQuery('#digital-options-' + $scope.conf_set_up['uuid']).removeClass('ng-hide');
			jQuery('#printed-options-' + $scope.conf_set_up['uuid']).removeClass('ng-hide');
			jQuery('#impresa-' + $scope.conf_set_up['uuid']).attr("checked", "checked");
			jQuery('#digital-' + $scope.conf_set_up['uuid']).attr("checked", "checked");
			jQuery('label[for=mail]').addClass('active');
			$scope.digital_checked = 'digital';
			$scope.printed_checked = 'impresa';
		} else if ($scope.suggestions[0].show_invoice_billing == 'digital') {
			jQuery('#digital-options-' + $scope.conf_set_up['uuid']).removeClass('ng-hide');
			jQuery('label[for=mail]').addClass('active');
			jQuery('#digital-' + $scope.conf_set_up['uuid']).attr("checked", "checked");
			jQuery('#printed-options-' + $scope.conf_set_up['uuid']).addClass('ng-hide');
			$scope.digital_checked = 'digital';
			$scope.printed_checked = false;
		} else if ($scope.suggestions[0].show_invoice_billing == 'impresa') {
			jQuery('#printed-options-' + $scope.conf_set_up['uuid']).removeClass('ng-hide');
			jQuery('#impresa-' + $scope.conf_set_up['uuid']).attr("checked", "checked");
			jQuery('#digital-options-' + $scope.conf_set_up['uuid']).addClass('ng-hide');
			$scope.printed_checked = 'impresa';
			$scope.digital_checked = false;
		}

		if ($scope.suggestions[0].invoiceDetailOption == 'DETAIL') {
			jQuery('#is_detail-' + $scope.conf_set_up['uuid']).attr("checked", "checked");
		} else {
			jQuery('#is_not_detail-' + $scope.conf_set_up['uuid']).attr("checked", "checked");
		}

		document.getElementById("form-delivery-" + $scope.conf_set_up['uuid']).reset();
	}

	//Submit service fixed
	$scope.sendInvoiceDeliveryFixed = function () {
		var params = {
			'email': '',
			'datail': $scope.details,
			'contractId': $scope.suggestions[0].contractId,
			'type': '',
			'accion': '',
			'old_accion': $scope.suggestions[0].show_invoice_billing,
			'new_accion': '',
		};

		if ($scope.suggestions[0].show_invoice_billing == 'ambas') {
			params['old_accion'] = 'impresa/digital';
			if ($scope.digital_checked) {
				//validar si sigue seteada y solo se quiere actualizar
				if ($scope.mail_model != $scope.suggestions[0].email) {
					params['type'] = 'update';
					params['email'] = $scope.mail_model;
					params['accion'] = 'actualización';
					params['new_accion'] = 'impresa/digital';
				}
			} else {
				params['type'] = 'delete';
				params['accion'] = 'desactivación';
				params['new_accion'] = 'impresa';
			}
		} else if ($scope.suggestions[0].show_invoice_billing == 'impresa') {
			//Se valida si se esta generando una factura digital
			if ($scope.digital_checked && $scope.mail_model != undefined) {
				params['type'] = 'register';
				params['email'] = $scope.mail_model;
				params['accion'] = 'activación';
				params['new_accion'] = 'impresa/digital';
			}
		}

		if (params['type'] != '') {
			if ($scope.resources.indexOf($scope.url_set_up) == -1) {
				jQuery('.preloading-set-up').css('display', 'block');
				$http.get('/rest/session/token').then(function (resp) {
					$http({
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'Accept': 'application/json',
							'X-CSRF-Token': resp.data
						},
						data: params,
						url: $scope.url_set_up
					}).then(function successCallback(response) {
						window.location.reload(true);
						//jQuery(el).parents("section").fadeIn('slow');
					}, function errorCallback(response) {
						window.location.reload(true);
					});
				});
			}
		} else {
			jQuery('.modal-close').trigger('click');
		}

	}

	$scope.sendInvoiceDeliveryMobile = function () {
		var params = {
			"invoiceDeliveryOption": "",
			"invoiceDetailOption": $scope.details,
			"contractId": $scope.suggestions[0].contractId,
			'accion': '',
			'old_accion': $scope.suggestions[0].show_invoice_billing,
			'new_accion': ''
		};

		var validate = false;

		if ($scope.suggestions[0].show_invoice_billing == 'ambas') {
			params['old_accion'] = 'impresa/digital';
			if (!$scope.digital_checked && $scope.printed_checked) {
				//tomar el detalle y setear como both
				params['invoiceDeliveryOption'] = 'PRINTED';
				params['accion'] = 'desactivación';
				params['new_accion'] = 'impresa';
			} else if ($scope.digital_checked && !$scope.printed_checked) {
				params['invoiceDeliveryOption'] = 'ELECTRONIC';
				params['accion'] = 'desactivación';
				params['new_accion'] = 'digital';
			} else if (($scope.digital_checked && $scope.printed_checked) && $scope.details != $scope.suggestions[0].invoiceDetailOption) {
				params['invoiceDeliveryOption'] = 'BOTH';
				params['accion'] = 'activación';
				params['new_accion'] = 'impresa/digital';
			}
		} else if ($scope.suggestions[0].show_invoice_billing == 'impresa') {
			if ($scope.digital_checked && $scope.printed_checked) {
				//tomar el detalle y setear como both
				params['invoiceDeliveryOption'] = 'BOTH';
				params['accion'] = 'activación';
				params['new_accion'] = 'impresa/digital';
			} else if ($scope.digital_checked && !$scope.printed_checked) {
				params['invoiceDeliveryOption'] = 'ELECTRONIC';
				params['accion'] = 'activacion';
				params['new_accion'] = 'digital';
			} else if ($scope.printed_checked && $scope.details != $scope.suggestions[0].invoiceDetailOption) {
				params['invoiceDeliveryOption'] = 'PRINTED';
				params['accion'] = 'desactivación';
				params['new_accion'] = 'impresa';
			}
		} else if ($scope.suggestions[0].show_invoice_billing == 'digital') {
			if ($scope.digital_checked && $scope.printed_checked) {
				//tomar el detalle y setear como both
				params['invoiceDeliveryOption'] = 'BOTH';
				params['accion'] = 'activación';
				params['new_accion'] = 'impresa/digital';
			} else if (!$scope.digital_checked && $scope.printed_checked) {
				params['invoiceDeliveryOption'] = 'PRINTED';
				params['accion'] = 'desactivación';
				params['new_accion'] = 'impresa';
			} else if ($scope.digital_checked && $scope.details != $scope.suggestions[0].invoiceDetailOption) {
				params['invoiceDeliveryOption'] = 'ELECTRONIC';
				params['accion'] = 'activación';
				params['new_accion'] = 'digital';
			}
		}

		if (!$scope.digital_checked && !$scope.printed_checked) {
			validate = true;
		}

		if (validate) {
			drupal_set_message(Drupal.t("Debe seleccionar algun tipo de factura"), "error", $scope.uuid_data_ng_invoice_delivery);
			window.location.reload(true);
		} else {

      if (params['invoiceDeliveryOption'] != '' || ($scope.details != $scope.suggestions[0].invoiceDetailOption)) {

        if ($scope.details != $scope.suggestions[0].invoiceDetailOption) {
          params['invoiceDetailOption'] = $scope.details;
          params['invoiceDetailOptionChange'] = true;
				}

				if ($scope.resources.indexOf($scope.url_set_up) == -1) {
					jQuery('.preloading-set-up').css('display', 'block');
					$http.get('/rest/session/token').then(function (resp) {
						$http({
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
								'Accept': 'application/json',
								'X-CSRF-Token': resp.data
							},
							data: params,
							url: $scope.url_set_up
						}).then(function successCallback(response) {
							window.location.reload(true);
							//jQuery(el).parents("section").fadeIn('slow');
						}, function errorCallback(response) {
							window.location.reload(true);
						});
					});
				}
			} else {
        $timeout(function() {
          angular.element('#close-set-up-invoice').triggerHandler('click');
        });
			}
		}
	}

	//Show message exception
	$scope.alertas_set_up_invoice_delivery = function () {
		jQuery(".set-up-invoice-delivery .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
		$html_mensaje = jQuery('.set-up-invoice-delivery .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery(".set-up-invoice-delivery .messages-only .text-alert .txt-message").remove();

		jQuery('.messages .close').on('click', function() {
			jQuery('.messages').hide();
		});
	}
}
