myApp.directive('ngPaymentDomiciliation', ['$http', ngPaymentDomiciliation]);


function ngPaymentDomiciliation($http) {
  var directive = {
    restrict: 'EA',
    controller: PaymentDomiciliationController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    var config = drupalSettings.paymentDomiciliationBlock[scope.uuid_data_ng_payment_domiciliation];
    scope.enviroment = config['type'];
    retrieveInformation(scope, config, el);
    scope.apiIsLoading = function() {
      return $http.pendingRequests.length > 0;
    };

    scope.$watch(scope.apiIsLoading, function(v) {
      if (v == false) {
        if(scope.payment_domiciliation.error){
          jQuery("div.actions", el).hide();
        }else{
          jQuery(el).parents("section").fadeIn(400);
        }
      }
    });

    scope.openM = 1;

    scope.openModalConfirmation = function () {
      if (scope.openM == 1) {
        document.getElementById('open-modal-confirmation').click();
        scope.openM = 0;
      }
    }

		scope.index = '';

		scope.setIndex = function (number, name, card) {
		  //Set values for delete
			scope.index = number;
			scope.name = name;
			scope.card = card;
		}

		scope.deleteCard = function () {
			if (scope.resources.indexOf(config.url) == -1) {
				var parameters = {};
				parameters['type'] = 'deleteCard';
				parameters['number'] = scope.index;
				parameters['name'] = scope.name;
				parameters['card'] = scope.card;
				var config_data = {
					params: parameters,
					headers: {'Accept': 'application/json'}
				};

				//Show preloading
				jQuery('.preloading-set-up').css('display', 'block');
				$http.get(config.url, config_data)
					.then(function (response) {
						window.location.reload();
						jQuery(el).parents("section").fadeIn('slow');
					}, function () {
						console.log("Error obteniendo los datos borrado");
					});
			}
		}
  }

  function retrieveInformation(scope, config, el) {
    if (scope.resources.indexOf(config.url) == -1) {
      //Add key for this display
      var parameters = {};
      parameters['contractId'] = config.contractId;
      parameters['type'] = config.type;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      $http.get(config.url, config_data)
        .then(function (resp) {
					if (resp.data.error) {
						scope.show_mesagge_data = resp.data.message;
						scope.alertas_payment_domiciliation();
					} else {
						scope.payment_domiciliation = resp.data;
					}
        }, function () {
          console.log("Error obteniendo los datos");
        });
    }
  }
}

PaymentDomiciliationController.$inject = ['$scope'];

function PaymentDomiciliationController($scope) {

  // Init vars
  if(  typeof $scope.payment_domiciliation == 'undefined'){
    $scope.payment_domiciliation = [];
  }
  if(  typeof $scope.payment_domiciliation[$scope.uuid_data_ng_payment_domiciliation] == 'undefined'){
    $scope.payment_domiciliation[$scope.uuid_data_ng_payment_domiciliation] = [];
  }
  if (typeof $scope.resources == 'undefined') {
    $scope.resources = [];
  }

  jQuery( ".close-manage-cards-delete" ).click(function() {
		jQuery('.close-manage-cards').trigger('click');
	});

	//Show message service
	$scope.alertas_payment_domiciliation = function () {
		jQuery(".block-payment-domiciliation .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
		$html_mensaje = jQuery('.block-payment-domiciliation .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery('.messages .close').on('click', function() {
			jQuery('.messages').hide();
		});
	}

}

myApp.filter('to_trusted', ['$sce', function($scope) {
  return function(text) {
    return $scope.trustAsHtml(text);
  };
}]);