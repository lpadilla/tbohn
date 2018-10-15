/**
 * @file
 */

myApp.directive('ngMobileCallHistory', ['$http', '$rootScope', ngMobileCallHistory]);

function ngMobileCallHistory($http, $rootScope) {
  var directive = {
    restrict: 'EA',
    controller: CallHistoryController,
    link: linkFunc
  };
  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    $rootScope.$emit('filters', {
      start: 'start_date_voz',
      end: 'end_date_voz'
    });
    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_mobile_call_history];
    scope.init_date_call_history = config.init_date;
    scope.end_date_call_history = config.end_date;
    scope.start_date_voz = config.init_date;
    scope.end_date_voz = config.end_date;
    scope.changeDatepicker();
    retrieveInformation(scope, config, el);
    scope.empty_data_voz = undefined;

  }

  function retrieveInformation(scope, config, el) {
    scope.getDataTable("", "", config);
  }
}

CallHistoryController.$inject = ['$scope', '$http', '$rootScope'];

function CallHistoryController($scope, $http, $rootScope) {
  var reverseDate = false;
  var reverseHour = false;
  var reverseDateHour = false;
  $scope.insertLog = function (type, action) {
    $http.get('/rest/session/token').then(function (resp) {
      var parameters = {};
      parameters['type'] = type;
      parameters['action'] = action;
      $http({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        data: parameters,
        url: '/tbo-line/rest/call-history-log?_format=json'
      });
    });

  }

  $scope.groupToPagesVoz = function () {
    $scope.pagedItemsVoz = [];
    for (var i = 0; i < $scope.history_call.length; i++) {
      if (i % $scope.itemsPerPageVoz === 0) {
        $scope.pagedItemsVoz[Math.floor(i / $scope.itemsPerPageVoz)] = [$scope.history_call[i]];
      }
      else {
        $scope.pagedItemsVoz[Math.floor(i / $scope.itemsPerPageVoz)].push($scope.history_call[i]);
      }
    }
  };

  $scope.downloadReportVoz = function (type, action) {
    $scope.insertLog(type, action);
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_mobile_call_history];
    var parameters = {
      data: $scope.history_call,
      headers: {
        date: 'Fecha',
        hour: 'Hora',
        number: 'Destino',
        time_call: 'Duración'
      },
      download: 'voz',
      type: type
    };

    $http.get('/rest/session/token').then(function (resp) {
      $http({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': resp.data
        },
        url: config.url,
        data: parameters
      }).then(function (resp) {
        window.open('/adf_core/download-example/' + resp.data.file_name + '/NULL', '_blank');
      });
    });
     jQuery('#voz-download-select').val('');
      $scope.data.exportdata = '';
      jQuery('#voz-download-select').material_select();

  };

  // Filter data.
  $scope.filter_voz = function () {
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_mobile_call_history];
    if (($scope.start_date_voz === undefined || $scope.start_date_voz == '') && ($scope.end_date_voz === undefined || $scope.end_date_voz == '')) {
      $scope.empty_data_voz = undefined;
      $scope.groupToPagesVoz();
    }
    else {
      $scope.getDataTable($scope.start_date_voz, $scope.end_date_voz, config);
    }
    $scope.currentPageVoz = 0;
  };

  // Set filtered data.
  $scope.setDataVoz = function (data) {
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_mobile_call_history];
    if (data == 'Error en las fechas' || data == 'Error obteniendo información') {
      $scope.history_call = [];
      $scope.groupToPagesVoz();
      location.reload();
    }
    else if (data == false) {
      $scope.history_call = [];
      $scope.groupToPagesVoz();
      $scope.empty_data_voz = config.empty_message;
    }
    else {
      if (data.length === 0) {
        $scope.empty_data_voz = config.empty_message;
      }
      else {
        $scope.empty_data_voz = undefined;
      }

      $scope.history_call = data;
      $scope.groupToPagesVoz();
    }

  };

  // Sort information.
  $scope.sortByVoz = function (type) {

    if (type == 'date_show') {
      $scope.history_call.sort(function (a, b) {
        if (reverseDate === false) {
          return new Date(a.date).getTime() - new Date(b.date).getTime();
        }
        else {
          return new Date(b.date).getTime() - new Date(a.date).getTime();
        }

      });
      reverseDate = !reverseDate;
    }
    else if (type == 'hour') {
      $scope.history_call.sort(function (a, b) {
        if (reverseHour === false) {
          return new Date(a.date_sort).getHours() - new Date(b.date_sort).getHours();
        }
        else {
          return new Date(b.date_sort).getHours() - new Date(a.date_sort).getHours();
        }
      });
      reverseHour = !reverseHour;
    }
    else {
      $scope.history_call.sort(function (a, b) {
        if (reverseDateHour === false) {
          return new Date(a.date).getTime() - new Date(b.date).getTime();
        }
        else {
          return new Date(b.date).getTime() - new Date(a.date).getTime();
        }
      });
      reverseDateHour = !reverseDateHour;
    }

    $scope.groupToPagesVoz();
  };

  $scope.rangeVoz = function (size, start, end) {
    var ret = [];
    if (size < end) {
      end = size;
    }
    for (var i = 0; i < end; i++) {
      ret.push(i);
    }
    return ret;
  };

  $scope.prevPageVoz = function () {
    if ($scope.currentPageVoz > 0) {
      $scope.currentPageVoz--;
    }
  };

  $scope.nextPageVoz = function () {
    if ($scope.currentPageVoz < $scope.pagedItemsVoz.length - 1) {
      $scope.currentPageVoz++;
    }
  };

  $scope.setPageVoz = function () {
    $scope.currentPageVoz = this.n;
  };

  $scope.getDataTable = function (init_date, end_date, config) {

    var service_url = config.url;
    if (init_date != "" && end_date != "") {
      service_url = service_url + "&init_date=" + init_date + "&end_date=" + end_date;
      $scope.init_date_call_history = init_date;
      $scope.end_date_call_history = end_date;
    }
    var data = false;
    $http.get(service_url).then(function (resp) {
      if (resp.data.length > 0) {
        $scope.currentPageVoz = 0;
        data = resp.data;
        // scope.save_data_voz = resp.data;.
        $scope.num_logs_voz = data.length;
        $scope.itemsPerPageVoz = config.config_pager['number_rows_pages'];
        var gap = 1;
        gap += Math.floor($scope.num_logs_voz / $scope.itemsPerPageVoz);
        $scope.gapVoz = gap;
        // $scope.groupToPagesVoz();
        $scope.changeDatepicker();
      }
      else {
        $scope.empty_data_voz = config.empty_message;
      }
      $scope.setDataVoz(data);
    });
  }

  $scope.changeDatepicker = function () {
    angular.element('#start_date_voz').val($scope.init_date_call_history);
    angular.element('#end_date_voz').val($scope.end_date_call_history);
  }
}
