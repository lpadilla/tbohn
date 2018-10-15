myApp.directive('ngFixedConsumptionDaily', ['$http', ngFixedConsumptionDaily]);

function ngFixedConsumptionDaily($http) {

  var directive = {
    restrict: 'EA',
    controller: FixedConsumptionDailyController,
    link: linkFunc
  }

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.fixedConsumptionDailyBlock[scope.uuid_data_ng_fixed_consumption_daily];
    scope.file_format = config['file_format'];
    scope.parameterOpcional = config.parameterOpcional;
    scope.month = config['month'];
    scope.order = 1;
    scope.orderM = 1;
    scope.fileDaily = '';
    retrieveInformation(scope, config, el);
  }

  function retrieveType(scope, config, el) {
  }

  function retrieveInformation(scope, config, el) {
    parameters = {};
    parameters['type'] = 'daily';
    parameters['month'] = config.timestamp;
    var config_data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };
    $http.get(config.url, config_data)
      .then(function (resp) {
        scope.all_data = resp.data['desktop'];
        scope.all_data_m = resp.data['mobile'];
        scope.dataDaily = scope.all_data['local_minutes'];
        scope.dataDailyM = scope.all_data_m['local_minutes'];
        scope.dateMobileTitle = resp.data['date_mobile'];
        scope.data_download = resp.data['data_download'];
        aux_rows = config.paginate;
        $max_rows = parseInt(aux_rows);
        scope.itemsPerPageDaily = $max_rows;
        scope.itemsPerPageDailyM = $max_rows;
        var gap = 1;
        var daily_length = 0;

        if (typeof scope.dataDaily !== 'undefined') {
          daily_length = scope.dataDaily.length;
        }

        gap += Math.floor(daily_length / $max_rows);
        scope.gapDaily = gap;
        scope.gapDailyM = gap;
        scope.groupToPagesDaily();
        scope.groupToPagesDailyM();
        scope.downloadFunctionDaily();
      }, function () {
        console.log("Error obteniendo los datos");
      });
  }
}

FixedConsumptionDailyController.$inject = ['$scope', '$http'];

function FixedConsumptionDailyController($scope, $http, $rootScope) {
  $scope.groupedItemsDaily = [];
  $scope.pagedItemsDaily = [];
  $scope.currentPageDaily = 0;
  $scope.groupedItemsDailyMobile = [];
  $scope.pagedItemsDailyMobile = [];
  $scope.currentPageDailyMobile = 0;
  $scope.orderIcon = 'Desc';
  $scope.elementOrder = '.hour';


  // calcular paginas desktop
  $scope.groupToPagesDaily = function () {
    $scope.pagedItemsDaily = [];
    if (typeof $scope.dataDaily !== 'undefined') {
      for (var i = 0; i < $scope.dataDaily.length; i++) {
        if (i % $scope.itemsPerPageDaily === 0) {
          $scope.pagedItemsDaily[Math.floor(i / $scope.itemsPerPageDaily)] = [$scope.dataDaily[i]];
        } else {
          $scope.pagedItemsDaily[Math.floor(i / $scope.itemsPerPageDaily)].push($scope.dataDaily[i]);
        }
      }
    }
  };

  $scope.rangeDaily = function (size, start, end) {
    var ret = [];
    if (size < end) {
      end = size;
    }
    for (var i = 0; i < end; i++) {
      ret.push(i);
    }
    return ret;
  };

  $scope.prevPageDaily = function () {
    if ($scope.currentPageDaily > 0) {
      $scope.currentPageDaily--;
    }
  };

  $scope.nextPageDaily = function () {
    if ($scope.currentPageDaily < $scope.pagedItemsDaily.length - 1) {
      $scope.currentPageDaily++;
    }
  };

  $scope.setPageDaily = function () {
    $scope.currentPageDaily = this.n;
  };

  // calculate page in place
  $scope.groupToPagesDailyM = function () {
    $scope.pagedItemsDailyMobile = [];
    if (typeof $scope.dataDailyM !== 'undefined') {
      //console.log('hola 0');
      for (var i = 0; i < $scope.dataDailyM.length; i++) {
        if (i % $scope.itemsPerPageDailyM === 0) {
          $scope.pagedItemsDailyMobile[Math.floor(i / $scope.itemsPerPageDailyM)] = [$scope.dataDailyM[i]];
        } else {
          $scope.pagedItemsDailyMobile[Math.floor(i / $scope.itemsPerPageDailyM)].push($scope.dataDailyM[i]);
        }
      }
      $scope.orderIcontable();

    }
  };

  $scope.rangeDailyM = function (size, start, end) {
    var ret = [];
    if (size < end) {
      end = size;
    }
    for (var i = 0; i < end; i++) {
      ret.push(i);
    }
    return ret;
  };

  $scope.prevPageDailyM = function () {
    if ($scope.currentPageDailyMobile > 0) {
      $scope.currentPageDailyMobile--;
    }
  };

  $scope.nextPageDailyM = function () {
    if ($scope.currentPageDailyMobile < $scope.pagedItemsDailyMobile.length - 1) {
      $scope.currentPageDailyMobile++;
    }
  };

  $scope.setPageDailyM = function () {
    $scope.currentPageDailyMobile = this.n;
  };

  //ordenamiento en versión desktop
  $scope.orderResultsDaily = function (key, index) {

    if ($scope.order == 1) {
      aux = $scope.dataDaily.sort(function (a, b) {
        return a.timestamp - b.timestamp
      });
      $scope.order = 0;
      $scope.dataDaily = aux;
      $scope.orderIcon ='Asc';
      $scope.orderIcontable();

    } else {
      aux = $scope.dataDaily.sort(function (a, b) {
        return b.timestamp - a.timestamp
      });
      $scope.order = 1;
      $scope.dataDaily = aux;
      $scope.orderIcon ='Desc';
      $scope.orderIcontable();
    }

    $scope.groupToPagesDaily();
  }

  //ordenamiento en versión mobile
  $scope.orderResultsDailyM = function (key, index) {

    if ($scope.orderM == 1) {
      aux = $scope.dataDailyM.sort(function (a, b) {
        return a.timestamp - b.timestamp
      });
      $scope.orderM = 0;
      $scope.dataDaily = aux;
      $scope.orderIcon ='Asc';
      $scope.orderIcontable();
    } else {
      aux = $scope.dataDailyM.sort(function (a, b) {
        return b.timestamp - a.timestamp
      });
      $scope.orderM = 1;
      $scope.dataDaily = aux;
      $scope.orderIcon ='Desc';
      $scope.orderIcontable();
    }

    $scope.groupToPagesDailyM();
  }

  $scope.changeResource = function () {
    $scope.dataDaily = $scope.all_data[$scope.minutes_type];
    $scope.dataDailyM = $scope.all_data_m[$scope.minutes_type];
    $scope.groupToPagesDaily();
    $scope.groupToPagesDailyM();
  }

  $scope.downloadFunctionDaily = function () {
    var file = [];

    $http.get('/rest/session/token').then(function (resp) {
      $http({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        data: {type_file : $scope.file_format, type_download : 'daily', data : $scope.data_download, month : $scope.month},
        url: '/tboapi/lines/download-consumption/fixed?_format=json'
      }).then(function successCallback(response) {
        //console.log(response.data);
        // $scope.file = response.data;
        $scope.fileDaily = '/adf_core/download-example/' + response.data.file_name + '/NULL', '_blank';
      }, function errorCallback(response) {
        // called asynchronously if an error occurs
        // or server returns response with an error status.
        console.log('error obteniendo el servicio metodo post');
      });
    });
  }

  $scope.logDailyDownload = function () {
    $http.get('/rest/session/token').then(function (resp) {
      $http({
        method: 'POST',
        url: '/tboapi/lines/detail-consumption/fixed?_format=json',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        data: { month : $scope.dateMobileTitle, type : 'daily', format : $scope.file_format },
      }).then(function (response) {

      }, function () {
        console.log('error');
      });
    });
  }

  $scope.orderIcontable = function() {
    $el = $scope.elementOrder;
    if($scope.orderIcon=='Desc'){
      jQuery($el).removeClass('icon-arrow-up');
      jQuery($el).addClass('icon-arrow-down');
    }else{
      jQuery($el).removeClass('icon-arrow-down');
      jQuery($el).addClass('icon-arrow-up');
    }
    
  }
}
