myApp.directive('ngChangeSimCard', ['$http', ngChangeSimCard]);

function ngChangeSimCard($http) {
	return {
		restrict: 'EA',
		controller: changeSimCardContrller,
		link: linkFunc
	}

	function linkFunc(scope, el) {
		scope.state_button = 1;
		scope.help_text = 'No debe contener menos de 15 carácteres';
	}
}

myApp.directive('regexNumber', function () {
	"use strict";
	return {
		restrict: 'A',
		scope: {},
		replace: false,
		link: function (scope, el, attrs, ctrl) {

			el.on("input change paste",	function (event) {
				var formControl;
				formControl = jQuery(event.target);
				formControl.val(formControl.val().slice(0,15));
			});

		}
	}
});

changeSimCardContrller.$inyect = ['$scope', '$http', '$timeout'];

function changeSimCardContrller($scope, $http, $timeout) {

	$scope.change_sim = function (num) {

		var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_change_sim_card];
		$http({
			method: 'GET',
			url: config.url,
			params: {new_sim: num, change: 1}
		}).then(function (resp) {
			if (resp.data.response.status == 200) {
				$scope.class_modal = 'alert-success';
			} else if (resp.data.response.code != 200 || resp.data.response.status != 200) {
				$scope.class_modal = 'alert-danger';
			}

			if (resp.data !== undefined && resp.data != '') {
				//Set pop-up data
				$scope.state_modal = resp.data.state_modal;

				$scope.document = {
					label: resp.data.enterpriseDoc,
					data: resp.data.enterpriseNumber
				};

				$scope.user = {
					label: config.pop_fields.user.label,
					data: resp.data.userName
				};

				$scope.line_number = {
					label: config.pop_fields.line_number.label,
					data: resp.data.phone
				};

				if ($scope.line_number.data !== undefined || $scope.line_number.data != '') {
					$scope.line_number.data = $scope.line_number.data.trim();
					$scope.line_number.data = "(" + $scope.line_number.data.substring(0, 3) + ") " + $scope.line_number.data.substring(3, 6) + "-" + $scope.line_number.data.substring(6, 10);
				}

				$scope.enterprise = {
					label: config.pop_fields.enterprise.label,
					data: resp.data.enterpriseName
				};

				$scope.detail = {
					label: config.pop_fields.detail.label,
					data: resp.data.detail
				};

				$scope.date_change = {
					label: config.pop_fields.date_change.label,
					data: resp.data.date
				};

				$scope.hour = {
					label: config.pop_fields.hour.label,
					data: resp.data.hour
				};

				$scope.description = {
					label: config.pop_fields.description.label,
					data: resp.data.description
				};
				angular.element('.modal').modal();
				angular.element('#modal-change-sim').modal('open');
			}

		}, function (resp) {
			console.log('Error obteniendo información');
		});

	};

	/**
	 * Validate new length of new number, set help message and enable/disable accept button
	 */
	$scope.validate_sim = function (num) {

		if (num) {
			num = num.toString();

			if (/^[0-9]+$/.test(num) === false) {
				jQuery('#new_sim').val(num.replace(/\D/g, ''));
			}

			if (num.length < 15) {
				$scope.help_text = 'Minimo 15 caracteres';
				jQuery('#new_sim').addClass('error').removeClass('valid');
				jQuery('#status_sim').addClass('error').removeClass('valid');
				if ($scope.state_button == 0) {
					$scope.state_button = 1;
				}
			} else if (num.length == 15) {

				$scope.help_text = undefined;
				jQuery('#new_sim').addClass('valid').removeClass('error');
				jQuery('#status_sim').addClass('valid').removeClass('error');
				if ($scope.confirm_change == 1) {
					$scope.state_button = 0;
				}
			}
		} else {
			$scope.help_text = 'No debe contener menos de 15 carácteres';
			jQuery('#new_sim').removeClass('valid error');
			jQuery('#status_sim').removeClass('valid error');

			if ($scope.state_button == 0) {
				$scope.state_button = 1;
			}
		}
	};

	/**
	 * Validate checkbox and enable/disable accept button
	 */
	$scope.val_confirm = function (check) {
		if (check == 1) {
			$scope.validate_sim($scope.new_sim);
		} else {
			$scope.state_button = 1;
		}
	};

	/**
	 * Reset fields
	 */
	$scope.clearFieldsSim = function () {
		$scope.new_sim = null;
		$scope.confirm_change = 0;
		$scope.state_button = 1;
		$scope.validate_sim($scope.new_sim);

		jQuery('#new_sim').val('');
	};
}
