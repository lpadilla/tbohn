myApp.directive('ngConsumptionDetailData', ['$http', '$rootScope', ngConsumptionDetailData]);

function ngConsumptionDetailData($http, $rootScope) {

  var directive = {
    restrict: 'EA',
    controller: ConsumptionDetailDataController,
    link: linkFunc
  }

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.consumptionDetailDataBlock[scope.uuid_data_ng_consumption_detail_data];
    scope.environment = config.environment;
    $rootScope.$emit('filters', {
      start: 'start_date_data',
      end: 'end_date_data'
    });
    retrieveType(scope, config, el);
    retrieveInformation(scope, config, el);
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

  function retrieveInformation(scope, config, el) {

    //Add key for this display
    var parameters = {};
    for (i = 0; i < config.table.length; i++) {
      parameters[config.table[i]] = i;
    }
    parameters['paginate'] = config.paginate;
    var config_data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };
    $http.get(config.url, config_data)
      .then(function (resp) {
        scope.consumption = resp.data[0];
        scope.consumption_complete = resp.data[0];
        scope.data_mobile = resp.data[1];
        scope.data_mobile_complete = resp.data[1];
        scope.submitFilters();
        scope.format = config.format;
        scope.downLoadReport(scope.format);
        aux_rows = config.paginate;
        $max_rows = parseInt(aux_rows);
        scope.itemsPerPageData = $max_rows;
        scope.itemsPerPageDataM = $max_rows;
        var gap = 1;
        gap += Math.floor(scope.consumption.length / $max_rows);
        scope.gapData = gap;
        scope.gapDataM = gap;
        scope.groupToPagesData();
        scope.groupToPagesDataM();
      }, function () {
        if (scope.type_service != 'POS' && scope.environment == 'movil') {
          jQuery(".block-consumption-detail-data .messages-only .text-alert").append('<div class="txt-message"><p>' + Drupal.t('En este momento no podemos obtener la información de tus servicios moviles, por favor intenta de nuevo') + '</p></div>');
          $html_mensaje = jQuery('.block-consumption-detail-data .messages-only').html();
          jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

          jQuery(".block-consumption-detail-data .messages-only .text-alert .txt-message").remove();

          jQuery('.messages .close').on('click', function () {
            jQuery('.messages').hide();
          });
        }
       /* jQuery(".block-consumption-detail-data .messages-only .text-alert").append('<div class="txt-message"><p>' + Drupal.t('En este momento no podemos obtener la información de tus servicios moviles, por favor intenta de nuevo') + '</p></div>');
        $html_mensaje = jQuery('.block-consumption-detail-data .messages-only ').html();
        jQuery('.main-top').append('<div class="messages messages--success alert alert-pending">' + $html_mensaje + '</div>');*/
        console.log("Error obteniendo los datos");
      });
  }

}

ConsumptionDetailDataController.$inject = ['$scope', '$http', '$rootScope'];

function ConsumptionDetailDataController($scope, $http, $rootScope) {

  $scope.groupedItemsData = [];
  $scope.pagedItemsData = [];
  $scope.currentPageData = 0;
  $scope.groupedItemsDataMobile = [];
  $scope.pagedItemsDataMobile = [];
  $scope.currentPageDataMobile = 0;

  // calculate page in place
  $scope.groupToPagesData = function () {
    $scope.pagedItemsData = [];
    for (var i = 0; i < $scope.consumption.length; i++) {
      if (i % $scope.itemsPerPageData === 0) {
        $scope.pagedItemsData[Math.floor(i / $scope.itemsPerPageData)] = [$scope.consumption[i]];
      } else {
        $scope.pagedItemsData[Math.floor(i / $scope.itemsPerPageData)].push($scope.consumption[i]);
      }
    }
  };

  $scope.rangeData = function (size, start, end) {
    var ret = [];
    if (size < end) {
      end = size;
    }
    for (var i = 0; i < end; i++) {
      ret.push(i);
    }
    return ret;
  };

  $scope.prevPageData = function () {
    if ($scope.currentPageData > 0) {
      $scope.currentPageData--;
    }
  };

  $scope.nextPageData = function () {
    if ($scope.currentPageData < $scope.pagedItemsData.length - 1) {
      $scope.currentPageData++;
    }
  };

  $scope.setPageData = function () {
    $scope.currentPageData = this.n;
  };

  var status_date = 0;
  var status_hour = 0;
  var status_date_m = 0;

  $scope.orderResultsData = function (parameter, key) {
    aux = [];

    switch (parameter) {
      case 'date':
        if (status_date == 0) {
          aux = $scope.consumption.sort(function (a, b) {
            status_date = 1;
            return new Date(a.date) - new Date(b.date)
          });
        } else {
          aux = $scope.consumption.sort(function (a, b) {
            status_date = 0;
            return new Date(b.date) - new Date(a.date)
          });
        }
        break;

      case 'hour':
        if (status_date == 0) {
          aux = $scope.consumption.sort(function (a, b) {
            status_date = 1;
            return new Date(a.date) - new Date(b.date)
          });
        } else {
          aux = $scope.consumption.sort(function (a, b) {
            status_date = 0;
            return new Date(b.date) - new Date(a.date)
          });
        }
        break;
    }
    $scope.consumption = aux;
    $scope.groupToPagesData();
  }

  $scope.orderResultsDataM = function (parameter, key) {
    aux = [];
    switch (parameter) {
      case 'date_hour':
        if (status_date_m == 0) {
          aux = $scope.data_mobile.sort(function (a, b) {
            status_date_m = 1;
            return new Date(a.date) - new Date(b.date)
          });
        } else {
          aux = $scope.data_mobile.sort(function (a, b) {
            status_date_m = 0;
            return new Date(b.date) - new Date(a.date)
          });
        }
        break;
    }
    $scope.data_mobile = aux;
    $scope.groupToPagesDataM();
  }

  $scope.validateCell = function (values) {
    response = 0;
    if (typeof values.date === 'undefined') {
      response = 1;
    }
    return response;
  }

  $scope.downLoadReport = function (format) {
    var file = [];

    if ($scope.consumption != null && $scope.consumption != '') {
      $scope.consumption.forEach(function (item, key, array) {
        file.push(item['download']);
      })
    }

    $http.get('/rest/session/token').then(function (resp) {
      $http({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        data: file,
        url: '/tboapi/lines/data/download?_format=json&type=' + format
      }).then(function successCallback(response) {
        //console.log(response.data);
       // $scope.file = response.data;
        $scope.file = '/adf_core/download-example/'+response.data.file_name+'/NULL', '_blank';
      }, function errorCallback(response) {
        // called asynchronously if an error occurs
        // or server returns response with an error status.
        console.log('error obteniendo el servicio metodo post');
      });
    });
  }

  $scope.submitFilters = function () {
    var data = {
      data: $scope.consumption_complete,
      setFunction: 'returnFiltersData',
      startDate: $scope.start_date_data,
      endDate: $scope.end_date_data
    };
    $rootScope.$emit("filterData", data);
    var data_mobile = {
      data: $scope.data_mobile_complete,
      setFunction: 'returnFiltersDataMobile',
      startDate: $scope.start_date_data,
      endDate: $scope.end_date_data
    }
    $rootScope.$emit("filterData", data_mobile);
  }

  $rootScope.$on("returnFiltersData", function (event, data) {
    $scope.returnFiltersData(data.data);
  });

  $rootScope.$on("returnFiltersDataMobile", function (event, data) {
    $scope.returnFiltersDataMobile(data.data);
  });

  $scope.returnFiltersData = function (data) {
    if (data == 'Error en las fechas') {
      location.reload();
    } else {
      $scope.consumption = data.data;
      $scope.downLoadReport($scope.format);
      $scope.currentPageData = 0;
      $scope.groupToPagesData();
    }
  }

  $scope.returnFiltersDataMobile = function (data) {
    if (data == 'Error en las fechas') {
      location.reload();
    } else {
      $scope.data_mobile = data.data;
      $scope.currentPageDataMobile = 0;
      $scope.groupToPagesDataM();
    }
  }

  // calculate page in place
  $scope.groupToPagesDataM = function () {
    $scope.pagedItemsDataMobile = [];
    for (var i = 0; i < $scope.data_mobile.length; i++) {
      if (i % $scope.itemsPerPageDataM === 0) {
        $scope.pagedItemsDataMobile[Math.floor(i / $scope.itemsPerPageDataM)] = [$scope.data_mobile[i]];
      } else {
        $scope.pagedItemsDataMobile[Math.floor(i / $scope.itemsPerPageDataM)].push($scope.data_mobile[i]);
      }
    }
  };

  $scope.rangeDataM = function (size, start, end) {
    var ret = [];
    if (size < end) {
      end = size;
    }
    for (var i = 0; i < end; i++) {
      ret.push(i);
    }
    return ret;
  };

  $scope.prevPageDataM = function () {
    if ($scope.currentPageDataMobile > 0) {
      $scope.currentPageDataMobile--;
    }
  };

  $scope.nextPageDataM = function () {
    if ($scope.currentPageDataMobile < $scope.pagedItemsDataMobile.length - 1) {
      $scope.currentPageDataMobile++;
    }
  };

  $scope.setPageDataM = function () {
    $scope.currentPageDataMobile = this.n;
  };

  $scope.downloadFunctionData = function () {
    var parameters = {};
    parameters['type_file'] = $scope.format;
    var config_data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };
    $http.get('/tboapi/lines/data/download?_format=json', config_data)
      .then(function (resp) {
       console.log('inserto log');
      }, function () {
        console.log("Error obteniendo los datos");
      });
  }

}
