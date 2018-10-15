myApp.directive('ngCorporativeProfiles', ['$http', '$rootScope', ngCorporativeProfiles]);

function ngCorporativeProfiles($http, $rootScope) {

  return {
    restrict: 'EA',
    controller: corporativeProfilesController,
    link: linkFunc
  };

  function linkFunc(scope, el) {

    var config = drupalSettings.corporativeProfilesBlock[scope.uuid_data_ng_corporative_profiles];

    scope.itemsPerPageCorProfiles = config.config_pager.number_rows_pages;
    scope.lengthSearch = [];

    $rootScope.$emit('loadFunctions', {
      name: 'changeContractProfiles'
    });

    $rootScope.$on('changeContractProfiles', function(event, data) {
      scope.contractCorProfiles = data.select.contract;
      scope.accountIdCorProfiles = data.select.accountId;
      scope.newData = false;
      scope.getProfliesInformation(config);
    });

    /*$rootScope.$on('getCollectService', function() {
      $rootScope.
    });*/

    scope.loadMore = function () {
      if(scope.newData !== false) {
        if (scope.auxScroll.length > 0 ) {
          var sizeInvoice = scope.auxScroll.length;
          scope.corporativeProfiles = scope.scrollAux(sizeInvoice);
        } else if (typeof scope.information[1] != 'undefined') {
          scope.corporativeProfiles = scope.scroll(sizeInvoice);
        }
      }
    }
  }

}

corporativeProfilesController.$inyect = ['$http', '$scope'];

function corporativeProfilesController($http, $scope) {

  $scope.getProfliesInformation = function(config) {
    $scope.currentPageCorProfiles = 0;

    var params = {
      params: {
        accountId: $scope.accountIdCorProfiles,
        contractId: $scope.contractCorProfiles
      }
    };
    $scope.newData = false;
    $scope.corporativeProfiles = {};
    $scope.auxScroll = {};
    $scope.information = {};
    $scope.quantiyScrollContractProfile = config.scroll_number;

    $http.get(config.url, params).then(function(resp) {

      if(resp.data.error) {
        if ( resp.data.code == 404 ) {
          $scope.empty_message_corp_profiles = config.error_message_404;
        }
        else {
          $scope.empty_message_corp_profiles = config.error_message;
        }

        $scope.information = [];
      }
      else {
        $scope.valIndex = [];
        $scope.empty_message_corp_profiles = undefined;
        $scope.information = resp.data.data;
        $scope.corporativeProfiles = $scope.scroll();
        $scope.auxScroll = resp.data.data;
        $scope.initValIndex();
        $scope.disabled_for_line  = (resp.data.total_lines == 0) ? 'disabled' : '';
        $scope.disabled_for_line_table  = (resp.data.total_lines == 0) ? 'disabled_anchor' : '';

        var gap = 1;
        gap += Math.floor($scope.information.length / $scope.itemsPerPageCorProfiles);
        $scope.gapCorProfiles = gap;
        $scope.pagesCorporativeProfiles();
      }

    });
    $scope.newData = true;
  };

  $scope.pagesCorporativeProfiles = function () {
    $scope.pagedItemsCorProfiles = [];
    for (var i = 0; i < $scope.information.length; i++) {
      if (i % $scope.itemsPerPageCorProfiles === 0) {
        $scope.pagedItemsCorProfiles[Math.floor(i / $scope.itemsPerPageCorProfiles)] = [$scope.information[i]];
      } else {
        $scope.pagedItemsCorProfiles[Math.floor(i / $scope.itemsPerPageCorProfiles)].push($scope.information[i]);
      }
    }
  };

  $scope.rangeCorProfiles = function (size, start, end) {
    var ret = [];
    if (size < end) {
      end = size;
    }
    for (var i = 0; i < end; i++) {
      ret.push(i);
    }
    return ret;
  };

  $scope.prevPageCorProfiles = function () {
    if ($scope.currentPageCorProfiles > 0) {
      $scope.currentPageCorProfiles--;
    }
  };

  $scope.nextPageCorProfiles = function () {
    if ($scope.currentPageCorProfiles < $scope.pagedItemsCorProfiles.length - 1) {
      $scope.currentPageCorProfiles++;
    }
  };

  $scope.setPageCorProfiles = function () {
    $scope.currentPageCorProfiles = this.n;
  };

  $scope.reverseProfiles = {
    profile: false,
    associated_lines: false,
    package_value: false,
    total_value: false
  };

  $scope.sortTableProfiles = function(type, event) {
    
    var $iconClass = '.icon-order'
    var $arrowe = jQuery(event.target).find($iconClass)
   
    if (jQuery($arrowe).hasClass('icon-arrow-down')) {
      jQuery($arrowe).removeClass('icon-arrow-down')
      jQuery($arrowe).addClass('icon-arrow-up')
    } else {
      jQuery($iconClass).removeClass('icon-arrow-down')
      jQuery($iconClass).removeClass('icon-arrow-up')
      jQuery($arrowe).addClass('icon-arrow-down')
    }
    
    if(type != 'profile') {
      $scope.information.sort(function(a, b) {
        return (!$scope.reverseProfiles[type]) ? a[type] - b[type] : b[type] - a[type];
      });
    }
    else {
      $scope.information.sort(function(a, b) {
        var pos = 0;

        if(a[type] == b[type]) return 0;

        while(a[type][pos]) {
          var _a = a[type][pos].toLowerCase();
          var _b = b[type][pos].toLowerCase();

          if(!$scope.reverseProfiles[type]) {
            return ( _a.charCodeAt(0) > _b.charCodeAt(0)) ? -1 : 1;
          }
          else {
            return ( _b.charCodeAt(0) >_a.charCodeAt(0)) ? -1 : 1;
          }
          pos++;
        }
      });
    }
    $scope.reverseProfiles[type] = !$scope.reverseProfiles[type];
    $scope.pagesCorporativeProfiles();
  };

  $scope.scroll = function (size) {
    var aux = [];
    scroll = {};
    if (size === undefined) {
      var quantity = $scope.quantiyScrollContractProfile;
    } else {
      var quantity = size + $scope.quantiyScrollContractProfile;
    }

    if (quantity > 0) {
      var slice = quantity;
      if (quantity > $scope.information.length) {
        slice = $scope.information.length;
      }

      aux.splice(0, 0, $scope.information.slice(0, slice));
      scroll[0] = aux[0];
    }

    return scroll;
  };

  // Scroll Aux.
  $scope.scrollAux = function (size) {
    var aux = [];
    scroll = {};

    if (size === undefined) {
      var quantity = $scope.quantiyScrollContractProfile;
    }
    else {
      var quantity = size + $scope.quantiyScrollContractProfile;
    }

    if (quantity > 0) {
      var slice = quantity;
      if (quantity > $scope.auxScroll.length) {
        slice = $scope.auxScroll.length;
      }
      aux.splice(0, 0, $scope.auxScroll.slice(0, slice));
      scroll[0] = aux[0];
    }

    return scroll;
  };

  $scope.setLog = function($event, type, profile) {
    var config = drupalSettings.corporativeProfilesBlock[$scope.uuid_data_ng_corporative_profiles];
    var params = {
      params: {
        log: 1,
        type: type,
        profile: profile,
        contract: $scope.contractCorProfiles
      }
    };

    $http.get(config.url, params).then(function(resp) {
      if($event) {
        window.location = $event.target.href;
      }
    });
  };

  $scope.initValIndex = function() {
    for(index in $scope.auxScroll) {
      $scope.valIndex[index] = 0;
      $scope.lengthSearch[index] = 0;
    }
  }

  $scope.searchProfile = function(toSearch) {
    $scope.suggestions = [];

    if(toSearch !== undefined && toSearch != '') {
      for(index in $scope.auxScroll) {
        if($scope.auxScroll[index].profile.toLowerCase().search(toSearch.toLowerCase()) > -1) {

          jQuery('.container-suggestions').addClass('active-dropdown');
          var val = $scope.auxScroll[index].profile.toLowerCase().search(toSearch.toLowerCase());

          if($scope.lengthSearch[index] != toSearch.length) {
            $scope.valIndex[index] = ($scope.lengthSearch[index] > toSearch.length) ? $scope.valIndex[index] - 1 : $scope.valIndex[index] + 1;
          }

          if (val == 0) {
            var complete = $scope.auxScroll[index].profile.slice(val + $scope.valIndex[index]);
            $scope.auxScroll[index].search = "" + toSearch + "<strong>" + complete + "</strong>";
          }
          else {
            var startComplete = $scope.auxScroll[index].profile.slice(0, val);
            var endComplete = $scope.auxScroll[index].profile.slice(val + $scope.valIndex[index]);
            $scope.auxScroll[index].search = "<strong>" + startComplete + "</strong>"+ toSearch + "<strong>" + endComplete + "</strong>";
          }

          $scope.suggestions.push($scope.auxScroll[index]);
          $scope.lengthSearch[index] = toSearch.length;
        }
      }
    }
    else {
      jQuery('.container-suggestions').removeClass('active-dropdown');
      $scope.initValIndex();
      $scope.information = $scope.auxScroll;
      $scope.pagesCorporativeProfiles();
      $scope.newData = true;
      $scope.loadMore();
    }
  };

  // Set suggestion in the table.
  $scope.selectedSuggestion = function(key, suggestion) {
    $scope.currentPageCorProfiles = 0;
    $scope.name_filter = $scope.suggestions[key].profile;
    $scope.lengthSearch.forEach(function(value, key) {
      $scope.lengthSearch[key] = $scope.name_filter.length;
      $scope.valIndex[key] = $scope.name_filter.length;
    });
    $scope.setLog('', 'search', $scope.suggestions[key].profile);
    $scope.searchProfile($scope.name_filter);
    $scope.information = [];
    $scope.corporativeProfiles[0] = [];
    $scope.information.push($scope.suggestions[key]);
    $scope.newData = false;
    $scope.corporativeProfiles[0].push($scope.suggestions[key]);
    $scope.pagesCorporativeProfiles();
    $scope.suggestions = [];
  }

}
