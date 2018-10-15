/**
 * @file
 */

myApp.directive('ngSmsConsumptionHistory', ['$http', '$rootScope', ngSmsConsumptionHistory]);

function ngSmsConsumptionHistory($http, $rootScope) {

  var directive = {
    restrict: 'EA',
    controller: smsConsumptionHistoryController,
    link: linkFunc
  };

    return directive;

  function linkFunc(scope, el) {
        var config = drupalSettings.b2bBlock[scope.uuid_data_ng_sms_consumption_history];
    scope.empty_message_sms = undefined;
    $rootScope.$emit('filters', {
      start: 'start_date_sms',
      end: 'end_date_sms'
    });

    $rootScope.$on('setDataSms', function (event, data) {
      scope.setSmsData(data.data);
    });

    // Set filtered data.
    scope.setSmsData = function (data) {
      var config = drupalSettings.b2bBlock[scope.uuid_data_ng_sms_consumption_history];
      if (data.data === false || data == 'Error en las fechas' || data == 'Error obteniendo información') {
        location.reload();
      }
      else {
        if (data.data.length === 0) {
          scope.empty_message_sms = config.empty_message;
        }
        else {
          scope.empty_message_sms = undefined;
        }

        scope.history_sms = data.data;
        scope.num_logsSms = scope.history_sms.length;
        var gap = 1;
        gap += Math.floor(scope.num_logsSms / scope.itemsPerPageSms);
        scope.gapSms = gap;
        scope.groupToPagesSms();
      }
    };

    retrieveInformation(scope, config, el);

    }

    function retrieveInformation(scope, config, el) {

    $http.get(config.url).then(function (resp) {
        if (resp.data.length > 0) {
        scope.currentPageSms = 0;
        scope.save_data_sms = resp.data;
        scope.itemsPerPageSms = config.config_pager['number_rows_pages'];
        $rootScope.$emit('filterData', {
          data : scope.save_data_sms,
          setFunction: 'setDataSms',
          startDate: scope.start_date_sms,
          endDate: scope.end_date_sms
        });
            }
else {
                scope.empty_message_sms = config.empty_message;
            }
        });
    }

}

smsConsumptionHistoryController.$inyect = ['$http', '$scope', '$rootScope'];

function smsConsumptionHistoryController($http, $scope, $rootScope) {
    var reverseDate = false;
    var reverseHour = false;
    var reverseDateHour = false;

  // Calculate page in place.
  $scope.groupToPagesSms = function () {
    $scope.pagedItemsSms = [];
    for (var i = 0; i < $scope.history_sms.length; i++) {
      if (i % $scope.itemsPerPageSms === 0) {
        $scope.pagedItemsSms[Math.floor(i / $scope.itemsPerPageSms)] = [$scope.history_sms[i]];
      }
else {
        $scope.pagedItemsSms[Math.floor(i / $scope.itemsPerPageSms)].push($scope.history_sms[i]);
      }
    }
  };

    $scope.downloadReportSms = function (type) {

        var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_sms_consumption_history];

        var parameters = {
            data: $scope.history_sms,
            headers: {
                date: 'Fecha',
                hour: 'Hora',
                msisdn: 'Destino'
            },
            download: 'sms',
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
        jQuery('#sms-download-select').val('');
        $scope.data.exportdataSms = '';
        jQuery('#sms-download-select').material_select();

    };

    // Filter data.
    $scope.filter_sms = function () {
        if (($scope.start_date_sms === undefined || $scope.start_date_sms == '') && ($scope.end_date_sms === undefined || $scope.end_date_sms == '')) {
      $scope.history_sms = $scope.save_data_sms;
      $scope.empty_message_sms = undefined;
            $scope.groupToPagesSms();
        }
else {
            $rootScope.$emit('filterData', {
              data : $scope.save_data_sms,
        setFunction: 'setDataSms',
        startDate: $scope.start_date_sms,
        endDate: $scope.end_date_sms
            });
        }
    $scope.currentPageSms = 0;
    };

    // Sort information.
    $scope.sortBySms = function (type) {

        if (type == 'date_show') {
            $scope.history_sms.sort(function (a, b) {
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
            $scope.history_sms.sort(function (a, b) {
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
            $scope.history_sms.sort(function (a, b) {
                if (reverseDateHour === false) {
                    return new Date(a.date_sort).getTime() - new Date(b.date_sort).getTime();
                }
                else {
                    return new Date(b.date_sort).getTime() - new Date(a.date_sort).getTime();
                }
            });
            reverseDateHour = !reverseDateHour;
        }

        $scope.groupToPagesSms();
    };

    $scope.rangeSms = function (size, start, end) {
        var ret = [];
        if (size < end) {
            end = size;
        }
        for (var i = 0; i < end; i++) {
            ret.push(i);
        }
        return ret;
    };

    $scope.prevPageSms = function () {
        if ($scope.currentPageSms > 0) {
            $scope.currentPageSms--;
        }
    };

    $scope.nextPageSms = function () {
        if ($scope.currentPageSms < $scope.pagedItemsSms.length - 1) {
            $scope.currentPageSms++;
        }
    };

    $scope.setPageSms = function () {
        $scope.currentPageSms = this.n;
    };
}
