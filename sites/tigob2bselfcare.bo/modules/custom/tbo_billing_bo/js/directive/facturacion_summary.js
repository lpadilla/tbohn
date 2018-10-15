myApp.directive('ngFacturacionSummary', ['$http', ngFacturacionSummary]);


function ngFacturacionSummary($http) {
  var directive = {
    restrict: 'EA',
    controller: FacturacionSummaryController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_facturacion_summary];
    retrieveInformation(scope, config, el);
    var orderName = 0;
    var orderAdmin = 0;

    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };
    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        if (typeof scope.summary !== 'undefined') {
          if (scope.summary.error) {
            jQuery("div.actions", el).hide();
          }
        }
      }
    });
  }

  function loadFacturacion (scope, config){

    //asingar el valor del client code como parametro para llamar a servicio
      
      if(Array.isArray(scope.clients)){
        var parameters = {
          client:  scope.clients[scope.cicle],
          type: config.type,
          config_columns: config.config_columns,
        };
      }else{
        var parameters = {
          client:  scope.clients,
          type: config.type,
          config_columns: config.config_columns,
        };
      }

      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };

      $http.get(config.url, config_data)
        .then(function (resp) {

          if (resp.data.error) {
            scope.show_mesagge_data_billing_sumary_movil = resp.data.message;
            scope.alertas_servicios_movil();
            
            scope.cantids--;
            if(scope.cantids>0){
              return loadFacturacion(scope, config);
            }

          } else {
            scope.cicle++;
            if(scope.resul_summary==""){
              scope.resul_summary = resp.data;
              
              scope.uno=1;                        
              
            }else{
              var res1="";
              var res2="";
              var totalsum=0;
              var simbol="";
              var str1 = scope.resul_summary[0].ammount;
              var str2 = resp.data[0].ammount;
              if(str1=="")
                res1=0;
              else{
                
                res1=scope.resul_summary[0].ammount;
                
              }

              // Evaluar si existe datos en la respuesta del servicio y hacer la suma respectiva 
              // cambiando los datos de string a float
              if(str2=="")
                res2 = 0;
              else{
                res2 = str2;
              }

              if(str2=="" && str1!=""){
                totalsum = parseFloat(res1);
              }
              else{
                if(str2=="" && str1=="")
                  totalsum = res1 + res2;
                else{
                  if(str2!="" && str1==""){
                    totalsum = 0 + parseFloat(res2);
                  }
                  else{
                    totalsum = parseFloat(res1) + parseFloat(res2);
                  }  
                    
                }
              }

              if(resp.data[0].facturas=="")
                resp.data[0].facturas=0;
              
              scope.resul_summary[0].ammount= totalsum.toFixed(2); //sumatoria deuda
              scope.resul_summary[0].facturas+=resp.data[0].facturas; // sumatoria de cantidad de facturas

            }

            
            //Set segment track

            scope.segmet_value = scope.resul_summary.segment_amount;
            if(scope.segmet_value !== undefined || scope.segmet_value != '') {
              jQuery(".segment-load.movil").attr('data-segment-load', 1);
            }
          }
          /* Verificar si quedan client code por ejecutar */
            scope.cantids--;
            if(scope.cantids>0){
              return loadFacturacion(scope, config);
            }else{
              // Si ya no existen client code a verificar, se configura los decimales por coma
              var hasformat=0;
              var number=scope.resul_summary[0].ammount.toString();
              var cant_split=number.split(".");
              number=number.replace(/\./g, ',');
              if(cant_split[0].length>3)
                number=number.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
              else{
                if(cant_split[0].length=0 || number==""){
                  number="0,00";
                  scope.resul_summary[0].facturas=0;
                  hasformat=1;
                }
                else    
                  number=number;
              }

              var numberdecimal=parseFloat(scope.resul_summary[0].ammount.toString());
              
              // Debido a que puede que no se tenga valor de sumatoria o por la transformacion no haydecimales
              // se ha de notificar que siempre se muestre 2 decimales como formato, por ello en ese caso se agrega
              var result = (numberdecimal - Math.floor(numberdecimal)) !== 0; 
              if(!result && hasformat==0){
                number= number+",00";
              }
              // Por defecto se agrega la moneda cableada
              scope.resul_summary[0].ammount= "Bs "+number;
              scope.summary=scope.resul_summary; 
            }


        }, function () {
          //mensaje a enviar en caso de error
          scope.show_mesagge_data_billing_sumary_movil = "Error obteniendo los datos de Facturación del cliente";
          scope.alertas_servicios_movil();
        });

    return 1;
  }

  function retrieveInformation(scope, config, el) {

    if (scope.resources.indexOf(config.url) == -1) {
      //Add key for this display
      var parameters = {};
      parameters['type'] = config.type;
      parameters['config_columns'] = config.config_columns;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      
      /* conocer cuantos client_code existen*/
      scope.clients=config.clients;
      if(Array.isArray(scope.clients)){
        scope.cantids=config.clients.length;
      }else{
        scope.cantids=1;
      }
      var carga = '[{"ammount":"Cargando.."}]'
      scope.summary=JSON.parse(carga);
      

     
      /* variables para manejar en la recursividad, gracias al scope*/
      
      scope.cicle         =0; 
      scope.resul_summary="";
      scope.uno=0;

      /* Llamada a la funcion recursiva*/
      scope.respu= loadFacturacion(scope, config); 
      
    }
  }
}

FacturacionSummaryController.$inject = ['$scope', '$http'];

function FacturacionSummaryController($scope, $http) {
  
  if (typeof $scope.adminCompany == 'undefined') {
    $scope.adminCompany = "";
    $scope.adminCompany.error = false;
  }

  if (typeof $scope.resources == 'undefined') {
    $scope.resources = [];
  }


  //Declare vars and function for ordering
  $scope.predicate = 'attraction';
  $scope.reverse = false;
  $scope.order = function (predicate) {
    $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
    $scope.predicate = predicate;
  };

  //Show message service
  $scope.alertas_servicios_movil = function () {
    
    jQuery(".block-billing-summary-message .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_billing_sumary_movil + '</p></div>');
    $html_mensaje = jQuery('.block-billing-summary-message .messages-only ').html();
    jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" >' +  $scope.show_mesagge_data_billing_sumary_movil + '</div>');

    jQuery(".block-billing-summary-message .messages-only .text-alert .txt-message").remove();

    jQuery('.messages .close').on('click', function() {
      jQuery('.messages').hide();
    });
  }

}