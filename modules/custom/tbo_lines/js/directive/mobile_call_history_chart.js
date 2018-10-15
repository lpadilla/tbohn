/**
 * @file
 * Configuration of behaviour, for the "Mobile Call History Chart" Card.
 */
myApp.directive('ngMobileCallHistoryChart', ['$http', '$rootScope', ngMobileCallHistoryChart]);


function ngMobileCallHistoryChart($http, $rootScope) {
  var directive = {
    restrict: 'EA',
    controller: CallHistoryChartController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el) {

    $rootScope.$emit('filters', {
      start: 'start_date_voz_chart',
      end: 'end_date_voz_chart'
    });

    $rootScope.$emit('filters', {
      start: 'start_date_voz_chart_m',
      end: 'end_date_voz_chart_m'
    });
    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_mobile_call_history_chart];
    scope.yAxisLabelVozChart = Drupal.t('MIN');
    scope.ZAxisLabelVozChart = Drupal.t('DÍAS');
    var simple_colors = config.colorsChart;
    var object_colors = [];
    for (var color in simple_colors) {
      var object_color = {
        backgroundColor: simple_colors[color],
        borderColor: simple_colors[color],
      };
      object_colors.push(object_color);
    }
    scope.mobile_options = {
      tooltips: {enabled: false},
      scales: {
        xAxes: [{
          ticks: {
            lineColor: "blue",
            beginAtZero: true,
            fontColor: "#879AB8",
            FontFamily: "'Roboto',sans-serif",
            FontSize: 12
          },
          gridLines: {
            zeroLineWidth: 1,
            borderDash: [2, 4],
            color: "#fff",
            lineColor: "blue",
            zeroLineColor: '#879AB8'
          }
        }],
        yAxes:
          [{
            ticks: {
              beginAtZero: true,
              fontColor: "#879AB8",
              FontFamily: "'Roboto',sans-serif",
              FontSize: 12,
              color: "#dae2eb",
              lineColor: "blue"
            },
            gridLines: {
              zeroLineWidth: 1,
              borderDash: [2, 4],
              color: "#dae2eb",
              zeroLineColor: '#879AB8'
            }
          }]
      }
    };
    scope.desktop_options = {
      layout: {
        padding: {
          left: 10,
          right: 15
        }
      },
      scales: {
        xAxes: [{
          ticks: {
            lineColor: "blue",
            beginAtZero: true,
            fontColor: "#879AB8",
            FontFamily: "'Roboto',sans-serif",
            FontSize: 12
          },
          gridLines: {
            zeroLineWidth: 1,
            borderDash: [2, 4],
            color: "#fff",
            lineColor: "blue",
            zeroLineColor: '#879AB8'
          }
        }],
        yAxes:
          [{
            ticks: {
              beginAtZero: true,
              fontColor: "#879AB8",
              FontFamily: "'Roboto',sans-serif",
              FontSize: 12,
              color: "#dae2eb",
              lineColor: "blue"
            },
            gridLines: {
              zeroLineWidth: 1,
              borderDash: [2, 4],
              color: "#dae2eb",
              zeroLineColor: '#879AB8'
            }
          }]
      }
    };
    scope.colors_voz_chart = object_colors;
    scope.series_voz_chart = config.seriesChart;

    scope.init_date_chart_voz = config.init_date;
    scope.end_date_chart_voz = config.end_date;

    scope.start_date_voz_chart = config.init_date;
    scope.end_date_voz_chart = config.end_date;

    scope.start_date_voz_chart_m = config.init_date;
    scope.end_date_voz_chart_m = config.end_date;

    scope.changeDateDesktop();
    scope.changeDateMobile();
    scope.mdestino = '0 MIN';
    scope.mdistancia = '0 MIN';
    scope.mfavorito = '0 MIN';
    scope.mtigo = '0 MIN';
    scope.operador = '0 MIN';
    scope.mroaming = '0 MIN';
    retrieveInformation(scope, config, el);

  }

  function retrieveInformation(scope, config) {
    if (config.enviroment != 'fijo') {
      scope.dataPlan(config);
      scope.dataChart("","",config);
    }
  }
}

CallHistoryChartController.$inject = ['$scope', '$http', '$location', '$rootScope'];

function CallHistoryChartController($scope, $http, $location, $rootScope) {
  $scope.insertLog = function (type, action, url) {
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
      }).then(function () {
        var sub_cadena = url.substr(0, 4);
        if (sub_cadena == "http") {
          window.location = url;
        }
        else {
          var loc = window.location;
          window.location = loc.protocol + "//" + loc.hostname + loc.port + url;
        }

      });
    });
  }

  $scope.filter_voz_chart = function () {

    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_mobile_call_history_chart];

     if (($scope.start_date_voz_chart !== undefined || $scope.start_date_voz_chart != '') && ($scope.end_date_voz_chart !== undefined || $scope.end_date_voz_chart != '')) {
     $scope.init_date_chart_voz = $scope.start_date_voz_chart;
     $scope.end_date_chart_voz = $scope.end_date_voz_chart;
     $scope.start_date_voz_chart_m = $scope.start_date_voz_chart;
     $scope.end_date_voz_chart_m = $scope.end_date_voz_chart;
     $scope.changeDateMobile();
     $scope.dataChart($scope.start_date_voz_chart,$scope.end_date_voz_chart,config);
    }
  }
  $scope.filter_voz_chart_m = function () {

    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_mobile_call_history_chart];

    if (($scope.start_date_voz_chart_m !== undefined || $scope.start_date_voz_chart_m != '') && ($scope.end_date_voz_chart_m !== undefined || $scope.end_date_voz_chart_m != '')) {
      $scope.init_date_chart_voz = $scope.start_date_voz_chart_m;
      $scope.end_date_chart_voz = $scope.end_date_voz_chart_m;
      $scope.start_date_voz_chart = $scope.start_date_voz_chart_m;
      $scope.end_date_voz_chart = $scope.end_date_voz_chart_m;
      $scope.changeDateDesktop();
      $scope.dataChart($scope.start_date_voz_chart_m,$scope.end_date_voz_chart_m,config);
    }
  }

  // Set filtered data.
  $scope.setDataVozChart = function (data) {
    var dat = [];
    if (data == 'Error en las fechas' || data == 'Error obteniendo información') {
      location.reload();
    }
    else {
      if (data == "") {
        $scope.data_voz_chart = $scope.setDataChart(dat);
      }
      else {
        $scope.data_voz_chart = $scope.setDataChart(data.data);
      }
    }
    $scope.changeDateMobile();
    $scope.changeDateDesktop();
  };

  // Build data.
  $scope.setDataChart = function (data) {
    var data_chart = [[], [], [], [], [], []];

    for (var i = 0; i < $scope.labels_voz_chart.length; i++) {
      if (data[i] == undefined) {
        data_chart[0][i] = 0;
        data_chart[1][i] = 0;
        data_chart[2][i] = 0;
        data_chart[3][i] = 0;
        data_chart[4][i] = 0;
        data_chart[5][i] = 0;
      }
      else {
        if (data[i]["values"][0] != undefined) {
          data_chart[0][i] = Math.round(data[i]["values"][0] / 60);
        }
        else {
          data_chart[0][i] = 0;
        }
        if (data[i]["values"][1] != undefined) {
          data_chart[1][i] = Math.round(data[i]["values"][1] / 60);
        }
        else {
          data_chart[1][i] = 0;
        }
        if (data[i]["values"][2] != undefined) {
          data_chart[2][i] = Math.round(data[i]["values"][2] / 60);
        }
        else {
          data_chart[2][i] = 0;
        }
        if (data[i]["values"][3] != undefined) {
          data_chart[3][i] = Math.round(data[i]["values"][3] / 60);
        }
        else {
          data_chart[3][i] = 0;
        }
        if (data[i]["values"][4] != undefined) {
          data_chart[4][i] = Math.round(data[i]["values"][4] / 60);
        }
        else {
          data_chart[4][i] = 0;
        }
        if (data[i]["values"][5] != undefined) {
          data_chart[5][i] = Math.round(data[i]["values"][5] / 60);
        }
        else {
          data_chart[5][i] = 0;
        }

      }
    }
    return data_chart;
  };

  $scope.dataPlan = function (config) {
    $http.get(config.url_plan).then(function (resp) {
      if (resp.data.error) {
        $scope.mdestino = '0 MIN';
        $scope.mdistancia = '0 MIN';
        $scope.mfavorito = '0 MIN';
        $scope.mtigo = '0 MIN';
        $scope.operador = '0 MIN';
        $scope.mroaming = '0 MIN';
      }
      else {
        $scope.mdestino = resp.data.mdestino;
        $scope.mdistancia = resp.data.mdistancia;
        $scope.mfavorito = resp.data.mfavorito;
        $scope.mtigo = resp.data.mtigo;
        $scope.operador = resp.data.operador;
        $scope.mroaming = resp.data.mroaming;
      }

    }, function () {
    });
  }

  $scope.dataChart = function (init_date, end_date, config) {

    var service_url = config.url;
    if (init_date != "" && end_date != "") {
      service_url = service_url + "&init_date=" + init_date + "&end_date=" + end_date;
      $scope.init_date_chart_voz = init_date;
      $scope.end_date_chart_voz = end_date;
    }
     $http.get(service_url).then(function (resp) {
      if (resp.data.error) {
        $scope.labels = [];
      }
      else {
        $scope.labels_voz_chart = resp.data.labels;
        $scope.setDataVozChart(resp.data);
      }

    }, function () {
    });
  }

  $scope.changeDateDesktop = function () {
    angular.element('#start_date_voz_chart').val($scope.init_date_chart_voz);
    angular.element('#end_date_voz_chart').val($scope.end_date_chart_voz);
  }

  $scope.changeDateMobile = function () {
    angular.element('#start_date_voz_chart_m').val($scope.init_date_chart_voz);
    angular.element('#end_date_voz_chart_m').val($scope.end_date_chart_voz);
  }
}
