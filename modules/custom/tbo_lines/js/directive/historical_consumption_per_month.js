myApp.directive('ngHistoricalConsumptionPerMonth', ['$http', ngHistoricalConsumptionPerMonth]);

function ngHistoricalConsumptionPerMonth($http) {
  var directive = {
    restrict: 'EA',
    controller: HistoricalConsumptionPerMonthController,
    link: linkFunc
  }

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.historicalConsumptionPerMonthBlock[scope.uuid_data_ng_historical_consumption_per_month];
    scope.orderMonth = 1;
    scope.orderMonthM = 1;
    scope.monthFileFormat = config.format;
    scope.monthFixed = config.month;
    retrieveInformation(scope, config, el);
  }


  function retrieveInformation(scope, config, el) {

    //Add key for this display
    var parameters = {};
    parameters['type'] = 'month';
    parameters['month'] = config.month;
    var config_data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };
    $http.get(config.url, config_data)
      .then(function (resp) {

        scope.consumptionMonth = resp.data['desktop'];
        scope.consumptionMonthMobile = resp.data['mobile'];
        scope.data_download_month = resp.data['data_download'];
        var data_month_length = 0;

        if (typeof scope.consumptionMonth !== 'undefined' && scope.consumptionMonth != null) {
          data_month_length = scope.consumptionMonth.length;
        }

        aux_rows = config.paginate;
        $max_rows = parseInt(aux_rows);
        var gap = 1;
        gap += Math.floor(data_month_length / $max_rows);
        scope.gapMonth = gap;
        scope.gapMonthM = gap;
        scope.itemsPerPageMonth = $max_rows;
        scope.itemsPerPageMonthM = $max_rows;
        scope.groupToPagesMonth();
        scope.groupToPagesMonthM();
        scope.downLoadReportMonth();
      }, function () {
        if (scope.type_service != 'POS' && scope.environment == 'movil') {
          jQuery(".block-historical-consumption-per-month .messages-only .text-alert").append('<div class="txt-message"><p>' + Drupal.t('En este momento no podemos obtener la información de tus servicios moviles, por favor intenta de nuevo') + '</p></div>');
          $html_mensaje = jQuery('.block-historical-consumption-per-month .messages-only').html();
          jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

          jQuery(".block-historical-consumption-per-month .messages-only .text-alert .txt-message").remove();

          jQuery('.messages .close').on('click', function () {
            jQuery('.messages').hide();
          });
        }
        /* jQuery(".block-historical-consumption-per-month .messages-only .text-alert").append('<div class="txt-message"><p>' + Drupal.t('En este momento no podemos obtener la información de tus servicios moviles, por favor intenta de nuevo') + '</p></div>');
         $html_mensaje = jQuery('.block-historical-consumption-per-month .messages-only ').html();
         jQuery('.main-top').append('<div class="messages messages--success alert alert-pending">' + $html_mensaje + '</div>');*/
        console.log("Error obteniendo los datos");
      });
  }

}

HistoricalConsumptionPerMonthController.$inject = ['$scope', '$http'];

function HistoricalConsumptionPerMonthController($scope, $http) {

  $scope.groupedItemsMonth = [];
  $scope.pagedItemsMonth = [];
  $scope.currentPageMonth = 0;
  $scope.groupedItemsMonthMobile = [];
  $scope.pagedItemsMonthMobile = [];
  $scope.currentPageMonthMobile = 0;
  $scope.orderIcon = 'Desc';
  $scope.elementOrder = '.date';

  // calculate page in place
  $scope.groupToPagesMonth = function () {
    $scope.pagedItemsMonth = [];
    if (typeof $scope.consumptionMonth !== 'undefined' && $scope.consumptionMonth != null) {
      for (var i = 0; i < $scope.consumptionMonth.length; i++) {
        if (i % $scope.itemsPerPageMonth === 0) {
          $scope.pagedItemsMonth[Math.floor(i / $scope.itemsPerPageMonth)] = [$scope.consumptionMonth[i]];
        } else {
          $scope.pagedItemsMonth[Math.floor(i / $scope.itemsPerPageMonth)].push($scope.consumptionMonth[i]);
        }
      }
      //console.log('hola 0');
        $scope.orderIcontable();
    }
  };

  $scope.rangeMonth = function (size, start, end) {
    var ret = [];
    if (size < end) {
      end = size;
    }
    for (var i = 0; i < end; i++) {
      ret.push(i);
    }
    return ret;
  };

  $scope.prevPageMonth = function () {
    if ($scope.currentPageMonth > 0) {
      $scope.currentPageMonth--;
    }
  };

  $scope.nextPageMonth = function () {
    if ($scope.currentPageMonth < $scope.pagedItemsMonth.length - 1) {
      $scope.currentPageMonth++;
    }
  };

  $scope.setPageMonth = function () {
    $scope.currentPageMonth = this.n;
  };

  $scope.orderResultsMonth = function (parameter, key) {
    if($scope.orderMonth == 1){
      aux = $scope.consumptionMonth.sort(function (a, b) {
        return a.timestamp - b.timestamp
      });
      $scope.orderMonth = 0;
      $scope.consumptionMonth = aux;
      //console.log('hola1-desktop');
      $scope.orderIcon ='Asc';
      $scope.orderIcontable();
    }else{
      aux = $scope.consumptionMonth.sort(function (a, b) {
        return b.timestamp - a.timestamp
      });
      $scope.orderMonth = 1;
      $scope.consumptionMonth = aux;
      //console.log('hola1-desktop');
      $scope.orderIcon ='Desc';
      $scope.orderIcontable();
    }
    $scope.groupToPagesMonth();

  }

  $scope.orderResultsMonthM = function (parameter, key) {
    if($scope.orderMonthM == 1){
      aux = $scope.consumptionMonthMobile.sort(function (a, b) {
          return a.timestamp - b.timestamp
      });
      $scope.orderMonthM = 0;
      $scope.consumptionMonthMobile = aux;
     // console.log('hola1-mobile');
      $scope.orderIcon ='Asc';
      $scope.orderIcontable();
    }else{
      aux = $scope.consumptionMonthMobile.sort(function (a, b) {
          return b.timestamp - a.timestamp
      });
      $scope.orderMonthM = 1;
      $scope.consumptionMonthMobile = aux;
     // console.log('hola2-mobile');
      $scope.orderIcon ='Desc';
      $scope.orderIcontable();
    }
    $scope.groupToPagesMonthM();

  }

  $scope.downLoadReportMonth = function (format) {
    $http.get('/rest/session/token').then(function (resp) {
      $http({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        data: {type_file : $scope.monthFileFormat, type_download : 'month', data : $scope.data_download_month},
        url: '/tboapi/lines/download-consumption/fixed?_format=json'
      }).then(function successCallback(response) {
        console.log(response.data);
        // $scope.file = response.data;
        $scope.fileMonth = '/adf_core/download-example/' + response.data.file_name + '/NULL', '_blank';
      }, function errorCallback(response) {
        // called asynchronously if an error occurs
        // or server returns response with an error status.
        console.log('error obteniendo el servicio metodo post');
      });
    });
  }

  // calculate page in place
  $scope.groupToPagesMonthM = function () {
    $scope.pagedItemsMonthMobile = [];
    if(typeof $scope.consumptionMonthMobile !== 'undefined' && $scope.consumptionMonthMobile != null){
      for (var i = 0; i < $scope.consumptionMonthMobile.length; i++) {
        if (i % $scope.itemsPerPageMonthM === 0) {
          $scope.pagedItemsMonthMobile[Math.floor(i / $scope.itemsPerPageMonthM)] = [$scope.consumptionMonthMobile[i]];
        } else {
          $scope.pagedItemsMonthMobile[Math.floor(i / $scope.itemsPerPageMonthM)].push($scope.consumptionMonthMobile[i]);
        }
      }
    }
  };

  $scope.rangeMonthM = function (size, start, end) {
    var ret = [];
    if (size < end) {
      end = size;
    }
    for (var i = 0; i < end; i++) {
      ret.push(i);
    }
    return ret;
  };

  $scope.prevPageMonthM = function () {
    if ($scope.currentPageMonthMobile > 0) {
      $scope.currentPageMonthMobile--;
    }
  };

  $scope.nextPageMonthM = function () {
    if ($scope.currentPageMonthMobile < $scope.pagedItemsMonthMobile.length - 1) {
      $scope.currentPageMonthMobile++;
    }
  };

  $scope.setPageMonthM = function () {
    $scope.currentPageMonthMobile = this.n;
  };

  $scope.downloadFunctionMonth = function () {
    $http.get('/rest/session/token').then(function (resp) {
      $http({
        method: 'POST',
        url: '/tboapi/lines/detail-consumption/fixed?_format=json',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        data: { month : $scope.monthFixed, type : 'month_c', format : $scope.monthFileFormat },
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
