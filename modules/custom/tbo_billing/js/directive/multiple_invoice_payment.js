myApp.directive('ngMultipleInvoicePayment', ['$http', ngMultipleInvoicePayment]);

function ngMultipleInvoicePayment($http) {

  var directive = {
    restrict: 'EA',
    controller: ngMultipleInvoicePaymentController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.MultipleInvoicePaymentBlock[scope.uuid_data_ng_multiple_invoice_payment];
    scope.table_fields = config.table;
    aux_table_fields = [];
    scope.invoices_collection = [];
    scope.data_cache = config.data_cache_payment;
    scope.cache_status = 0;

    for (var key in scope.table_fields) {
      if (scope.table_fields[key]['show'] == 1) {
        aux_table_fields [key] = scope.table_fields[key];
      }
    }

    scope.table_fields = aux_table_fields;
    scope.status_scramble = false;
    scope.total_value = '';
    scope.table_fields_mobile = config.table_mobile;
    scope.setOrigin(config.pay_from_summary);
    scope.old_value = '';
  }

  ngMultipleInvoicePaymentController.$inject = ['$scope', '$http', '$rootScope', 'multipleInvoices'];

  function ngMultipleInvoicePaymentController($scope, $http, $rootScope, multipleInvoices) {

    $scope.returnInvoicesToPay = function () {
      $scope.type_invoices = multipleInvoices.getType();
      $scope.invoices_collection = [];
      $scope.invoices_collection_mobile = [];


      if ($scope.data_cache && $scope.cache_status == 0){
        multipleInvoices.setObject($scope.data_cache.data);
        $rootScope.$emit("changeButtonByCache", multipleInvoices.getObject().data);
        $scope.cache_status = 1;
      }

      $scope.invoicesToPayResponse = multipleInvoices.getObject().data;

      $scope.type_texts = multipleInvoices.getString();
      keys_to_show = Object.keys($scope.table_fields);
      close_value = $scope.table_fields['close']['label'];
      $scope.total_value = '';
      total_value = 0;

      $scope.invoicesToPayResponse.forEach(function (item, key, array) {
        if (parseInt(item.invoice_value) > 0) {
					aux_invoice = [];
					aux_invoice_mobile = [];
					total_value = total_value + parseInt(item.invoice_value);

					for (i = 0; i < keys_to_show.length; i++) {
						if (keys_to_show[i] == 'close') {
							aux_invoice[keys_to_show[i]] = close_value;
						} else {
							aux_invoice[keys_to_show[i]] = item[keys_to_show[i]];
						}
					}

					for (i = 0; i < $scope.table_fields_mobile.length; i++) {
						if ($scope.table_fields_mobile[i] == 'close') {
							aux_invoice_mobile['close'] = close_value;
						} else {
							aux_invoice_mobile[$scope.table_fields_mobile[i]] = item[$scope.table_fields_mobile[i]];
						}
					}
					$scope.invoices_collection.push(Object.assign({}, aux_invoice));
					$scope.invoices_collection_mobile.push(Object.assign({}, aux_invoice_mobile));
        }
      });

      $scope.status_button = (total_value > 0) ? 1 : 0;

      if ($scope.status_button == 0) {
        jQuery("#body-payment-box").addClass('hide-table');
      }
      $scope.total_value_resp = total_value;
      $scope.amount_invoices = $scope.invoices_collection.length;
      var parameters = {};
      parameters['type'] = 'money_value';
      parameters['value'] = total_value;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      $http.get('/tbo_general/rest/format-values?_format=json', config_data)
        .then(function (resp) {
          var $scramble = jQuery(".scramble");
          $scramble.text(resp.data);
          if ($scope.status_scramble) {
            if($scope.old_value != resp.data) {
              $scramble.scramble(300, 20, "numbers", true);
            }
          }else{
            $scope.status_scramble = true;
          }
          $scope.total_value = resp.data;
          $scope.old_value = resp.data;
        }, function () {
          console.log("Error obteniendo los datos");
        });

      if ($scope.invoices_collection.length == multipleInvoices.getTotal()){
        $scope.all_selected = 1;
      }else{
        $scope.all_selected = 0;
      }
    }

    $scope.setOrigin = function (value) {
      multipleInvoices.setOrigin(value);
    }

    $scope.initScrollPane = function () {
      jQuery('.scroll-pane').jScrollPane();
    }

    $scope.showHideDetails = function () {
      var classes = jQuery("#body-payment-box").attr('class');
      if (classes.search("hide") != -1) {
        jQuery("#body-payment-box").removeClass('hide-table');
        //jQuery(".deploy-icon .prefix").removeClass('icon-upside-cyan');
        jQuery(".deploy-icon .prefix").addClass('rotate');

      } else {
        jQuery("#body-payment-box").addClass('hide-table');
        jQuery(".deploy-icon .prefix").removeClass('rotate');
       // jQuery(".deploy-icon .prefix").addClass('icon-upside-cyan');
      }
    }

    $rootScope.$on("InvoicesToPayMethod", function () {
      $scope.returnInvoicesToPay();
    });

    $scope.deleteInvoicePayment = function (invoice, iterator) {
      invoices_collection = multipleInvoices.getObject().data;
      invoices_collection.forEach(function (item, key, array) {
        if (item['payment_reference'] == invoice['payment_reference']) {
          invoices_collection.splice(key, 1);
        }
      })
      multipleInvoices.setObject(invoices_collection);
      multipleInvoices.setString('selected');
      $scope.returnInvoicesToPay();
      $rootScope.$emit("InvoicesToRemovePayMethod", invoice);
    }

    $scope.paymentProcess = function () {
      $http.get('/rest/session/token').then(function (resp) {
        //Get Data For Filters;
        $http({
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': resp.data
          },
          data: multipleInvoices.getObject().data,
          url: '/tboapi/billing/multiple/payment?_format=json'
        }).then(function successCallback(response) {
          data_payment = JSON.parse(response.data);
          window.location = data_payment.url;
        }, function errorCallback(response) {
          // called asynchronously if an error occurs
          // or server returns response with an error status.
          console.log('error obteniendo el servicio metodo post');
        });
      });
    }

    $scope.value_status = 0;
    $scope.date_status = 0;

    $scope.orderByParameter = function (parameter) {
      aux_collection = multipleInvoices.getObject().data;
      aux = [];

      if (parameter == 'date_payment') {

        if ($scope.date_status == 0) {
          aux = aux_collection.sort(function (a, b) {
            return new Date(b.date_payment2) - new Date(a.date_payment2)
          });
          $scope.date_status = 1;
        } else {
          aux = aux_collection.sort(function (a, b) {
            return new Date(a.date_payment2) - new Date(b.date_payment2)
          });
          $scope.date_status = 0;
        }
          
          if (jQuery('.date_payment').hasClass('icon-arrow-down')) {
              jQuery('.date_payment').removeClass('icon-arrow-down');
              jQuery('.date_payment').addClass('icon-arrow-up');
          } else {
              jQuery(".date_payment").removeClass('icon-arrow-up');
              jQuery(".date_payment").addClass('icon-arrow-down');
          }
          

      };

      if (parameter == 'value') {
        if ($scope.value_status == 0) {
          aux = aux_collection.sort(function (a, b) {
            return parseInt(b.invoice_value) - parseInt(a.invoice_value)
          });
          $scope.value_status = 1;
        } else {
          aux = aux_collection.sort(function (a, b) {
            return parseInt(a.invoice_value) - parseInt(b.invoice_value)
          });
          $scope.value_status = 0;
        }

          if (jQuery('.value').hasClass('icon-arrow-down')) {
              jQuery('.value').removeClass('icon-arrow-down');
              jQuery('.value').addClass('icon-arrow-up');
          } else {
              jQuery('.value').removeClass('icon-arrow-up');
              jQuery('.value').addClass('icon-arrow-down');
          }
          
      };

      multipleInvoices.setObject(aux);
      $scope.returnInvoicesToPay();
    }

    $scope.valueType = function (invoice) {
      invoices = multipleInvoices.getObject().data;
      status = '0';
      invoices.forEach(function (item, key, array) {
        if (item['payment_reference'] == invoice['payment_reference']) {
          if (!item['status_invoice'] && !item['alert']) {
            status = '0';
          } else {
            status = '1';
          }
        }
      })
      return status;
    }
  }
}