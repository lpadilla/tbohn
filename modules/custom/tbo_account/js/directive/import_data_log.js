myApp.directive('ngImportDataLog', ['$http', ngImportDataLog]);


function ngImportDataLog($http) {
  var directive = {
    restrict: 'EA',
    controller: ImportDataLogController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
   var config = drupalSettings.b2bBlock[scope.uuid];
   scope.config = [];
   retrieveInformation(scope, config, el);
  }

  function retrieveInformation(scope, config, el) {
    var fields = config.key_fields;
    var parameters = {};

    parameters = {
      params: {
        type:'retrieve',
        fields: fields.join(';')
      },
      headers: {'Accept': 'application/json'}
    };

    //Get data
    $http.get(config.url, parameters).then(function(response) {
      scope.auditLogs = response.data;
      var num_logs = scope.auditLogs.length;
      var num_rows = config.config_pager;
      scope.itemsPerPage = config.config_pager;
      var gap = 1;
      gap += Math.floor(num_logs / num_rows);
      scope.gap = gap;
      scope.groupToPages();
    }, function () {
      console.log('Error al obtener los datos');
    });

  }
}

ImportDataLogController.$inject = ['$scope', '$http'];

function ImportDataLogController($scope, $http) {

  $scope.filterLog = function (custom_id, status) {

    var config = drupalSettings.b2bBlock[$scope.uuid];
    var fields = config.key_fields;
    var parameters = {};
    parameters['fields'] = fields.join(';');
    parameters['type'] = 'filter';

    if (status !== undefined) {
      if(status.indexOf('all') === 0 && (custom_id === undefined || custom_id == '')) {
        parameters['type'] = 'retrieve';
      } else if(status.indexOf('all') === 0 && (custom_id !== undefined || custom_id != '')) {
        parameters['status'] = 'fallo;exitoso;error';
      } else {
        parameters['status'] = status.join(';');
      }
    }

    if (custom_id != '' && custom_id !== undefined) {
      parameters['custom_id'] = custom_id;
    }

    if(custom_id === undefined && (status === undefined || status.length < 1)) {
      parameters['type'] = 'retrieve';
    }

    var data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };

    //Get filtered data
    $http.get(config.url, data).then(
      function (response) {
        $scope.auditLogs = response.data;
        $scope.groupToPages();
      }, function () {
        console.log('Error obteniendo datos');
      }
    );
  };

  $scope.clear = function() {
    $scope.status = '';
    $scope.custom_id = '';
  };

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
    window.open('/adf_core/export/'+type_export+'/audit/'+$scope.filter_status+'/'+$scope.uuid, '_blank');
  };

    //Reser filters
    jQuery('.click-filter-reset').click(function () {
        //reset filters
        var config = drupalSettings.b2bBlock[$scope.uuid];

        //Get value filters
        var parameters = {};
        for (filter in config['filters']) {
            $scope[filter] = '';
        }

			  //Clear multiselect
        clear()
        $scope.filterLog();
    });

	function clear() {
		setTimeout(function () {
			$scope.$apply(function () {
				if($scope.hasOwnProperty('status')) {
					$scope['status'] = [];
					jQuery('.select-dropdown li').removeClass('selected');
					jQuery('#status').material_select();
				}
			});
		}, 100);
	}
}
