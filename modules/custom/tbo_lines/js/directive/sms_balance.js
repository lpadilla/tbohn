myApp.directive('ngSmsBalance', ['$http', ngSmsBalance]);

function ngSmsBalance($http) {

	return {
		restrict: 'EA',
		controller: smsBalanceController,
		link: linkFunc
	};

	function linkFunc(scope, el) {
		var config = drupalSettings.b2bBlock[scope.uuid_data_ng_sms_balance];
		retrieveInformation(scope, config, el);
	}

	function retrieveInformation(scope, config) {

		var parameters = {
      params: {
      	val: 1
      }
		};

		$http.get(config.url,parameters).then(function(resp) {
			scope.environment = resp.data.environment;
      if(resp.data.environment != 'fijo') {
      	$http.get(config.url).then(function(resp) {
          if (resp.data.error) {
            scope.show_mesagge_data = resp.data.message;
            scope.alertas_servicios_sms_balance();

            scope.sms_tigo = 'No disponible';
            scope.sms_destiantion = 'No disponible';
            scope.sms_operator = 'No disponible';
          } else {
            scope.sms_tigo = resp.data.smsTigo;
            scope.sms_destiantion = resp.data.smsDestiantion;
            scope.sms_operator = resp.data.smsOperator;
          }
				}, function() {
          console.log('Error obteniendo la información');
				});
			}
		});

	}
}

smsBalanceController.$inyect = ['$http','$scope'];

function smsBalanceController($http, $scope) {
	//Show message service
	$scope.alertas_servicios_sms_balance = function () {
		jQuery(".block--sms-balance .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
		$html_mensaje = jQuery('.block--sms-balance .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery(".block--sms-balance .messages-only .text-alert .txt-message").remove();

		jQuery('.messages .close').on('click', function() {
			jQuery('.messages').hide();
		});
	}
}
