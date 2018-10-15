myApp.directive('ngInvoiceDeliveryStatus', ['$http', '$q', ngInvoiceDeliveryStatus]);


function ngInvoiceDeliveryStatus($http,$q) {

  var directive = {
    restrict: 'EA',
    controller: InvoiceDeliveryStatusController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    var uuid = scope.uuid;
    var config = drupalSettings.billingDeliveryConfigBlock[uuid];

    retrieveInformation(scope, config, el, uuid);
    scope.apiIsLoading = function() {
      return $http.pendingRequests.length > 0;
    };

    scope.$watch(scope.apiIsLoading, function(v) {
      if (v == false) {
        if(scope.invoice_delivery_status.error){
          jQuery("div.actions", el).hide();
        }else{
          jQuery(el).parents("section").fadeIn(400);
        }
      }
    });
  }

  function retrieveInformation(scope, config, el, uuid) {

    if ( scope.resources.indexOf(config.url) == -1){
      scope.url = config.url;
      $http.get('/rest/session/token').then(function(resp) {

        $http({
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': resp.data
          },
          params: config,
          url: config.url
        }).then(function successCallback(response) {
          // this callback will be called asynchronously
          // when the response is available
          if ((typeof response.data.fields) !== 'undefined' == true){
            scope.invoice_delivery_status[uuid] = response.data;
            scope.printed_checked=true;
            if(config.block_config.show_invoice_include_details.editable === "1"){
              scope.class = 'true';
            }
            scope.city_model = response.data.info.city;
            scope.address_model = scope.invoice_delivery_status[uuid].fields.show_invoice_printed_address.value;
            if(scope.invoice_delivery_status[uuid].config.show_button_edit_invoice_electronic == 1){
              console.log('ya tiene factura electronica');
              scope.mail_model = scope.invoice_delivery_status[uuid].fields.show_invoice_electronic_email.value;
              scope.digital_old = true;
              scope.digital_checked=true;
            }
            if(scope.invoice_delivery_status[uuid].config.show_button_active_invoice_electronic == 1){
              console.log('no tiene factura electronica');
              scope.printed_old = true;

            }
          }else{
            scope.invoice_delivery_status.error = true;
            drupal_set_message(Drupal.t("En este momento no podemos obtener la <strong>información de tu factura electrónica</strong>s, intenta de nuevo mas tarde."), "error", uuid);
          }
          jQuery(el).parents("section").fadeIn('slow');
        }, function errorCallback(response) {
          console.log(response);
          console.log('error obteniendo el servicio metodo get');
          scope.invoice_delivery_status.error = true;
          drupal_set_message(Drupal.t("En este momento no podemos procesar tu solicitud, por favor intente más tarde"), "error", $scope.uuid);
        });
      });
    }
  }
}

InvoiceDeliveryStatusController.$inject = ['$scope','$http'];

function InvoiceDeliveryStatusController($scope,$http) {

  // Init vars
  if(  typeof $scope.invoice_delivery_status == 'undefined'){
    $scope.invoice_delivery_status = [];
  }
  if(  typeof $scope.invoice_delivery_status[$scope.uuid] == 'undefined'){
    $scope.invoice_delivery_status[$scope.uuid] = [];
  }
  if (typeof $scope.resources == 'undefined') {
    $scope.resources = [];
  }

  $scope.configureBill = function(){
    jQuery(".modal-close").trigger('click');
    if($scope.printed_checked !== 'undefined'){
      var printed_new = $scope.printed_checked;
    }
    if($scope.digital_checked !== 'undefined'){
      var digital_new = $scope.digital_checked;
    }
    if($scope.city_model !== 'undefined'){
      var city = $scope.city_model;
    }
    if($scope.address_model !== 'undefined'){
      var address = $scope.address_model;
    }
    if($scope.mail_model !== 'undefined'){
      var mail = $scope.mail_model;
    }

    if (typeof $scope.digital_old === 'undefined' && digital_new == true){
      var params = {
        "action" : "create",
        "mail" : mail
      };
      if ( $scope.resources.indexOf($scope.url) == -1){
        $http.get('/rest/session/token').then(function(resp) {
          $http({
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': resp.data
            },
            data: params,
            url: $scope.url
          }).then(function successCallback(response) {
            // this callback will be called asynchronously
            // when the response is available
           if(response.status == 200){
             console.log('post successfull');
             window.location.reload(true);
           }else{
             $scope.invoice_delivery_status.error = true;
             drupal_set_message(Drupal.t("En este momento no podemos procesar tu solicitud, por favor intente más tarde"), "error", $scope.uuid);
            }
            jQuery(el).parents("section").fadeIn('slow');
          }, function errorCallback(response) {
            console.log(response);
            console.log('error obteniendo el servicio metodo create');
            drupal_set_message(Drupal.t("En este momento no podemos procesar tu solicitud, por favor intente más tarde"), "error", $scope.uuid);
          });
        });
      }
    }

    if ($scope.digital_old == true && digital_new === false){
      var params = {
        "action" : "delete",
      };
      if ( $scope.resources.indexOf($scope.url) == -1){
        $http.get('/rest/session/token').then(function(resp) {
          $http({
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': resp.data
            },
            data: params,
            url: $scope.url
          }).then(function successCallback(response) {
            // this callback will be called asynchronously
            // when the response is available
            if (response.status == 200){
              console.log('delete successfull');
              window.location.reload(true);
            }else{
              $scope.invoice_delivery_status.error = true;
              drupal_set_message(Drupal.t("En este momento no podemos procesar tu solicitud, por favor intente más tarde"), "error", $scope.uuid);
            }
            jQuery(el).parents("section").fadeIn('slow');
          }, function errorCallback(response) {
            console.log(response);
            console.log('error obteniendo el servicio metodo delete');
            drupal_set_message(Drupal.t("En este momento no podemos procesar tu solicitud, por favor intente más tarde"), "error", $scope.uuid);
          });
        });
      }
    }

    if ($scope.digital_old == true && digital_new == true){
      if ( $scope.resources.indexOf($scope.url) == -1){
        $http.get('/rest/session/token').then(function(resp) {
          $http({
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': resp.data
            },
            data: params,
            url: $scope.url
          }).then(function successCallback(response) {
            // this callback will be called asynchronously
            // when the response is available
            if (response.status == 200){
              console.log('update successfull');
              window.location.reload(true);
            }else{
              $scope.invoice_delivery_status.error = true;
              drupal_set_message(Drupal.t("En este momento no podemos procesar tu solicitud, por favor intente más tarde"), "error", $scope.uuid);
            }
            jQuery(el).parents("section").fadeIn('slow');
          }, function errorCallback(response) {
            console.log(response);
            console.log('error obteniendo el servicio metodo put');
            drupal_set_message(Drupal.t("En este momento no podemos procesar tu solicitud, por favor intente más tarde"), "error", $scope.uuid);
          });
        });
      }
    }

  }
}
