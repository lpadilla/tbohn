myApp.directive('ngCompaniesList', ['$http', ngCompaniesList]);

function ngCompaniesList($http) {
  var directive = {
    restrict: 'EA',
    controller: CompaniesListController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_companies_list];
		scope.show_mesagge_data = "";
    retrieveInformation(scope, config, el);

    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };
    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        if (scope.companiesList.error) {
          jQuery("div.actions", el).hide();
        }
      }
    });

    scope.changeAdmin = function (event) {
      if (scope.adminType == 'create') {
        jQuery('#user-form').removeClass('hidden');
        jQuery('#user-form').addClass('user-form');
        jQuery('#user-form-old').removeClass('hidden');
        jQuery('#user-form-old').removeClass('user-form');
        jQuery('#user-form-old').addClass('hidden');
        jQuery('#user-form-new').removeClass('hidden');
        jQuery('#user-form-new').removeClass('user-form');
        jQuery('#user-form-new').addClass('user-form');
      }
      if (scope.adminType == 'associate') {
        jQuery('#user-form').removeClass('hidden');
        jQuery('#user-form').addClass('user-form');
        jQuery('#user-form-new').removeClass('hidden');
        jQuery('#user-form-new').removeClass('user-form');
        jQuery('#user-form-new').addClass('hidden');
        jQuery('#user-form-old').removeClass('hidden');
        jQuery('#user-form-old').removeClass('user-form');
        jQuery('#user-form-old').addClass('user-form');
      }
    }

    scope.suggestionsAjax = [];
    scope.selectedIndexAjax = -1;

    scope.searchMail=function(key_data){
      scope.dataSearch = scope[key_data];
      if(!scope[key_data] == '' || !scope[key_data] == 'undefined') {
        $http.get('/tbo_account/autocomplete/'+scope[key_data]+'?_format=json').success(function (data) {
          scope.suggestionsAjax=data;
          scope.selectedIndexAjax=-1;
        });
      } else {
        scope.suggestionsAjax = [];
      }
    }

    scope.checkKeyDownMail = function (event, field) {
      if (event.keyCode === 40) {//down key, increment selectedIndex
        event.preventDefault();
        if (scope.selectedIndexAjax + 1 !== scope.suggestionsAjax.length) {
          scope.selectedIndexAjax++;
        }
      }
      else if (event.keyCode === 38) { //up key, decrement selectedIndex
        event.preventDefault();
        if (scope.selectedIndexAjax - 1 !== -1) {
          scope.selectedIndexAjax--;
        }
      }
      else if (event.keyCode === 13) { //enter pressed
        scope.resultClickedAjax(scope.selectedIndexAjax, field);
      }
      else {
        scope.suggestionsAjax = [];
      }
    }
    scope.resultClickedAjax = function (index, field) {
      scope[field] = scope.suggestionsAjax[index].name;
      scope.suggestionsAjax = [];
    }


    scope.suggestions = [];
    scope.selectedIndex = -1; //currently selected suggestion index

    scope.search = function (key_data) {
      if (!scope[key_data] == '' || !scope[key_data] == 'undefined') {
        $http.get(config.url + '&autocomplete=' + scope[key_data]).success(function (data) {

          scope.suggestions = data;
          scope.selectedIndex = -1;
        });
      } else {
        scope.suggestions = [];
      }
    }
    scope.checkKeyDown = function (event, field) {
      if (event.keyCode === 40) {//down key, increment selectedIndex
        event.preventDefault();
        if (scope.selectedIndex + 1 !== scope.suggestions.length) {
          scope.selectedIndex++;
        }
      }
      else if (event.keyCode === 38) { //up key, decrement selectedIndex
        event.preventDefault();
        if (scope.selectedIndex - 1 !== -1) {
          scope.selectedIndex--;
        }
      }
      else if (event.keyCode === 13) { //enter pressed
        scope.resultClicked(scope.selectedIndex, field);
      }
      else {
        scope.suggestions = [];
      }
    }
    scope.resultClicked = function (index, field) {
      scope[field] = scope.suggestions[index].name;
      scope.suggestions = [];
    }

    scope.isItem = function (item) {
      if (isArray(item)) {
        return TRUE;
      }
    }
  }

  function retrieveInformation(scope, config, el) {
    if (scope.resources.indexOf(config.url) == -1) {
      //Add key for this display
      var parameters = {};

			parameters['config_columns'] = config.uuid;
      parameters['config_name'] = config.config_name;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      $http.get(config.url, config_data)
        .then(function (resp) {
					if (resp.data.error) {
						scope.show_mesagge_data = resp.data.message;
						scope.alertas_servicios_create_companies();
					} else {
						scope.companiesList = resp.data;
						var num_companies = scope.companiesList.length;
						var num_rows = config.config_pager['number_rows_pages'];
						scope.itemsPerPage = config.config_pager['number_rows_pages'];
						var gap = 1;
						gap += Math.floor(num_companies / num_rows);
						scope.gap = gap;
						scope.currentPage = 0;
						scope.groupToPages();
						jQuery(el).parents("section").fadeIn('slow');
          }
        }, function () {
          console.log("Error obteniendo los datos");
        });
    }
  }
}

CompaniesListController.$inject = ['$scope', '$http'];

function CompaniesListController($scope, $http) {
  // Init vars
  if (typeof $scope.companiesList == 'undefined') {
    $scope.companiesList = "";
    $scope.companiesList.error = false;
  }

  if (typeof $scope.resources == 'undefined') {
    $scope.resources = [];
  }

  $scope.groupedItems = [];
  $scope.pagedItems = [];
  $scope.currentPage = 0;

  //Function por filter info
  $scope.filterCompanies = function () {
    //Get config
    var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_companies_list];

    //Get value filters
    var parameters = {};
    for (filter in config['filters']) {
      if (!$scope[filter] == '' || !$scope[filter] === undefined) {
				parameters[filter] = $scope[filter];
      }
    }

		parameters['config_columns'] = config.uuid;
		parameters['config_name'] = config.config_name;

    //Add config to url
    var config_data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };

    //Get Data For Filters;
    $http.get(config.url, config_data)
      .then(function (resp) {
				if (resp.data.error) {
					$scope.show_mesagge_data = resp.data.message;
					$scope.alertas_servicios_create_companies();
				} else {
					$scope.companiesList = resp.data;
					var num_companies = $scope.companiesList.length;
					var num_rows = config.config_pager['number_rows_pages'];
					$scope.itemsPerPage = config.config_pager['number_rows_pages'];
					var gap = 1;
					gap += Math.floor(num_companies / num_rows);
					$scope.gap = gap;
					$scope.currentPage = 0;
					$scope.groupToPages();
				}
      }, function () {
        console.log("Error obteniendo los datos");
      });
  }

  $scope.orderReverse = function () {
    $scope.companiesList = $scope.companiesList.reverse();
    $scope.groupToPages();
  }

  // calculate page in place
  $scope.groupToPages = function () {
    $scope.pagedItems = [];
    for (var i = 0; i < $scope.companiesList.length; i++) {
      if (i % $scope.itemsPerPage === 0) {
        $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.companiesList[i]];
      } else {
        $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.companiesList[i]);
      }
    }
  };

  $scope.range = function (size, start, end) {
    var ret = [];
    if (size < end) {
      end = size;
    }
    for (var i = 0; i < end; i++) {
      ret.push(i);
    }
    return ret;
  };

  $scope.prevPage = function () {
    if ($scope.currentPage > 0) {
      $scope.currentPage--;
    }
  };

  $scope.nextPage = function () {
    if ($scope.currentPage < $scope.pagedItems.length - 1) {
      $scope.currentPage++;
    }
  };

  $scope.setPage = function () {
    $scope.currentPage = this.n;
  };

  //Declare vars and function for ordering
  $scope.predicate = 'attraction';
  $scope.reverse = false;
  $scope.order = function (predicate) {
    $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
    $scope.predicate = predicate;
  };

	//Show message service
	$scope.alertas_servicios_create_companies = function () {
		jQuery(".block-create-company-message .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
		$html_mensaje = jQuery('.block-create-company-message .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery(".block-create-company-message .messages-only .text-alert .txt-message").remove();

		jQuery('.messages .close').on('click', function() {
			jQuery('.messages').hide();
		});
	}

	jQuery('.click-filter-reset').click(function () {
	  //reset filters
		var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_companies_list];

		//Get value filters
		var parameters = {};
		for (filter in config['filters']) {
			$scope[filter] = '';
		}

		$scope.filterCompanies();
	});
}
