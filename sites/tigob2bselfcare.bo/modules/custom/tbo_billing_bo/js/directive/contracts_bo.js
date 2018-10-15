myApp.directive('ngContractsBo', ['$http', ngContractsBo]);

function ngContractsBo($http) {
	var directiveOpt = {
		restrict: 'EA',
		controller: GetUserListController,
		link: getLink
	};

	return directiveOpt;

	function getLink(scope, el) {
		var myConfigs =  drupalSettings.b2bBlock[scope.uuid_data_ng_contracts_bo];
		scope.id_user_tigo_admin = 0;
		scope.show_mesagge_data_talb = "";

		getRegister(myConfigs);

		//Load Tigo Admins data
		function getRegister(myConfigs) {
			//Define Params to load data
			var params = {
				fields: myConfigs.fields,
				config_pager: myConfigs.config_pager,
				opt: 1,
				limit_lines: myConfigs.limit_lines,
				type: myConfigs.type,
				cant_contratos: myConfigs.cant_contratos,
			};

			var config_data = {
		        params: params,
		        headers: {'Accept': 'application/json'}
		    };

		    /* conocer cuantos client_code existen*/
			scope.clients_contratos=myConfigs.clients;
			if(Array.isArray(scope.clients_contratos)){
			scope.cantids_contracts=myConfigs.clients.length;
			}else{
			scope.cantids_contracts=1;
			}
		  
		  	scope.invoices="1"; //iniciar vacio el bloque de contrtos
		  	scope.cicle_contract =0;  // Inicializacion en cero para ciclar arreglo de clientes
		  	scope.longi=0;

		  /* Llamada a la funcion recursiva*/
      		scope.respu= loadContratos(scope, myConfigs); 
		  

		}
	}

	function loadContratos(scope, myConfigs){

    //asingar el valor del client code como parametro para llamar a servicio
      
	    if(Array.isArray(scope.clients_contratos)){
	        var parameters = {
	          	client:  scope.clients_contratos[scope.cicle_contract],
	          	fields: myConfigs.fields,
				config_pager: myConfigs.config_pager,
				opt: 1,
				limit_lines: myConfigs.limit_lines,
				type: myConfigs.type,
				cant_contratos: myConfigs.cant_contratos,
				total_contracts: scope.longi,
	        };
	    }else{
	        var parameters = {
	          	client:  scope.clients_contratos,
	          	fields: myConfigs.fields,
				config_pager: myConfigs.config_pager,
				opt: 1,
				limit_lines: myConfigs.limit_lines,
				type: myConfigs.type,
				cant_contratos: myConfigs.cant_contratos,
				total_contracts: scope.longi,
	        };
	    }

		var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
    	}


	    $http.get(myConfigs.url, config_data)
	        .then(function (resp) {
	        	if (resp.data.error) {
		            scope.show_mesagge_data = resp.data.message;
		            scope.alertas_contrato_movil();
		            
		            scope.cantids_contracts--;
		            if(scope.cantids_contracts>0){
		              return loadContratos(scope, myConfigs);
		            }
		        }
		        else {
		        	
		        	scope.cicle_contract++;
		        	if(scope.invoices=="1"){
		        	// respuesta del servicio del contrato y se asigna a variable que se usa en el template
		              scope.invoices = resp.data; 

		            }else{
		            	// si son mas de un contrato, se va concatenando el resultado
		            	scope.invoices=scope.invoices.concat(resp.data);

		            }

		            if(Array.isArray(scope.invoices)){
		          		scope.longi =scope.invoices.length;
		          	}else{
		          		scope.longi +=1;
		          	}
				   
				}

				/* Verificar si quedan client code por ejecutar */
	            scope.cantids_contracts--;
	            if(scope.cantids_contracts>0){
	              return loadContratos(scope, myConfigs); // volver a ejecutar funcion con siguiente clentcode
	            }else{
	              scope.invoices=scope.invoices;

	            }

	        }, function () {
	          	scope.show_mesagge_data_contracts_movil = "Error obteniendo los datos de Contratos";
	      		scope.alertas_contrato_movil();
	        });


    }  
	

}

GetUserListController.$inject = ['$scope', '$http', '$window'];

function GetUserListController($scope, $http, $window) {

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
		for (var i = 0; i < $scope.response.length; i++) {
			if (i % $scope.itemsPerPage === 0) {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)] = [$scope.response[i]];
			} else {
				$scope.pagedItems[Math.floor(i / $scope.itemsPerPage)].push($scope.response[i]);
			}
		}
		$scope.currentPage = 0;
	};

	//Show message service error
  $scope.alertas_contrato_movil = function () {
    jQuery(".block-billing-summary-message .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_contracts_movil + '</p></div>');
    $html_mensaje = jQuery('.block-billing-summary-message .messages-only ').html();
    jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger">' +  $scope.show_mesagge_data_contracts_movil + '</div>');

    jQuery(".block-billing-summary-message .messages-only .text-alert .txt-message").remove();

    jQuery('.messages .close').on('click', function() {
      jQuery('.messages').hide();
    });
  }


}