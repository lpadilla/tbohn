myApp.directive('ngAuditLogsList', ['$http', ngAuditLogsList]);

function ngAuditLogsList($http) {
  var directive = {
    restrict: 'EA',
    controller: AuditLogsController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    scope.filter_status = 1;
    var config = drupalSettings.auditLogsBlock[scope.uuid_data_ng_audit_logs_list];
		scope.show_mesagge_data_logs = "";

    retrieveInformation(scope, config, el);
    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };
    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        if (scope.auditLogs.error) {
          jQuery("div.actions", el).hide();
        }
      }
    });
  }

  function retrieveInformation(scope, config, el) {
    if (scope.resources.indexOf(config.url) == -1) {

      $http.get('/rest/session/token').then(function(resp) {
        //console.log(resp.data);
        var parameters = {};
				parameters['config_columns'] = config.uuid;
				parameters['config_name'] = config.config_name;
        //console.log(config.config_columns);
        $http({
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json' ,
            'X-CSRF-Token': resp.data
          },
          data: parameters,
          url: config.url
        }).then(function successCallback(response) {
          // this callback will be called asynchronously
					if (response.data.error) {
						scope.show_mesagge_data_logs = response.data.message;
						scope.alertas_servicios_logs();
					}
					else {
						// when the response is available
						scope.auditLogs = response.data;
						var num_logs = scope.auditLogs.length;
						var num_rows = config.config_pager['number_rows_pages'];
						scope.itemsPerPage = config.config_pager['number_rows_pages'];
						var gap = 1;
						gap += Math.floor(num_logs / num_rows);
						scope.gap = gap;
						scope.groupToPages();
					}
        }, function errorCallback(response) {
          // called asynchronously if an error occurs
          // or server returns response with an error status.
          console.log('error obteniendo el servicio metodo post');
        });
      });
    }
  }

}

AuditLogsController.$inject = ['$scope', '$http'];

function AuditLogsController($scope, $http) {

  // Init vars
  if (typeof $scope.auditLogs == 'undefined') {
    $scope.auditLogs = "";
    $scope.auditLogs.error = false;
  }

  if (typeof $scope.resources == 'undefined') {
    $scope.resources = [];
  }

  $scope.groupedItems = [];
  $scope.pagedItems = [];
  $scope.currentPage = 0;

  $scope.filterLogs = function () {
		$scope.filter_status = 1;

    //Get config
    var config = drupalSettings.auditLogsBlock[$scope.uuid_data_ng_audit_logs_list];

    //Get value filters
    var package = {};

    for (filter in config['filters']) {
      if(filter == "created"){
				//Vars for dateStart
				var dateStart = jQuery('#date_start_log').val();
				var validateStart = false;
				var valueScopeStart = false;

      	//Vars for dateEnd
      	var dateEnd = jQuery('#date_end_log').val();
      	var validateEnd = false;
      	var valueScopeEnd = false;

      	//Logic dateStart
				if (typeof dateStart !== 'undefined' && dateStart != '' && dateStart != null) {
					validateStart = true;
				}

				if (typeof $scope['date_start'] !== 'undefined' && $scope['date_start'] != '' && $scope['date_start'] != null) {
					valueScopeStart = true;
				}

				//Logic dateEnd
				if (typeof dateEnd !== 'undefined' && dateEnd != '' && dateEnd != null) {
					validateEnd = true;
				}

				if (typeof $scope['date_end'] !== 'undefined' && $scope['date_end'] != '' && $scope['date_end'] != null) {
					valueScopeEnd = true;
				}

				//Validate dateStart
        if (valueScopeStart == true || validateStart == true) {
					if (valueScopeStart == false) {
						var date_start = $scope.replaceMonth(dateStart);
					} else {
						var date_start = $scope.replaceMonth($scope['date_start']);
					}
          package['date_start'] = Date.parse(date_start);
					//Add to filter created
					$scope.filter_status = 2;
        } /*else {
					if (valueScopeEnd == true || validateEnd == true) {
						package['date_start'] = jQuery("#date_start_log_table .picker__day--selected").attr("data-pick");
					}
				}*/

        if (valueScopeEnd == true || validateEnd == true) {
					if (valueScopeEnd == false) {
						var date_end = $scope.replaceMonth(dateEnd);
					} else {
						var date_end = $scope.replaceMonth($scope['date_end']);
					}
          package['date_end'] = Date.parse(date_end);
					//Add to filter created
					$scope.filter_status = 2;
        } /*else {
					if (valueScopeStart == true || validateStart == true) {
						package['date_end'] = jQuery("#date_end_log_table .picker__day--selected").attr("data-pick");
					}
        }*/
      }

      if (!$scope[filter] == '' || !$scope[filter] === undefined) {
        package[filter] = $scope[filter];
        $scope.filter_status = 2;
      }
    }

		package['config_columns'] = config.uuid;
		package['config_name'] = config.config_name;

    $http.get('/rest/session/token').then(function(resp) {
      //Get Data For Filters;
      $http({
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json' ,
          'X-CSRF-Token': resp.data
        },
        data: package,
        url: config.url
      }).then(function successCallback(response) {
				// this callback will be called asynchronously
				if (resp.data.error) {
					scope.show_mesagge_data_logs = response.data.message;
					scope.alertas_servicios_logs();
				}
				else {
					// when the response is available
					$scope.auditLogs = response.data;
					var num_logs = $scope.auditLogs.length;
					var num_rows = config.config_pager['number_rows_pages'];
					$scope.itemsPerPage = config.config_pager['number_rows_pages'];
					var gap = 1;
					gap += Math.floor(num_logs / num_rows);
					$scope.gap = gap;
					$scope.groupToPages();
				}
      }, function errorCallback(response) {
        // called asynchronously if an error occurs
        // or server returns response with an error status.
        console.log('error obteniendo el servicio metodo post');
      });
    });
  }

  $scope.orderReverse = function (){
    $scope.auditLogs = $scope.auditLogs.reverse();
    $scope.groupToPages();
  }

  // calculate page in place
  $scope.groupToPages = function () {
    $scope.pagedItems = [];
    for (var i = 0; i < $scope.auditLogs.length; i++) {
      if (i % $scope.itemsPerPage === 0) {
        $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.auditLogs[i]];
      } else {
        $scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.auditLogs[i]);
      }
    }
    $scope.currentPage = 0;
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


  $scope.exportData = function(type_export){
    window.open('/adf_core/export/'+type_export+'/audit/'+$scope.filter_status+'/'+$scope.uuid_data_ng_audit_logs_list, '_blank');
  };

  //Replace spanish months to english
  $scope.replaceMonth = function (date) {
    var month_search = {};
    var new_date = '';

    month_search['Enero'] = 'January';
    month_search['Febrero'] = 'February';
    month_search['Marzo'] = 'March';
    month_search['Abril'] = 'April';
    month_search['Mayo'] = 'May';
    month_search['Junio'] = 'Jun';
    month_search['Julio'] = 'July';
    month_search['Agosto'] = 'August';
    month_search['Septiembre'] = 'September';
    month_search['Octubre'] = 'October';
    month_search['Nobiembre'] = 'November';
    month_search['Diciembre'] = 'December';

    for(var key in month_search) {
      if(date.search(key) !== -1) {
        new_date = date.replace(key, month_search[key]);
        return new_date;
      }
    }

    return date;
  };

  //Clean filters if model exists
  $scope.cleanValues = function() {
    if($scope.hasOwnProperty('date_start')) {
      $scope['date_start'] = '';
			jQuery('#date_start_log').val("");
    }

    if($scope.hasOwnProperty('date_end')) {
      $scope['date_end'] = '';
			jQuery('#date_end_log').val("");
    }

    if($scope.hasOwnProperty('company_name')) {
      $scope['company_name'] = '';
    }

    if($scope.hasOwnProperty('user_names')) {
      $scope['user_names'] = '';
    }

    if($scope.hasOwnProperty('company_segment')) {
      $scope['company_segment'] = '';
    }

    if($scope.hasOwnProperty('user_role')) {
      $scope['user_role'] = [];
    }

    if($scope.hasOwnProperty('description')) {
      $scope['description'] = '';
    }

    if($scope.hasOwnProperty('details')) {
      $scope['details'] = '';
    }

    //Reset multiselect
		clear();
    //Reset values
		$scope.filterLogs();
  };

  function clear() {
		setTimeout(function () {
			$scope.$apply(function () {
				if($scope.hasOwnProperty('user_role')) {
					$scope['user_role'] = [];
					jQuery('.select-dropdown li').removeClass('selected');
					jQuery('#user_role').material_select();
				}
			});
		}, 100);
	}

	//Show message service
	$scope.alertas_servicios_logs = function () {
		jQuery(".block-audit-logs .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_logs + '</p></div>');
		$html_mensaje = jQuery('.block-audit-logs .messages-only').html();
		jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

		jQuery(".block-audit-logs .messages-only .text-alert .txt-message").remove();

		jQuery('.messages .close').on('click', function() {
			jQuery('.messages').hide();
		});
	}

}
