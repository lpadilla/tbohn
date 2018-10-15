myApp.directive('ngLineasBo', ['$http', ngLineasBo]);

function ngLineasBo($http) {
	var directiveOpt = {
		restrict: 'EA',
		controller: GetLinesBoController,
		link: getLink
	};

	return directiveOpt;

	function getLink(scope, el) {
		var myConfigs = drupalSettings.lineasBoBlock[scope.uuid_data_ng_lineas_bo];
		var myConfigs1 = drupalSettings.lineasdetailBoBlock[scope.uuid_data_ng_lineas_bo];
		scope.id_user_tigo_admin = 0;
		scope.show_mesagge_data_talb = "";

		getRegister(myConfigs, myConfigs1);

		//Load Lines data
		function getRegister(myConfigs, myConfigs1) {
			//Define Params to load data
			var params = {
				fields: myConfigs.fields,
				config_pager: myConfigs.config_pager,
				opt: 1,
				limit_lines: myConfigs.limit_lines,
				num_contract_client: myConfigs.num_contract_client,
			};

			var config_data = {
		        params: params,
		        headers: {'Accept': 'application/json'}
	     	};
	     	

	     	//se llama al servicio para obtener las lineas
			$http.get(myConfigs.url, config_data)
	        .then(function (resp) {
	        	if (resp.data.error) {
		            scope.alerts(myConfigs.error_msg);
		          } else {
		          	scope.summary = resp.data;	           
					scope.cantLine 		=scope.summary.cant;
					scope.summaryDeuda	=scope.summary.deuda;
					scope.summaryContract=scope.summary.contrato;
				    scope.response = resp.data;
				    var num_lines = 10;//res.data.length;
				    //variable para el paginador
					var num_rows = myConfigs.config_pager['number_rows_pages'];
					scope.itemsPerPage = num_rows;
					//variable para el paginador
					var num_page = myConfigs.config_pager['number_pages'];
					gap = Math.floor(num_page);
					scope.gap = gap;
					scope.groupToPages();
					loadDetails(scope, myConfigs1, $http);	          		          	
		          }
	        }, function () {
          		scope.alerts(myConfigs.error_msg);
	        });			
		}
	}
}

GetLinesBoController.$inject = ['$scope', '$http', '$window'];

function loadDetails(scope, myConfigs1, $http){
    if(scope.cantLine <= 50){
    	scope.response.datos.forEach(function(line, index){   
            var params = {
				msisdn: line.msisdn,
			};

			var config_data = {
		        params: params,
		        headers: {'Accept': 'application/json'}
	     	};

	     	$http.get(myConfigs1.url, config_data)
	     	.then(function (resp) {
	        	if (resp.data.error) {
		            scope.alerts(myConfigs1.error_msg);
		        } else {
		          	jQuery("tr#"+line.msisdn+">td.plan_datos").text(resp.data.plan);
		          	jQuery("tr#"+line.msisdn+">td.add_ons").text(resp.data.addons);
		          	jQuery("tr#"+line.msisdn+">td.tele_group").text(resp.data.telegroup);		          	
		          	
		          	scope.response.datos[index].plan_datos = resp.data.plan;
		          	scope.response.datos[index].addons = resp.data.addons;
		          	scope.response.datos[index].telegroup = resp.data.telegroup;
					

		        }	
	        }, function () {
          		scope.alerts(myConfigs1.error_msg);
	        });	

        });
    }
}

function GetLinesBoController($scope, $http, $window) {
	$scope.pagedItems = [];
	$scope.currentPage = 0;

	//functions to paginate
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

	$scope.reverseTable = function () {
		$scope.response = $scope.response.reverse();
		$scope.groupToPages();
	};

	$scope.groupToPages = function () {
		$scope.pagedItems = [];
		for (var i = 0; i < $scope.response.datos.length; i++) {
			if (i % $scope.itemsPerPage === 0) {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.response.datos[i]];
			} else {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.response.datos[i]);
			}
		}
		$scope.currentPage = 0;		
	};

	//funcion llamada desde el template, para el funcionamiento del paginador
	$scope.hidePage = function(n){
		var start=$scope.currentPage-($scope.gap/2);
		if (start<0) start=0;
		var end = start+$scope.gap-2;
		if ((n >=start) && (n<=end+1)){
		 return false;
		}
		return true;		
	};

	//Show message service
  	$scope.alerts = function (show_mesagge_data_line) {
	    jQuery('.main-top').append(
	    	'<div class="messages clearfix messages--danger alert alert-danger">' 
	    	+ show_mesagge_data_line + 
	    	'</div>');	    
	};
}