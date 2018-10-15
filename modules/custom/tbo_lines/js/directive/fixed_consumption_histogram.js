/**
 * @file
 * Configuration of behaviour, for the "Fixed Consumption Histogram" Card.
 */
myApp.directive('ngFixedConsumptionHistogram', ['$http', ngFixedConsumptionHistogram]);

function ngFixedConsumptionHistogram($http) {

    var directive = {
        restrict: 'EA',
        controller: FixedConsumptionHistogramController,
        link: linkFunc
    }

    return directive;

    function linkFunc(scope, el, attr, ctrl) {
        var config = drupalSettings.fixedConsumptionHistogramBlock[scope.uuid_data_ng_fixed_consumption_histogram];
        scope.yAxisLabelFixedChart = Drupal.t('MIN');
        retrieveInformation(scope, config, el);
    }

    function retrieveType(scope, config, el) {
    }

    function retrieveInformation(scope, config, el) {
        parameters = {};
        parameters['type'] = 'histogram';
        var config_data = {
            params: parameters,
            headers: {'Accept': 'application/json'}
        };
        $http.get(config.url, config_data)
            .then(function (resp) {
                var data = resp.data;
                Chart.defaults.line.spanGaps = true;
                scope.labelsFixed = data.labels;
                scope.seriesFixed = data.series;
                scope.dataFixed = data.values;
                scope.chartOptionsF = {
                    scales: {
                        xAxes: [{
                            ticks: {
                                beginAtZero: true,
                                fontColor: "#879AB8",
                                FontFamily : "'Roboto',sans-serif",
                                FontSize : 12,
                            },
                            gridLines: {
                                // You can change the color, the dash effect, the main axe color, etc.
                                zeroLineWidth:1 ,
                                borderDash: [2, 4],
                                color: "#fff",
                                zeroLineColor: '#879AB8', // Color linea x - y.
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                fontColor: "#879AB8",
                                FontFamily : "'Roboto',sans-serif",
                                FontSize : 12,
                            },
                            gridLines: {
                                // You can change the color, the dash effect, the main axe color, etc.
                                zeroLineWidth:1 ,
                                borderDash: [2, 4],
                                color: "#dae2eb",
                                zeroLineColor: '#879AB8', // Color linea x - y.
                            }
                        }],
                    }
                };
                scope.colorsFixed = data.colors;
                scope.datasetOverrideFixed = [{
                    fill: true,
                    backgroundColor: [
                        data.colors[0],
                        data.colors[0],
                        data.colors[0],
                    ]
                }, {
                    fill: true,
                    backgroundColor: [
                        data.colors[1],
                        data.colors[1],
                        data.colors[1],
                    ]
                }, {
                    fill: true,
                    backgroundColor: [
                        data.colors[2],
                        data.colors[2],
                        data.colors[2],
                    ]
                }, {
                    fill: true,
                    backgroundColor: [
                        data.colors[3],
                        data.colors[3],
                        data.colors[3],
                    ]
                },
                ];

            }, function () {
                console.log("Error obteniendo los datos");
            });
    }

    FixedConsumptionHistogramController.$inject = ['$scope', '$http'];

    function FixedConsumptionHistogramController($scope, $http, $rootScope) {

    }
}
