myApp.directive('ngFixedConsumptionData', ['$http', ngFixedConsumptionData]);

function ngFixedConsumptionData($http) {

  var directive = {
    restrict: 'EA',
    controller: FixedConsumptionDataController,
    link: linkFunc
  }

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.FixedConsumptionDataBlock[scope.uuid_data_ng_fixed_consumption_data];
    retrieveInformation(scope, config, el);
  }

  function retrieveType(scope, config, el) {
  }
  function retrieveInformation(scope, config, el) {
    parameters = {};
    parameters['type'] = 'DateMinutes';
    var config_data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };
    $http.get(config.url, config_data)
      .then(function (resp) {

        scope.array = resp.data["values"];

      }, function () {
        console.log("Error obteniendo los datos");
        jQuery(".block-consumption-detail-data .messages-only .text-alert").append('<div class="txt-message"><p>' + Drupal.t('En este momento no podemos obtener la información de tus servicios moviles, por favor intenta de nuevo') + '</p></div>');
        $html_mensaje = jQuery('.block-consumption-detail-data .messages-only').html();
        jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

        jQuery(".block-consumption-detail-data .messages-only .text-alert .txt-message").remove();

        jQuery('.messages .close').on('click', function () {
          jQuery('.messages').hide();
        });
      });
  }
}
  FixedConsumptionDataController.$inject = ['$scope', '$http'];
    function FixedConsumptionDataController($scope, $http) {
        $scope.log = function (month) {
          $http.get('/rest/session/token').then(function (resp) {
            $http({
              method: 'POST',
              url: '/tboapi/lines/detail-consumption/fixed?_format=json',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': resp.data
              },
              data: {'month' : month, type: 'month'},
            }).then(function (response) {

            }, function () {
              console.log('error');
            });
          });
          }
        }
