myApp.directive('ngCheckMobileDetailsUsage', ['$http', ngCheckMobileDetailsUsage]);

function ngCheckMobileDetailsUsage($http) {

  var directive = {
    restrict: 'EA',
    controller: CheckMobileDetailsUsage,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.checkMobileDetailsUsage[scope.uuid_data_ng_check_mobile_details_usage];
    scope.msisdh = config.msisdh;
    scope.url_to_log_data_buttons = config.url_to_log_data_buttons;

    retrieveType(scope, config, el);

    var parameters = [];
    parameters['getDataFinish'] = 'yes';
    var config_data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };
    $http.get(config.url, config_data)
    .then(function (resp) {
      scope.voice_limit = resp.data.voiceLimit;
      scope.voice_actual = resp.data.voiceActual;
      scope.voice_percentage = resp.data.voicePercentage;
      scope.sms_limit = resp.data.smsLimit;
      scope.sms_actual = resp.data.smsActual;
      scope.sms_percentage = resp.data.smsPercentage;
      scope.internet_limit = resp.data.internetLimit;
      scope.internet_actual = resp.data.internetActual;
      scope.internet_percentage = resp.data.internetPercentage;
    }, function () {
      console.log("Error obteniendo los datos");
    });

    scope.enviarLogDetalles = function(tipo) {
      url = scope.url_to_log_data_buttons;
      url = url.replace('{phone_number_origin}', scope.msisdh);
      url = url.replace('{categoria}', tipo);
      $http.get(url)
          .then(function (resp) {
            console.log(resp);
          }, function () {
              console.log("Error obteniendo los datos");
          });
    }
  }

  function retrieveType(scope, config, el) {
      //Add key for this display
      var parameters_t = {};
      var config_data_t = {
        params: parameters_t,
        headers: {'Accept': 'application/json'}
      };
    $http.get('/tboapi/lines/info/line?_format=json', config_data_t)
      .then(function (resp) {
          scope.type_service = resp.data;
      }, function () {
      console.log("Error obteniendo el tipo");
    });
  }


  CheckMobileDetailsUsage.$inject = ['$scope','$http'];
  function CheckMobileDetailsUsage($scope,$http) {

  	$scope.binding = 12;

  	//para centrar los elementos dependiendo del numero de columnas
    if (jQuery('.box-center').length > 0) {
        num_elementos= jQuery('.box-center > .col').length;
        if (num_elementos ==2){
           jQuery('.box-center > div:first-child').addClass('offset-l2');
        }
        if (num_elementos ==1){
            jQuery('.box-center > div:first-child').addClass('offset-l4');
        }
    }
  }
}