myApp.directive('ngEmulateSession', ['$http', ngEmulateSession]);


function ngEmulateSession($http) {
  var directive = {
    restrict: 'EA',
    controller: EmulateSessionController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    var config = drupalSettings.emulateAdminCompanySessionBlock[scope.uuid_data_ng_emulate_session];

    //Get data
    retrieveInformation(scope, config, el);

    var orderName = 0;
    var orderAdmin = 0;

    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };
    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        if (scope.adminCompany.error) {
          jQuery("div.actions", el).hide();
        }
      }
    });

    scope.columnReverse = function (item) {
      var aux_resp = scope.auxAdminCompany;
      if (item == 'name' && orderName == 0){
        aux_resp.sort(function(a, b){
          if(a.name < b.name) return -1;
          if(a.name > b.name) return 1;
          return 0;
        })
        orderName = 1;
      }else if (item == 'name' && orderName == 1){
        aux_resp.sort(function(a, b){
          if(a.name > b.name) return -1;
          if(a.name < b.name) return 1;
          return 0;
        })
        orderName = 0;
      }

      if (item == 'full_name' && orderAdmin == 0 ){
        aux_resp.sort(function(a, b){
        	const stringa = a.admin_company[0].full_name;
        	const stringb = b.admin_company[0].full_name;
					//Validate full_name null or empty
					if (stringa != '' && stringa != null && stringb != '' && stringb != null) {
						if(stringa.normalize('NFD').replace(/[\u0300-\u036f]/g, "") > stringb.normalize('NFD').replace(/[\u0300-\u036f]/g, "")) return -1;
						if(stringa.normalize('NFD').replace(/[\u0300-\u036f]/g, "") < stringb.normalize('NFD').replace(/[\u0300-\u036f]/g, "")) return 1;
						return 0;
					}
        })
        orderAdmin = 1;
      }else if (item == 'full_name' && orderAdmin == 1 ){
        aux_resp.sort(function(a, b){
					const stringa = a.admin_company[0].full_name;
					const stringb = b.admin_company[0].full_name;
					//Validate full_name null or empty
					if (stringa != '' && stringa != null && stringb != '' && stringb != null) {
						if(stringa.normalize('NFD').replace(/[\u0300-\u036f]/g, "") < stringb.normalize('NFD').replace(/[\u0300-\u036f]/g, "")) return -1;
						if(stringa.normalize('NFD').replace(/[\u0300-\u036f]/g, "") > stringb.normalize('NFD').replace(/[\u0300-\u036f]/g, "")) return 1;
						return 0;
					}
        })
        orderAdmin = 0;
      }

      scope.auxAdminCompany = aux_resp;
      scope.groupToPages();
    }

    scope.auxAdminCompany = [];

    scope.filterAdmin = function () {
      var adminCompany = scope.adminCompany;
      var aux = [];
      var aux_resp = [];

      if (typeof scope.name !== 'undefined' && scope.name.length > 0){
        adminCompany.forEach(function (item, key, array) {
        	if (item.name != null) {
						aux_company = item.name.toUpperCase();
						resp = aux_company.search(scope.name.toUpperCase());
						if (resp > -1){
							aux_resp.push(item);
						}
					}
        })
      }else {
        aux_resp = adminCompany;
      }

      if (typeof scope.full_name !== 'undefined' && scope.full_name.length > 0){
        aux_filter = scope.full_name.toUpperCase();
        aux_resp.forEach(function (item, key, array) {
          if (item.admin_company.length == 1){
          	if (item.admin_company[0].full_name != null) {
							aux_admin = item.admin_company[0].full_name.toUpperCase();
							resp = aux_admin.search(aux_filter);

							if (resp > -1) {
								aux.push(item);
							}
						}
          }
          if (item.admin_company.length > 1){
            item.admin_company.forEach(function (element, index, array1) {
							if (element.full_name != null) {
								aux_admin = element.full_name.toUpperCase();
								resp = aux_admin.search(aux_filter);

								if (resp > -1) {
									if (jQuery.inArray(item, aux) == -1) {
										aux.push(item);
									}
								}
							}
            })
          }
        })
        aux_resp = aux;
        aux = [];
      }

      scope.auxAdminCompany = aux_resp;
      aux_resp = [];
      scope.currentPage = 0;
      scope.groupToPages();
    }

		scope.cleanValues = function() {
			scope.name = '';
			scope.full_name = '';

			//Reset values
			scope.filterAdmin();
		};
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
          scope.adminCompany = resp.data;
          scope.auxAdminCompany = scope.adminCompany;
          var num_companies = scope.adminCompany.length;
          var num_rows = config.config_pager['number_rows_pages'];
          scope.itemsPerPage = num_rows;
          var gap = 1;
          gap += Math.floor(num_companies / num_rows);
          scope.gap = gap;
          scope.groupToPages();
        }, function () {
          console.log("Error obteniendo los datos");
        });
    }
  }
}

EmulateSessionController.$inject = ['$scope', '$http'];

function EmulateSessionController($scope, $http) {
  // Init vars
  if (typeof $scope.adminCompany == 'undefined') {
    $scope.adminCompany = "";
    $scope.adminCompany.error = false;
  }

  if (typeof $scope.resources == 'undefined') {
    $scope.resources = [];
  }
	$scope.orderByProperty = function (propertyName, event) {

		if (propertyName == 'name') {
			propertyName = 'full_name';
		}
		if (propertyName == 'roles_target_id') {
			propertyName = 'user_role';
		}

		if (jQuery(event.target).hasClass('asc')) {
			jQuery(event.target).removeClass('asc').addClass('desc');
			$scope.usersList = $scope.usersList.sort(function (a, b) {
				if (propertyName == 'company_name') {
					var o1 = a[propertyName][0] || '';
					var o2 = b[propertyName][0] || '';
				} else {
					var o1 = a[propertyName] || '';
					var o2 = b[propertyName] || '';
				}
				o1 = o1.toLowerCase();
				o2 = o2.toLowerCase();
				if (o1 < o2) return 1;
				if (o1 > o2) return -1;

				return 0;
			});

		} else {
			jQuery(event.target).removeClass('desc').addClass('asc');
			$scope.usersList = $scope.usersList.sort(function (a, b) {
				if (propertyName == 'company_name') {
					var o1 = a[propertyName][0] || '';
					var o2 = b[propertyName][0] || '';
				} else {
					var o1 = a[propertyName] || '';
					var o2 = b[propertyName] || '';
				}
				o1 = o1.toLowerCase();
				o2 = o2.toLowerCase();
				if (o1 < o2) return -1;
				if (o1 > o2) return 1;
				return 0;
			});
		}
		$scope.groupToPages();
	}


  $scope.groupedItems = [];
  $scope.pagedItems = [];
  $scope.currentPage = 0;

  $scope.groupToPages = function () {
    $scope.pagedItems = [];
    for (var i = 0; i < $scope.auxAdminCompany.length; i++) {
      if (i % $scope.itemsPerPage === 0) {
        $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.auxAdminCompany[i]];
      } else {
        $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.auxAdminCompany[i]);
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



  //Reset filters
	jQuery('.btn-clear').click(function () {
		//reset filters
	
		jQuery('.input-field label').addClass('active');		
	
	});

}