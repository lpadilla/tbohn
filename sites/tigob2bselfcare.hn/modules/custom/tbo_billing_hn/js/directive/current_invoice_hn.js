myApp.directive('ngCurrentInvoice', ['$http', ngCurrentInvoice]);

function ngCurrentInvoice($http) {

  var directive = {
    restrict: 'EA',
    controller: CurrentInvoiceController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl){    
    var config = drupalSettings.currentInvoiceBlock[scope.uuid_data_ng_current_invoice];
  
    scope.environment = config['config_type'];
    scope.result = config['config_type_send'];
    scope.isDetails = false;
    scope.show_mesagge_data = "";

    if (scope.environment != '') {
      retrieveInformation(scope, config, el);
    }
    var hasRegistered = false;
    scope.$watch(function() {
      if (hasRegistered) return;
      hasRegistered = true;
      scope.$$postDigest(function() {
      hasRegistered = false;
      jQuery('select').material_select();

      });
    });  


  
    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };
    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).fadeIn(400).removeClass('hide')
        if (scope.invoicesList.error) {
          jQuery("div.actions", el).hide();
        }
      }
    });

    scope.changeEnvironment = function (type) {
      if (type == 'mobile') {
        window.location.replace("/change/environment/movil");
      } else {
        window.location.replace("/change/environment/fijo");
      }
    };

    scope.initDropdown = function () {
      var variable = false;
      if (variable == false) {
        jQuery('.dropdown-button').dropdown({
            inDuration: 300,
            outDuration: 225,
            constrainWidth: false, // Does not change width of dropdown to that of the activator
            hover: true, // Activate on hover
            gutter: 0, // Spacing from edge
            belowOrigin: false, // Displays dropdown below the button
            alignment: 'left', // Displays dropdown with edge aligned to the left of button
            stopPropagation: false // Stops event propagation
          }
        );
      }
      variable = true;
    }

    var aux_contract = [];
    var aux_address = [];
    var aux_invoices = [];
    var aux_contractD = [];
    var aux_addressD = [];

    scope.closeSuggestions = function () {
      if (document.getElementById('suggestionsAddress')){
        document.getElementById('suggestionsAddress').style.display = 'none';
      }
      if (document.getElementById('suggestionsReferences')){
        document.getElementById('suggestionsReferences').style.display = 'none';
      }
    }

    scope.aplyCurrentInvoice = function (){
      scope.filtersChange(1);
    };

    scope.filtersclear = function (){
      jQuery('.dropdown-content.multiple-select-dropdown input[type="checkbox"]:checked').not(':disabled').trigger('click');
      
     
    }

    scope.filtersChangeDesktop = function (type, checked, $event) {
     
      if (type == 'address' && checked) {
        
        if (scope.addressOptions.length <= 5) {

          scope.suggestionsAddress.forEach(function (item, key, array) {
            item.name = item.name.replace("<strong>", "").replace("</strong>", "");
            jQuery('#check-' + item.name).removeAttr('disabled');
          })

          aux_addressD.push($event.target.value.replace("<strong>", "").replace("</strong>", ""));          

          if (scope.addressOptions.length == 5) {
            scope.suggestionsAddress.forEach(function (item, key, array) {
              if (aux_addressD.indexOf(item.name) == -1) {
                item.name = item.name.replace("<strong>", "").replace("</strong>", "");
                
                jQuery('#check-' + item.name).attr('disabled', 'true');
              }
            })
          }
        } else {
          scope.suggestionsAddress.forEach(function (item, key, array) {

            item.name = item.name.replace("<strong>", "").replace("</strong>", "");

            if (aux_addressD.indexOf(item.name) == -1) {
              jQuery('#check-' + item.name).attr('disabled', 'true');
            }
          })
        }
      }else if (type == 'address' && !checked) {
      
        index = aux_addressD.indexOf($event.target.value);
        if (index > -1) {
          aux_addressD.splice(index, 1);
          scope.suggestionsAddress.forEach(function (item, key, array) {
            item.name = item.name.replace("<strong>", "").replace("</strong>", "");

            jQuery('#check-' + item.name).removeAttr('disabled');
          })
        }
      }      
      scope.addressOptions = aux_addressD;     

    }

    scope.filtersChangeMobile = function (type, checked, $event) {       
      if (type == 'address' && checked) {
       
        aux_address.push($event.target.id.replace("<strong>", "").replace("</strong>", ""));
      } else if (type == 'address' && !checked) {
       
        index = aux_address.indexOf($event.target.id);
        aux_address.splice(index);
      }
      if (type == 'order' && $event.target.id) {
        switch ($event.target.id) {
          case 'Por estado':
            scope.orderModel = [];
            scope.orderModel.push('status');
            break;
          case 'Por fecha':
            scope.orderModel = [];
            scope.orderModel.push('date');
            break;
          case 'Por menor valor':
            scope.orderModel = [];
            scope.orderModel.push('min');
            break;
          case 'Por mayor valor':
            scope.orderModel = [];
            scope.orderModel.push('max');
            break;
          default:
        }
      }
      if (type == 'invoices' && checked) {
        aux_invoices.push($event.target.id);
      } else if (type == 'invoices' && !checked) {
      
        index = aux_invoices.indexOf($event.target.id);
        aux_invoices.splice(index);
      }
      scope.invoicesModel = aux_invoices;
      scope.addressOptions = aux_address;

    };

    scope.filtersChange = function (flag) {
      scope.aux_filters = 0;
      var invoices = scope.invoicesByContract[1];
      var aux = [];
      var aux_resp = [];
      
      
      if (typeof scope.addressOptions !== 'undefined' && scope.addressOptions.length > 0) {
      
        scope.aux_filters = 1;
        scope.addressOptions.forEach(function (filter, key, array) {
          
          invoices.forEach(function (bill, pos, array1) {
            if (filter == bill.address) {
              aux_resp [pos] = bill;
            }
          })
        })
       
      } else {
        aux_resp = invoices;
      }

      if (typeof scope.invoicesModel !== 'undefined' && scope.invoicesModel.length > 0) {    
        scope.aux_filters = 1;
        scope.invoicesModel.forEach(function (invoice, key, array) {

          aux_resp.forEach(function (bill, pos, array1) {
            if (invoice == 'all') {
              aux = aux_resp;
            } else if (invoice == 'slopes') {
              if (bill.date_status == 'slopes') {
                aux [pos] = bill;
              }
            } else if (invoice == 'overdue') {
              if (bill.date_status == 'overdue') {
                aux [pos] = bill;
              }
            } else if (invoice == 'paid') {
              if (bill.date_status == 'paid') {
                aux [pos] = bill;
              }
            } else if (invoice == 'adjusted') {
              if (bill.date_status == 'adjusted') {
                aux [pos] = bill;
              }
            }
          })
        })
        aux_resp = aux;

        aux = [];
      }
 
      if (typeof scope.orderModel !== 'undefined') {
        if (scope.orderModel.length > 0) {
          scope.aux_filters = 1;
          var order = scope.orderModel[scope.orderModel.length - 1];

          switch (order) {
            case 'status':
              aux = aux_resp.sort(function (a, b) {
                return new Date(a.date_payment.slice(0, 10)) - new Date(b.date_payment.slice(0, 10))
              });
              break;
            case 'date':
              aux = aux_resp.sort(function (a, b) {
                return new Date(b.date_payment.slice(0, 10)) - new Date(a.date_payment.slice(0, 10))
              });
              break;
            case 'min':
              aux = aux_resp.sort(function (a, b) {
                return a.invoice_value - b.invoice_value
              });
              break;
            case 'max':
              aux = aux_resp.sort(function (a, b) {
                return b.invoice_value - a.invoice_value
              });
              break;
            default:
          }
        } else {
          aux = aux_resp;
        }
        aux_resp = aux;
        aux = [];
      }

      if(typeof scope.linea !== 'undefined' && scope.linea.length > 0){    
        scope.aux_filters = 1;   
        
        arr = jQuery.grep(scope.linea,function(n){         
          return(n);
        });
        
        
        arr.forEach(function (line, key, array) {
        

          aux_resp.forEach(function (bill, pos, array1) {

            
            if (scope.suggestionsContractMobile[line].name == bill.address) {
              
              aux[pos] = bill;
            }
          });
        });
       
        aux_resp = aux;
        aux = [];
      }
      if(flag==1 || flag == 2){
        scope.auxScroll = aux_resp;
        scope.auxScroll = scope.groupInvoices(scope.auxScroll);
        scope.invoices = scope.auxScroll.slice(0, 10);
        if(flag == 2){
          //se eliminan los filtros aplicados en address
          jQuery( "div" ).remove(".chip.ng-binding.ng-scope");
          jQuery("div.input-field.Línea>div>input.select-dropdown").val("Línea");
        }
      }
    };

    scope.groupInvoices = function (invoices) {
    
      var group_array = [];
      result = invoices.reduce(function (r, a) {
       

        r[a.address] = r[a.address] || [];
        r[a.address].push(a);       
        return r;
      }, Object.create(null));
      for (invoice in result) {
        if (result[invoice].length == 1) {
          result[invoice][0]['address_show'] = 1;
          group_array.push(result[invoice][0]);
        } else if (result[invoice].length > 1) {
          for (i = 0; i < result[invoice].length; i++) {
            if(i == 0){
              result[invoice][i]['address_show'] = 1;
            }else{
              result[invoice][i]['address_show'] = 0;
            }
            group_array.push(result[invoice][i]);
          }
        }
      }
     
      return group_array;
    }

    scope.title = '';
    scope.auxScroll = [];
    scope.currentAddress = {'document_number': 1, 'name': 'Seleccione'};
    scope.invoices = [];
    scope.addressOptions = [];
    scope.suggestionsAddress = [];  
    scope.selectedIndexAddress = -1;
    scope.contractOptions = [];
    scope.suggestionsContract = [];
    scope.selectedIndexContract = -1;
    scope.referenceOptions = [];
    scope.suggestionsReference = [];
    scope.selectedIndexReference = -1; //currently selected suggestion index
    scope.aux_filters;
    scope.result;

    scope.invoicesFilterOptions = [
      /*{key:'all', name:  'Todas las Facturas'},*/
      {key:'slopes', name:  'Facturas por pagar'},
      {key:'overdue', name: 'Facturas vencidas'},
      {key:'paid', name:  'Facturas pagadas'},
      {key:'adjusted', name:  'Facturas ajustadas'}
    ];
    scope.searchContract = function (key_data) {
      document.getElementById('suggestionsContract').style.display = 'block';
      if (typeof key_data !== 'undefined' && key_data == 'contract') {
        var aux = [];
        scope.invoicesByContract[0].forEach(function (value, key, array) {
          value.name = value.name.replace("<strong>", "").replace("</strong>", "");
          if (value.name.search(scope[key_data]) > -1) {
            aux.push(efecto(value, scope[key_data]));
          }
        })
        scope.suggestionsContract = aux;
        scope.selectedIndexContract = -1;
      } else {
        scope.suggestionsContract = scope.invoicesByContract[0];
      }
      scope.suggestionsContractMobile = scope.suggestionsContract;
     
      document.getElementById('multiple-select-contract-mobile').style.display = 'none';
      setTimeout(function () {
        document.getElementById('multiple-select-contract-mobile').style.display = 'block';
        jQuery('#contractM').material_select();
      }, 100);
      angular.element('#contract').triggerHandler('click');
    };

    scope.checkKeyDownContract = function (event, field) {
      if (event.keyCode === 40) {//down key, increment selectedIndex
        event.preventDefault();
        if (scope.selectedIndexContract + 1 !== scope.suggestionsContract.length) {
          scope.selectedIndexContract++;
        }
      }
      else if (event.keyCode === 38) { //up key, decrement selectedIndex
        event.preventDefault();
        if (scope.selectedIndexContract - 1 !== -1) {
          scope.selectedIndexContract--;
        }
      }
      else if (event.keyCode === 13) { //enter pressed
        scope.resultClickedContract(scope.selectedIndexContract, field);
      }
      else {
        scope.suggestionsContract = [];
        scope.suggestionsContractMobile = scope.suggestionsContract;
        jQuery('#contractM').material_select();
        angular.element('#contract').triggerHandler('click');
      }
    };
    scope.resultClickedContract = function (index, field) {

      scope[field] = '';
      if (scope.contractOptions.length < 5) {

        scope.contractOptions.push(scope.suggestionsContract[index].name.replace("<strong>", "").replace("</strong>", ""));
        scope.filtersChange();
        scope.suggestionsContract = [];
        scope.suggestionsContractMobile = scope.suggestionsContract;
        jQuery('#contractM').material_select();
        angular.element('#contract').triggerHandler('click');
      }
    };

    scope.removeChipContract = function (key) {
      var index = scope.contractOptions.indexOf(key);
      scope.contractOptions.splice(index, 1);
      scope.filtersChange();
    };

    scope.searchAddress = function (key_data) {
      document.getElementById('suggestionsAddress').style.display = 'block';
      if (typeof key_data !== 'undefined' && key_data == 'address') {
          var aux = [];
          scope.invoicesByContract[2].forEach(function (value, key, array) {
              value.name = value.name.replace("<strong>", "").replace("</strong>", "");
              if (value.name.search(scope[key_data]) > -1) {
                   value.name += "<strong>hola</strong>";
                  aux.push(value);
                  aux.push(efecto(value, scope[key_data]));
              }
          });
          scope.suggestionsAddress = aux;
          scope.selectedIndexAddress = -1;
      } else {
          scope.suggestionsAddress = scope.invoicesByContract[2];
      }
      scope.suggestionsAddressMobile = scope.suggestionsAddress;
      document.getElementById('multiple-select-address-mobile').style.display = 'none';
      setTimeout(function () {
          document.getElementById('multiple-select-address-mobile').style.display = 'block';
          jQuery('#addresM').material_select();
      }, 1000);
      angular.element('#address').triggerHandler('click');
  };
    scope.checkKeyDownAddress = function (event, field) {
      if (event.keyCode === 40) {//down key, increment selectedIndex
        event.preventDefault();
        if (scope.selectedIndexAddress + 1 !== scope.suggestionsAddress.length) {
          scope.selectedIndexAddress++;
        }
      }
      else if (event.keyCode === 38) { //up key, decrement selectedIndex
        event.preventDefault();
        if (scope.selectedIndexAddress - 1 !== -1) {
          scope.selectedIndexAddress--;
        }
      }
      else if (event.keyCode === 13) { //enter pressed
        scope.resultClickedAddress(scope.selectedIndexAddress, field);
      }
      else {
        scope.suggestionsAddress = [];
        scope.suggestionsAddressMobile = scope.suggestionsAddress;
        jQuery('#addressM').material_select();
        angular.element('#address').triggerHandler('click');
      }
    };
    scope.resultClickedAddress = function (index, field) {
      scope[field] = '';

      if (scope.addressOptions.length < 5) {

        /**** Quito los elementos <strong> que he anexado por el efecto. ojo con este codigo ***/
        scope.addressOptions.push(scope.suggestionsAddress[index].name.replace("<strong>", "").replace("</strong>", ""));

        scope.filtersChange(1);
        scope.suggestionsAddress = [];
        scope.suggestionsAddressMobile = scope.suggestionsAddress;
        jQuery('#addressM').material_select();
        angular.element('#address').triggerHandler('click');
      }
    };

    
    scope.removeChipAddress = function (key) {
      var index = scope.addressOptions.indexOf(key);
      scope.addressOptions.splice(index, 1);
      scope.filtersChange(1);
    };

    scope.searchReference = function (key_data) {
      document.getElementById('suggestionsReferences').style.display = 'block';
      if (typeof key_data !== 'undefined' && key_data == 'reference') {
        var aux = [];
        scope.invoicesByContract[2].forEach(function (value, key, array) {
          value.name = value.name.replace("<strong>", "").replace("</strong>", "");
          if (value.name.search(scope[key_data]) > -1) {
            aux.push(efecto(value, scope[key_data]));
          }
        });
        scope.invoicesByContract[0].forEach(function (value, key, array) {
          value.name = value.name.replace("<strong>", "").replace("</strong>", "");
          if (value.name.search(scope[key_data]) > -1) {
            aux.push(efecto(value, scope[key_data]));
          }
        });
        scope.suggestionsReference = aux;
        scope.selectedIndexReference = -1;
      } else {
        scope.suggestionsReference.push(scope.invoicesByContract[0]);
        scope.suggestionsReference.push(scope.invoicesByContract[2]);
      }
    };

    /***** función para el efecto de busqueda ***/
    function efecto(value,key_data) {
        $str = (value.name + "");
        $search = key_data + "";
        $pos = $str.indexOf($search);

        if ($pos >=0){
            value.name = "<strong>" + $str.substr($pos, $search.length)  + "</strong>" + (($str.length - $search.length)>0?$str.substr($search.length, $str.length):"");
        }
        return value;
    }


    scope.checkKeyDownReference = function (event, field) {
      if (event.keyCode === 40) {//down key, increment selectedIndex
        event.preventDefault();
        if (scope.selectedIndexReference + 1 !== scope.suggestionsReference.length) {
          scope.selectedIndexReference++;
        }
      }
      else if (event.keyCode === 38) { //up key, decrement selectedIndex
        event.preventDefault();
        if (scope.selectedIndexReference - 1 !== -1) {
          scope.selectedIndexReference--;
        }
      }
      else if (event.keyCode === 13) { //enter pressed
        scope.resultClickedReference(scope.selectedIndexReference, field);
      }
      else {
        scope.suggestionsReference = [];
      }
    };
    scope.resultClickedReference = function (index, item) {
      field = item.slice(0, 9).trim();
      display = item.slice(10, 16).trim();
      scope[field] = '';
      /**** Quito los elementos que he anexado por el efecto ***/
      scope.referenceChange(scope.suggestionsReference[index].name.replace("<strong>", "").replace("</strong>", ""));
      scope.suggestionsReference = [];
      if (display == 'mobile') {
        document.getElementById('filters-mobile-container').className = 'filters-mobile-container closed';
        document.getElementById('closed-btn-1').className = 'icon-filters-cyan closed';
      }
    };

    scope.referenceChange = function (option) {
      var aux = [];
      scope.invoicesByContract[1].forEach(function (item, key, array) {
        if (item.address == option || item.contract == option) {
          aux [key] = item;
        } else {
          aux[key] = '';
        }
      });
      scope.auxScroll = aux;
      scope.invoices = scope.auxScroll.slice(0, 10);
    };


    jQuery("#contract").trigger({type: 'keypress', which: 13, keyCode: 13});
    jQuery("#address").trigger({type: 'keypress', which: 13, keyCode: 13});
    jQuery("#reference").trigger({type: 'keypress', which: 13, keyCode: 13});

    scope.loadMore = function () {
      if (scope.isDetail != 'true') {
        if (scope.auxScroll.length > 0) {
          scope.invoices = scope.auxScroll.slice(0, scope.invoices.length + 10);
        } else if (typeof scope.invoicesByContract != 'undefined' && typeof scope.invoicesByContract[1] != 'undefined') {
          if (scope.aux_filters == 0) {
            scope.invoices = scope.invoicesByContract[1].slice(0, scope.invoices.length + 10);
          }
        }
      }
    }

    var aux = 'filterM-';
    scope.showHideFilter = function (identifier) {
      aux_filter = aux.concat(identifier);
      jQuery('#contractM').material_select();
      jQuery('#addressM').material_select();
      document.getElementById(aux_filter).style.display = 'block';
      document.getElementById('mobile-menu-filters').style.display = 'none';
      document.getElementById('form-filtros-interno').style.display = 'block';
    }

    scope.hideFilter = function (identifier) {
      aux_filter = aux.concat(identifier);
      document.getElementById(aux_filter).style.display = 'none';
      document.getElementById('mobile-menu-filters').style.display = 'block';
    }

    scope.openCloseFilters = function () {
      if (document.getElementById('filters-mobile-container').className == 'filters-mobile-container closed') {
       document.getElementById('filters-mobile-container').className = 'filters-mobile-container';
        document.getElementById('closed-btn-1').className = 'prefix icon-filters-cyan';
      }else {
        document.getElementById('filters-mobile-container').className = 'filters-mobile-container closed';
        document.getElementById('closed-btn-1').className = 'prefix icon-filters-cyan closed';
      }
    }

    scope.filterFunctionMobile = function () {
      scope.filtersChangeMobile();
      document.getElementById('filters-mobile-container').className = 'filters-mobile-container closed';
      document.getElementById('closed-btn-1').className = 'prefix icon-filters-cyan closed';
    }

    scope.closeFunction = function () {
      scope.addressOptions = [];
      scope.contractOptions = [];
      scope.invoicesModel = [];
      scope.orderModel = [];
      scope.filtersChange();
      document.getElementById('filters-mobile-container').className = 'filters-mobile-container closed';
      document.getElementById('closed-btn-1').className = 'prefix icon-filters-cyan closed';
    }
  }

  function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
      results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, ""));
  }

  function retrieveInformation(scope, config, el) {
    scope.result = config.config_type;
    

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
          if (resp.data.error) {
            scope.show_mesagge_data = resp.data.message;
            scope.alertas_servicios_current_invoice();
          }
          else {           
            scope.invoicesByContract = resp.data;
            scope.suggestionsContractMobile = scope.invoicesByContract[0];
            scope.suggestionsAddressMobile = scope.invoicesByContract[2];
            if (config.invoiceId != '') {
              scope.invoicesByContract[1].forEach(function (value, key, array) {
                if (value.invoiceId.search(config.invoiceId) > -1) {
                  scope.invoices.push(value);
                }
              });

              scope.isDetails = true;
            }
            else {
              scope.invoices = scope.invoicesByContract[1].slice(0, 10);
              scope.auxScroll = scope.invoicesByContract[1];
              scope.isDetails = false;
            }
            scope.invoicesLength = scope.invoicesByContract[1].length;
            
            scope.addressF = scope.invoicesByContract[2];
            scope.contractsF = scope.invoicesByContract[0];
            scope.title = config.title_invoice;
            scope.details_url = config.details_url;
          }
        }, function () {
          console.log("Error obteniendo los datos");
        });
    }
  }

  CurrentInvoiceController.$inject = ['$scope', '$http'];

  function CurrentInvoiceController($scope, $http) {
    // Init vars
    if (typeof $scope.invoicesList == 'undefined') {
      $scope.invoicesList = "";
      $scope.invoicesList.error = false;
    }

    if (typeof $scope.resources == 'undefined') {
      $scope.resources = [];
    }

  
    var package = {};

    //Add config to url
    var config_data = {
      params: package,
      headers: {'Accept': 'application/json'}
    };

    //Declare vars and function for ordering
    $scope.predicate = 'attraction';
    $scope.reverse = false;
    $scope.order = function (predicate) {
      $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
      $scope.predicate = predicate;
    };

    //Send data session
    $scope.sendDetail = function ($event, doc_type, doc_number, contractId, type, detail, payment_reference, address, city, line, invoiceId, state, country, zipcode, msisdn) {
      $event.preventDefault();
      var url = '/billing/session/data/hn?_format=json';
      //Add key for this display
      var parameters = {};
      parameters['data'] = JSON.stringify({
        "docNumber": doc_number,
        "contractId": contractId,
        "paymentReference": payment_reference,
        "address": address,
        "city": city,
        "line": line,
        "invoiceId": invoiceId,
        "state": state,
        "country": country,
        "zipcode": zipcode,
        "msisdn": msisdn,
      });

      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      $http.get(url, config_data)
        .then(function (resp) {
          window.location.href = $event.target.href;
        }, function () {
          console.log("Error obteniendo los datos");
        });
     
      
    }

    //Show message service
    $scope.alertas_servicios_current_invoice = function () {

      jQuery(".block-currentinvoice .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
      $html_mensaje = jQuery('.block-currentinvoice .messages-only').html();
      jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

      jQuery(".block-currentinvoice .messages-only .text-alert .txt-message").remove();

      jQuery('.messages .close').on('click', function() {
        jQuery('.messages').hide();
      });
    }
    
    //Reset filters
    jQuery('.click-filter-reset').click(function ()
    {
      //reset filters         
     
    

      var config = drupalSettings.currentInvoiceBlock[$scope.uuid_data_ng_current_invoice];

      //Get value filters
      var parameters = {};
      for (filter in config['filters']) {
        $scope[filter] = '';
      }

      jQuery('.tags-cloud label').addClass('active');

     
      retrieveInformation($scope, config);
     
    });
  }
}