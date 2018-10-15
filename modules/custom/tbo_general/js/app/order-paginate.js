/**
 * @file
 * JavaScript for orderPaginate.
 */

var orderPaginate = angular.module("orderPaginate", []);

// Define factory.
orderPaginate.factory("$orderPaginateFactory", function () {
  return {
    orderTable: function ($scope, field, type, event, nameData, init, uuid) {
      var selector = 'span.icon-order.' + nameData;
      jQuery(selector).addClass('icon-arrowDisabled');
      jQuery(selector).removeClass('icon-arrowUp icon-arrowDown');

      if (!init) {
        var $iconClass = '.icon-order';
        var id = event.target.id;
        var $arrowe = '#' + id;
        jQuery($arrowe).removeClass('icon-arrowDisabled');

        if (!$scope[uuid]['reverseProfiles'][field]) {
          jQuery($arrowe).addClass('icon-arrowDown');
        }
        else {
          jQuery($arrowe).addClass('icon-arrowUp');
        }
      }
      else {
        var selectorFirst = 'span.icon-order-first.' + nameData;
        jQuery(selectorFirst).removeClass('icon-arrowDisabled');
        jQuery(selectorFirst).addClass('icon-arrowUp');
      }

      if (type == 'number') {
        if (!$scope[uuid]['reverseProfiles'][field]) {
          $scope[nameData].sort(function (a, b) {
            if (a[field] == null) {
              a[field] = ' ';
            }
            if (b[field] == null) {
              b[field] = ' ';
            }
            var textA = parseInt(b[field]);
            var textB = parseInt(a[field]);
            return textA < textB ? -1 : textA > textB ? 1 : 0;
          });
          state = true;
        }
        else {
          $scope[nameData].sort(function (a, b) {
            // Return String(a.name) - String(b.name)
            var textA = parseInt(a[field]);
            var textB = parseInt(b[field]);
            return textA < textB ? -1 : textA > textB ? 1 : 0;
          });
          state = false;
        }
      }
      else if (type == 'string') {
        var state = false;
        $scope[nameData].sort(function (a, b) {
          var pos = 0;

          if (a[field] == null) {
            a[field] = ' ';
          }
          if (b[field] == null) {
            b[field] = ' ';
          }

          if (a[field] == b[field]) {
            state = !$scope[uuid]['reverseProfiles'][field];
            return 0;
          }
          
          if (a[field] !== null && a[field] !== undefined) {
            var fieldA = a[field];
            if (Array.isArray(a[field])) {
              fieldA = a[field].toString();
              fieldA = fieldA.replace(",", " ");
            }
            while (fieldA[pos]) {
              if (b[field] !== null && b[field] !== undefined) {
                var fieldB = b[field];
                if (Array.isArray(b[field])) {
                  fieldB = b[field].toString();
                  fieldB = fieldB.replace(",", " ");
                }

                if (typeof fieldA[pos] !== 'undefined' && typeof fieldA[pos] !== undefined && fieldA[pos] !== null) {
                  var _a = fieldA[pos].toLowerCase();
                }
                else {
                  var _a = '';
                }

                if (typeof fieldB[pos] !== 'undefined' && typeof fieldB[pos] !== undefined && fieldB[pos] !== null) {
                  var _b = fieldB[pos].toLowerCase();
                }
                else {
                  var _b = '';
                }

                var aCharCodeAt = _a.charCodeAt(0);
                if (_a == " ") {
                  aCharCodeAt = 9999;
                }
                var bCharCodeAt = _b.charCodeAt(0);
                if (_b == " ") {
                  bCharCodeAt = 9999;
                }
                if (!$scope[uuid]['reverseProfiles'][field]) {
                  state = true;
                  if (aCharCodeAt != bCharCodeAt) {
                    return (aCharCodeAt > bCharCodeAt) ? -1 : 1;
                  }
                }
                else {
                  state = false;
                  if (aCharCodeAt != bCharCodeAt) {
                    return (bCharCodeAt > aCharCodeAt) ? -1 : 1;
                  }
                }
              }
              pos++;
            }
          }
        });
      }
      else if (type == 'date') {
        var state = false;
        $scope[nameData].sort(function (a, b) {
          if (a['timestamp'] == b['timestamp']) {
            return 0;
          }

          if (a['timestamp'] !== null && a['timestamp'] !== undefined) {
            if (b['timestamp'] !== null && b['timestamp'] !== undefined) {
              var _a = a['timestamp'];
              var _b = b['timestamp'];

              if (!$scope[uuid]['reverseProfiles'][field]) {
                state = true;
                return (_a > _b) ? -1 : 1;
              }
              else {
                state = false;
                return (_b > _a) ? -1 : 1;
              }
            }
          }
        });
      }
      $scope[uuid]['reverseProfiles'][field] = state;
      // Initialize paginate.
      $scope[uuid]['initPaginateNumber'] = $scope[uuid]['initPaginateNumberRespal'];
      $scope[uuid]['initPaginateStart'] = 0;
      $scope[uuid]['action'] = 0;
      $scope[uuid]['currentPage'] = 0;
      // Group pages.
      $scope.groupToPages(nameData);
    },
    // Paginate.
    range: function ($scope, size, start, end, uuid) {
      var ret = [];
      if (size < end) {
        end = size;
      }
      // First load.
      if ($scope[uuid]["initPaginate"]) {
        $scope[uuid]["initPaginate"] = false;
        end = $scope[uuid]["initPaginateNumber"];
      }
      else {
        if ($scope[uuid]["initPaginateSecond"]) {
          $scope[uuid]["initPaginateSecond"] = false;
          end = $scope[uuid]["initPaginateNumber"];
        }
      }

      // Logic for paginate.
      if (!$scope[uuid]["initPaginate"] && !$scope[uuid]["initPaginateSecond"]) {
        if ($scope[uuid]["action"] == 0 && !$scope[uuid]["notPaginate"]) {
          if ($scope[uuid]["initPaginateNumber"] == start) {
            if (!$scope[uuid]["firstProcess"]) {
              $scope[uuid]["initPaginateNumber"] = $scope[uuid]["initPaginateNumber"] + 1;
              $scope[uuid]["initPaginateStart"] = $scope[uuid]["initPaginateStart"] + 1;
              $scope[uuid]["firstProcess"] = true;
            }
            else {
              $scope[uuid]["firstProcess"] = false;
            }
          }
          else if ($scope[uuid]["initPaginateNumberRespal"] < $scope[uuid]["initPaginateNumber"]) {
            if ($scope[uuid]["initPaginateStart"] > start && $scope[uuid]["initPaginateNumber"] - 1 > start) {
              if (!$scope[uuid]["firstProcess"]) {
                $scope[uuid]["initPaginateNumber"] = $scope[uuid]["initPaginateNumber"] - 1;
                $scope[uuid]["initPaginateStart"] = $scope[uuid]["initPaginateStart"] - 1;
                $scope[uuid]["firstProcess"] = true;
              }
              else {
                $scope[uuid]["firstProcess"] = false;
              }
            }
          }
        }
        else if ($scope[uuid]["action"] == 1 && !$scope[uuid]["notPaginate"]) {
          if ($scope[uuid]["initPaginateNumber"] == start) {
            if (!$scope[uuid]["firstProcess"]) {
              $scope[uuid]["initPaginateNumber"] = $scope[uuid]["initPaginateNumber"] + 1;
              $scope[uuid]["initPaginateStart"] = $scope[uuid]["initPaginateStart"] + 1;
              $scope[uuid]["firstProcess"] = true;
            }
            else {
              $scope[uuid]["firstProcess"] = false;
            }
          }
          else if ($scope[uuid]["initPaginateNumberRespal"] < $scope[uuid]["initPaginateNumber"]) {
            if (!$scope[uuid]["initPaginateStart"] + 1 <= start && !$scope[uuid]["initPaginateNumber"] - 1 >= start) {
              if (!$scope[uuid]["firstProcess"]) {
                $scope[uuid]["initPaginateNumber"] = $scope[uuid]["initPaginateNumber"] - 1;
                $scope[uuid]["initPaginateStart"] = $scope[uuid]["initPaginateStart"] - 1;
                $scope[uuid]["firstProcess"] = true;
              }
              else {
                $scope[uuid]["firstProcess"] = false;
              }
            }
          }
        }
        else {
          if (!$scope[uuid]["firstProcess"]) {
            $scope[uuid]["notPaginate"] = true;
            $scope[uuid]["firstProcess"] = true;
          }
          else {
            $scope[uuid]["notPaginate"] = false;
          }
        }
        // Set values.
        end = $scope[uuid]["initPaginateNumber"];
        start = $scope[uuid]["initPaginateStart"];
      }

      for (var i = start; i < end; i++) {
        ret.push(i);
      }
      return ret;
    },

    prevPage: function ($scope, uuid) {
      if ($scope[uuid]['currentPage'] > 0) {
        $scope[uuid]['currentPage']--;
        $scope[uuid]["action"] = 0;
      }
      else {
        // No action.
        $scope[uuid]["action"] = 2;
      }
    },

    nextPage: function ($scope, uuid) {
      if ($scope[uuid]['currentPage'] < $scope[uuid]['pagedItems'].length - 1) {
        $scope[uuid]['currentPage']++;
        $scope[uuid]["action"] = 1;
      }
      else {
        // No action.
        $scope[uuid]["action"] = 2;
      }
    },

    setPage: function ($scope, n, uuid) {
      $scope[uuid]['currentPage'] = n;
      if ($scope[uuid]["initPaginateStart"] + 1 <= $scope[uuid]['currentPage'] && $scope[uuid]["initPaginateNumber"] - 1 >= $scope[uuid]['currentPage']) {
        $scope[uuid]["notPaginate"] = true;
      }
      $scope[uuid]["action"] = 2;
    },

    initPaginate: function ($scope, uuid, numberPages, dataAngular) {
      $scope[uuid] = {
        "initPaginate": true,
        "initPaginateSecond": true,
        "firstProcess": false,
        "notPaginate": false,
        "initPaginateNumber": numberPages,
        "initPaginateNumberRespal": numberPages,
        "initPaginateStart": 0,
        "action": 0,
        "firstPaginate": [],
        "haveInitOrder": false,
        "reverseProfiles": [],
        "pagedItems": [],
        "currentPage": 0,
        "clear": false,
        "dataAngular": dataAngular,
      };
    },

    groupToPages: function ($scope, directive, uuid) {
      $scope[uuid]['pagedItems'] = [];
      for (var i = 0; i < $scope[directive].length; i++) {
        var itemsPerPage = parseInt($scope[uuid]['itemsPerPage']);
        if (i % itemsPerPage === 0) {
          $scope[uuid]['pagedItems'][Math.floor(i / itemsPerPage)] = [$scope[directive][i]];
        }
        else {
          $scope[uuid]['pagedItems'][Math.floor(i / itemsPerPage)].push($scope[directive][i]);
        }
      }
      $scope[uuid]['currentPage'] = 0;
    },

    lengthPagedItems: function ($scope, uuid) {
      return $scope[uuid]['pagedItems'].length;
    },

    getPagedItems: function ($scope, uuid) {
      return $scope[uuid]['pagedItems'];
    },

    getCurrentPage: function ($scope, uuid) {
      return $scope[uuid]['currentPage'];
    },

    getGap: function ($scope, uuid) {
      return $scope[uuid]['gap'];
    }
  }
});
