myApp.directive('ngGroupsList', ['$http', ngGroupsList]);

function ngGroupsList($http) {
  var directive = {
    restrict: 'EA',
    controller: GroupsListController,
    link: linkFunc
  };

  return directive;

  /**
   *
   * @param scope
   * @param el
   * @param attr
   * @param ctrl
   */
  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.groupsListBlock[scope.uuid];
    retrieveInformation(scope, config, el);

    /**
     *
     * @returns {boolean}
     */
    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };

    /**
     *
     */
    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        if (scope.GroupsList.error) {
          jQuery("div.actions", el).hide();
        }
      }
    });

    var aux_account = [];
    var aux_accountD = [];

    /**
     *
     */
    scope.closeSuggestions = function () {
      document.getElementById('suggestionsAccount').style.display = 'none';
    }
    setTimeout(scope.closeSuggestions)

    scope.suggestionsAccount = [];
    scope.selectedIndexAccount = -1;

    /**
     *
     * @param key_data
     */
    scope.searchAccount = function (key_data) {
      console.log('searchAccount');
      document.getElementById('suggestionsAccount').style.display = 'block';
      scope.dataSearch = scope[key_data];
      if(!scope[key_data] == '' || !scope[key_data] == 'undefined') {

        $http.get('/tbo_groups/autocomplete/'+scope[key_data]+'?_format=json')
          .then(function (resp) {
            scope.suggestionsAccount = resp.data;
            scope.selectedIndex = -1;
          }, function () {
            console.log("Error obteniendo los datos");
          });
        scope.selectedIndexAccount = -1;
      } else {
        scope.suggestionsAccount = scope.suggestionsAccount;
      }

      scope.suggestionsAccountMobile = scope.suggestionsAccount;
      /*document.getElementById('multiple-select-account-mobile').style.display = 'none';
       setTimeout(function () {
       document.getElementById('multiple-select-account-mobile').style.display = 'block';
       jQuery('#addresM').material_select();
       }, 1000);*/
      angular.element('#account').triggerHandler('click');
    };

    /**
     *
     * @param event
     * @param field
     */
    scope.checkKeyDownAccount = function (event, field) {
      console.log('checkKeyDownAccount');
      if (event.keyCode === 40) {//down key, increment selectedIndex
        event.preventDefault();
        if (scope.selectedIndexAccount + 1 !== scope.suggestionsAccount.length) {
          scope.selectedIndexAccount++;
        }
      }
      else if (event.keyCode === 38) { //up key, decrement selectedIndex
        event.preventDefault();
        if (scope.selectedIndexAccount - 1 !== -1) {
          scope.selectedIndexAccount--;
        }
      }
      else if (event.keyCode === 13) { //enter pressed
        scope.resultClickedAccount(scope.selectedIndexAccount, field);
      }
      else {
        scope.suggestionsAccount = [];
        scope.suggestionsAccountMobile = scope.suggestionsAccount;
        jQuery('#accountM').material_select();
        angular.element('#account').triggerHandler('click');
      }
    };

    /**
     *
     * @param index
     * @param field
     */
    scope.resultClickedAccount = function (index, field) {
      console.log('resultClickedAccount');
      scope[field] = '';
      if (scope.accountOptions.length < 5) {
        scope.accountOptions.push(scope.suggestionsAccount[index].account);
        scope.selectChange();
        scope.suggestionsAccount = [];
        scope.suggestionsAccountMobile = scope.suggestionsAccount;
        jQuery('#accountM').material_select();
        angular.element('#account').triggerHandler('click');
      }
    };

    /**
     *
     * @param key
     */
    scope.removeChipAccount = function (key) {
      console.log('removeChipAccount');
      var index = scope.accountOptions.indexOf(key);
      scope.accountOptions.splice(index, 1);

      var json = JSON.stringify(scope.auxScroll);
      jQuery('input[name="associated_accounts_value"]').attr('value', json);


      scope.selectChange();
    };

    /**
     *
     * @param type
     * @param checked
     * @param $event
     */
    scope.selectChangeDesktop = function (type, checked, $event) {
      console.log('selectChangeDesktop');
      if (type == 'account' && checked) {
        aux_accountD.push($event.target.value);
      } else if (type == 'account' && !checked) {
        index = aux_accountD.indexOf($event.target.value);
        aux_accountD.splice(index);
      }

      scope.accountOptions = aux_accountD;
      scope.selectChange();
    }

    /**
     *
     * @param type
     * @param checked
     * @param $event
     */
    scope.selectChangeMobile = function (type, checked, $event) {

      if (type == 'account' && checked) {
        aux_account.push($event.target.id);
      } else if (type == 'account' && !checked) {
        index = aux_account.indexOf($event.target.id);
        aux_account.splice(index);
      }

      scope.accountOptions = aux_account;
      scope.selectChange();
    };

    /**
     *
     */
    scope.selectChange = function () {
      scope.aux_filters = 0;
      var accounts = scope.suggestionsAccount;
      var aux = [];
      var aux_resp = [];

      if (typeof scope.accountOptions !== 'undefined' && scope.accountOptions.length > 0) {
        scope.aux_filters = 1;
        scope.accountOptions.forEach(function (filter, key, array) {
          accounts.forEach(function (bill, pos, array1) {
            if (filter == bill.account) {
              aux_resp [pos] = bill;
            }
          })
        })
      } else {
        aux_resp = accounts;
      }

      scope.auxScroll = aux_resp; //console.log(scope.auxScroll);
      scope.accounts = scope.auxScroll.slice(0, 10);

      var json = JSON.stringify(scope.auxScroll);
      jQuery('input[name="associated_accounts_value"]').attr('value', json);

    };

  }

  /**
   * alimentamos la tabla con los datos que econtremos de grupos en la base de datos
   * @param scope
   * @param config
   * @param el
   */
  function retrieveInformation(scope, config, el) {
    console.log('retrieveInformation');
    if (scope.resources.indexOf(config.url) == -1) {
      //Add key for this display
      var parameters = {};
      parameters['config_columns'] = config.config_columns;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      $http.get(config.url, config_data)
        .then(function (resp) {
          scope.GroupsList = resp.data;

          /**
           * cambiamos el paginador
           */
          var num_groups = scope.GroupsList.length;
          var num_rows = config.config_pager['page_elements'];
          scope.itemsPerPage = config.config_pager['page_elements'];
          var gap = 1;
          gap += Math.floor(num_groups / num_rows);
          scope.gap = gap;
          scope.currentPage = 0;
          scope.groupToPages();
          jQuery(el).parents("section").fadeIn('slow');
        }, function () {
          console.log("Error obteniendo los datos");
        });
    }
  }
}

GroupsListController.$inject = ['$scope', '$http'];

/**
 *
 * @param $scope
 * @param $http
 * @constructor
 */
function GroupsListController($scope, $http) {
  // Init vars
  if (typeof $scope.GroupsList == 'undefined') {
    $scope.GroupsList = "";
    $scope.GroupsList.error = false;
  }

  if (typeof $scope.resources == 'undefined') {
    $scope.resources = [];
  }

  $scope.groupedItems = [];
  $scope.pagedItems = [];
  $scope.currentPage = 0;

  /**
   * Function por filter info
   */
  $scope.filterGroups = function () {
    console.log('filterGroups');
    //Get config
    var config = drupalSettings.groupsListBlock[$scope.uuid];

    //Get value filters
    var package = {};
    for (filter in config['filters']) {
      if (!$scope[filter] == '' || !$scope[filter] === undefined) {
        package[filter] = $scope[filter];
      }
    }

    package['config_columns'] = config.config_columns;

    //Add config to url
    var config_data = {
      params: package,
      headers: {'Accept': 'application/json'}
    };

    //Get Data For Filters;
    $http.get(config.url, config_data)
      .then(function (resp) {
        $scope.GroupsList = resp.data;
        var num_groups = $scope.GroupsList.length;
        var num_rows = config.config_pager['page_elements'];
        $scope.itemsPerPage = config.config_pager['page_elements'];
        var gap = 1;
        gap += Math.floor(num_groups / num_rows);
        $scope.gap = gap;
        $scope.currentPage = 0;
        $scope.groupToPages();
      }, function () {
        console.log("Error obteniendo los datos");
      });
  }

  /**
   *
   */
  $scope.orderReverse = function () {
    console.log('orderReverse');
    $scope.GroupsList = $scope.GroupsList.reverse();
    $scope.groupToPages();
  }

  /**
   *
   */
  $scope.groupToPages = function () {
    console.log('groupToPages');
    $scope.pagedItems = [];
    for (var i = 0; i < $scope.GroupsList.length; i++) {
      if (i % $scope.itemsPerPage === 0) {
        $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.GroupsList[i]];
      } else {
        $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.GroupsList[i]);
      }
    }
  };

  /**
   *
   * @param size
   * @param start
   * @param end
   * @returns {Array}
   */
  $scope.range = function (size, start, end) {
    console.log('range');
    var ret = [];
    if (size < end) {
      end = size;
    }
    for (var i = 0; i < end; i++) {
      ret.push(i);
    }
    return ret;
  };

  /**
   *
   */
  $scope.prevPage = function () {
    console.log('prevPage');
    if ($scope.currentPage > 0) {
      $scope.currentPage--;
    }
  };

  /**
   *
   */
  $scope.nextPage = function () {
    console.log('nextPage');
    if ($scope.currentPage < $scope.pagedItems.length - 1) {
      $scope.currentPage++;
    }
  };

  /**
   *
   */
  $scope.setPage = function () {
    console.log('setPage');
    $scope.currentPage = this.n;
  };

  //Declare vars and function for ordering
  $scope.predicate = 'attraction';
  $scope.reverse = false;
  $scope.order = function (predicate) {
    $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
    $scope.predicate = predicate;
  };

  /**
   * open modal to delete group
   * @param $event
   * @param name
   */
  $scope.deleteGroup = function ($event, name) {
    console.log('deleteGroup');
    $event.preventDefault();
    var change_name = name.replace(/ /g, "-");
    jQuery('#modal').load('/groups/message/deleteGroup/' + change_name + window.location.pathname).dialog('open');
  }

  /**
   * Alimenta los datos del formulario de editar grupo
   * @param $event
   * @param name
   */
  $scope.getDataGroup = function ($event, name) {
    console.log('getDataGroup');
    $event.preventDefault();
    $http.get('/groups/get_data/'+name+'?_format=json')
      .then(function (resp) {
        $scope.GroupData = resp.data;

        if($scope.GroupData != false) {

          jQuery('input[name="is_new"]').attr('value', 0);
          jQuery('input[name="gid_update"]').attr('value', $scope.GroupData.id);

          jQuery("input#edit-name").attr('value', $scope.GroupData.name);

          console.log($scope.GroupData.associated_accounts);

          $scope.accountOptions = Object.keys($scope.GroupData.associated_accounts).map(function (key) { return $scope.GroupData.associated_accounts[key]; });


          $scope.selectDefaultAccounts();

        }
      }, function () {
        console.log("Error obteniendo los datos");
      });
  }

  /**
   *
   */
  $scope.searchAllAccounts = function () {
    console.log('searchAllAccounts');

    return $http.get('/tbo_groups/get_all_accounts/?_format=json').then(function (result) {

      if (result.status !== 200)
        return [];

      $scope.selectedIndex = -1;

      return result.data;

    }, function (err) {

      return [];

    });


    /*$http.get('/tbo_groups/get_all_accounts/?_format=json')
        .then(function (resp) {
          console.log(resp.data);
          $scope.suggestionsAccount = resp.data;
          $scope.selectedIndex = -1;
        }, function () {
          console.log("Error obteniendo los datos");
        });*/

  };

  /**
   *
   */
  $scope.selectDefaultAccounts = function () {
    console.log('selectDefaultAccounts');

    $scope.aux_filters = 0;
    var aux_resp = [];

    console.log($scope.accountOptions);

    if (typeof $scope.accountOptions !== 'undefined' && $scope.accountOptions.length > 0) {

      console.log("qwertyui");

      $scope.aux_filters = 1;
      $scope.accountOptions.forEach(function (filter, key, array) {
        aux_resp.push(filter);
      });
    }

    console.log(aux_resp);

    $scope.auxScroll = aux_resp;

    var json = JSON.stringify($scope.auxScroll);
    jQuery('input[name="associated_accounts_value"]').attr('value', json);

  };
}
