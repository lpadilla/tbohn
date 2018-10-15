myApp.directive('ngConsumptionFilters', ['$http', '$rootScope', ngConsumptionFilters]);

function ngConsumptionFilters($http, $rootScope) {
  return {
    restrict: 'EA',
    scope: {},
    controller: consumptionsFiltersController,
    link: linkFunc
  };

  function linkFunc(scope, el) {
    var parameters = {};
    var config_data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };

    $rootScope.$on("filters", function (event, data) {
      scope.initFilters(data.start, data.end);
    });

    scope.initFilters = function(filStart, filEnd) {
      var date_ini = new Date();
      var date_end = new Date();

      $http.get('/tbo_lines/consumption_filters?_format=json', config_data)
        .then(function (resp) {
          date_ini = resp.data.date_ini;
          date_end = resp.data.date_end;
          dates_bloqued = resp.data.dates_bloqued;
          amount_days = resp.data.amount_days;

          var ini = new Date(date_ini);
          date_ini = new Date(ini.getFullYear(), ini.getMonth(), ini.getDate() + 1);
          var end = new Date(date_end);
          date_end = new Date(end.getFullYear(), end.getMonth(), end.getDate() + 1);

          var date = date_end;
          var last = new Date(date.getTime() - (amount_days * 24 * 60 * 60 * 1000));

          date_ini_ini = new Date(last.getFullYear(), last.getMonth(), last.getDate() + 1);

          dates_to_block = [true];

          dates_bloqued.forEach(function (item, key, array) {
            var aux_date = new Date(item);
            dates_to_block.push(new Date(aux_date.getFullYear(), aux_date.getMonth(), aux_date.getDate() + 1));
          })

          jQuery("#"+filStart).pickadate({
            selectMonths: true, // Creates a dropdown to control month
            selectYears: 15,
            min: date_ini,
            max: date_end,
            format: 'yyyy-mm-dd',
            closeOnSelect: false,
            disable: dates_to_block,
          });

          jQuery('#'+filStart).pickadate('picker').set('select', date_ini_ini, {format: 'yyyy-mm-dd'});

          jQuery("#"+filEnd).pickadate({
            selectMonths: true, // Creates a dropdown to control month
            selectYears: 15,
            min: date_ini,
            max: date_end,
            format: 'yyyy-mm-dd',
            closeOnSelect: false,
            disable: dates_to_block,
          });

          jQuery('#'+filEnd).pickadate('picker').set('select', date_end, {format: 'yyyy-mm-dd'});
        }, function () {
          console.log("Error obteniendo los datos");
        }
      );
    };


    var config = drupalSettings.b2bBlock[scope['uuid_' + scope.use_directive]];
    retrieveInformation(scope, config, el);
  }

  function retrieveInformation(scope, config, el) {

  }
}

consumptionsFiltersController.$inyect = ['$scope', '$http', '$rootScope'];

function consumptionsFiltersController($scope, $http, $rootScope) {

  $rootScope.$on("filterData", function (event, data) {
    $scope.filterInfo(data.data, data.setFunction, data.startDate, data.endDate);
  });

  //data (información), setFunction (Nombre de la función encargada de setear el resultado de los filtros)
  $scope.filterInfo = function (data, setFunction, startDate, endDate) {
    var params = {
      init_date: startDate,
      end_date: endDate,
      filter_data: data
    };

    $http.get('/rest/session/token').then(function (resp) {
      $http({
        method: 'POST',
        url: '/tbo_lines/consumption_filters?_format=json',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        data: params
      }).then(function (resp) {
        if (resp.data != 'error') {
          $rootScope.$emit(setFunction, {data: resp});
        }else{
          $rootScope.$emit(setFunction, {data: 'Error en las fechas'});
        }
      }, function () {
        $rootScope.$emit(setFunction, {data: 'Error obteniendo información'});
      });
    });
  }

  //format dates for filters
  /*$scope.formatDate = function (date) {

   }*/
}
