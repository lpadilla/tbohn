myApp.directive('ngCurrentInvoice', ['$http', ngCurrentInvoice]);

function ngCurrentInvoice($http) {

  var directive = {
    restrict: 'EA',
    controller: CurrentInvoiceController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.currentInvoiceBlock[scope.uuid_data_ng_current_invoice];
    scope.environment = config['config_type'];
    scope.result = config['config_type_send'];
    scope.isDetails = false;
    scope.show_mesagge_data = "";

    if (scope.environment != '') {
      retrieveInformation(scope, config, el);
      //retrieveInformation(scope, config, el);
    }

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
    scope.contrato;
    
    scope.aplyCurrentInvoice = function (){            
      scope.filtersChange(1);
    };

    scope.filtersclear = function (){
      if(scope.contrato == 0){
        //se limpia la varable auxiliar de Address      
        aux_address = [];
        aux_addressD = [];
        //se limpia la varable auxiliar de Invoices      
        var aux_invoices = [];
        //se limpia el scope de Order
        scope.orderModel = [];    

        scope.invoicesModel = aux_invoices;
        scope.addressOptions = aux_address; 
        jQuery("div" ).remove( ".chip.ng-binding.ng-scope");
        
        scope.filtersChange(2);                
      }else{
        scope.cont_general = scope.aux_num_contract; 
        scope.invoices_mc = scope.aux_invoices_mc;        
        //se limpia la varable auxiliar de Invoices      
        var aux_invoices = [];
        //se limpia el scope de Order
        scope.orderModel = [];    
        scope.invoicesModel = aux_invoices;

        jQuery( "div" ).remove( ".chip.ng-binding.ng-scope");               
        jQuery("li.active").removeClass("active");
        scope.filtersChange(2); 
      }
    }

    scope.filtersChange = function (flag){      
      if (angular.isUndefined(flag) || flag === null)
      {
          flag = 0;
      }
      scope.aux_filters = 0;
      var value_contract = jQuery("input#contract").val();
    
      var aux = [];
      var aux_resp = [];
      if(scope.contrato==0){ //para un solo contrato        
        if(typeof value_contract !== 'undefined' && value_contract != null && value_contract != ''){
          scope.aux_filters = 1;
          scope.invoicesByContract[1].forEach(function(invoice){            
            if(invoice.contract == value_contract){
              aux.push(invoice);              
            }
          });

          if(aux.length == 0){
              var config_url = scope.billing_bycontract+'&billing_contract='+value_contract;
              var config_data = scope.config_data;           

              $http.get(config_url, config_data)
                .then(function (resp) {
                  if (resp.data.error) {
                    scope.show_mesagge_data = resp.data.message;
                    scope.alertas_servicios_current_invoice();
                  }
                  else {                                 
                    scope.contrato = 0;
                    scope.invoicesByContract = resp.data;
                    
                    scope.invoices = scope.invoicesByContract[1].slice(0, 10);
                    scope.auxScroll = scope.invoicesByContract[1];
                                                    

                    resp.data.forEach(function(invoice_array, index) {                                        
                      invoice_array.forEach(function(invoice_one, ind){                        
                        scope.invoices['contract']=invoice_one.contract;
                        scope.invoices[ind]=[];
                      });                       
                    });
                  }
                }, function () {
                  console.log("Error obteniendo los datos");
                });
          }

          if(aux.length == 0){
            scope.cont_general = [];
          }
          aux_resp=aux;
          aux = [];
        }
        //estatus de factura (pagada, pendiente, vencida)
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
        //Orden de factura (fecha, menor valor, mayor valor)
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
      }else{ //para varios contratos
        aux_resp = scope.aux_invoices_mc;
        var num_contract = scope.cont_general;
        if(flag == 1){

          console.log(value_contract);
          console.log(scope.invoicesModel);
          console.log(scope.orderModel);

          console.log(aux_resp);


          if(typeof value_contract !== 'undefined' && value_contract != null && value_contract != ''){
            var invoices = scope.aux_invoices_mc;   
            scope.cont_general = [];
            num_contract.forEach(function(num){              
              if(num==value_contract){
                scope.cont_general.push(value_contract);
                invoices.forEach(function(inv){
                  if(inv.contract == value_contract){
                    aux.push(inv);
                  }
                });
              }
            });

            if(aux.length == 0){
              var config_data = scope.config_data;           
              scope.cont_general = [];i=0;
              if(Array.isArray(scope.clients)){                
                scope.clients.forEach(function(client_cc){
                  var config_url = scope.billing_bycontract+'&billing_contract='+value_contract+'&client='+client_cc;
                  $http.get(config_url, config_data).then(function (resp) {
                      if (resp.data.error) {
                        scope.show_mesagge_data = resp.data.message;
                        scope.alertas_servicios_current_invoice();
                      }else{                      
                        resp.data.forEach(function(invoice_array, index) {                  
                          scope.cont_general[i] = "";
                          invoice_array.forEach(function(invoice_one, ind){    
                            if(invoice_one.contract != undefined){
                              scope.invoices_gc=invoice_one.contract;
                              scope.invoices_mc[ind]=[];
                              scope.invoices_mc[ind]=invoice_one;                            
                            }
                          });                         
                          scope.cont_general[i] = scope.invoices_gc;
                          scope.invoices_gc = "";
                          i++;                
                        });
                        scope.invoicesByContract=scope.invoices_mc;
                      }
                  }, function () {
                      console.log("Error obteniendo los datos");
                  });
                });                
              }else{
                var config_url = scope.billing_bycontract+'&billing_contract='+value_contract+'&client='+scope.clients;
                $http.get(config_url, config_data).then(function (resp) {
                    if (resp.data.error) {
                      scope.show_mesagge_data = resp.data.message;
                      scope.alertas_servicios_current_invoice();
                    }else{                      
                      resp.data.forEach(function(invoice_array, index) {                  
                        scope.cont_general[i] = "";
                        invoice_array.forEach(function(invoice_one, ind){    
                          if(invoice_one.contract != undefined){
                            scope.invoices_gc=invoice_one.contract;
                            scope.invoices_mc[ind]=[];
                            scope.invoices_mc[ind]=invoice_one;                            
                          }
                        });                         
                        scope.cont_general[i] = scope.invoices_gc;
                        scope.invoices_gc = "";
                        i++;                
                      });
                      scope.invoicesByContract=scope.invoices_mc;
                    }
                }, function () {
                    console.log("Error obteniendo los datos");
                });
              }
            }
            aux_resp=aux;
            aux = [];                    
          }

          //estatus de factura (pagada, pendiente, vencida)
          if (typeof scope.invoicesModel !== 'undefined' && scope.invoicesModel != null && scope.invoicesModel != '' && scope.invoicesModel.length > 0) {        
            scope.aux_filters = 1;
            var bills_arrr = [];
            scope.invoicesModel.forEach(function (invoice, key, array) {
              aux_resp.forEach(function (bill, pos, array1) {
                if (invoice == 'all') {
                  aux [pos] = bill;
                  bills_arrr.push(bill.contract);
                } else if (invoice == 'slopes') {
                  if (bill.date_status == 'slopes') {
                    aux [pos] = bill;
                    bills_arrr.push(bill.contract);
                  }
                } else if (invoice == 'overdue') {
                  if (bill.date_status == 'overdue') {
                    aux [pos] = bill;
                    bills_arrr.push(bill.contract);
                  }
                } else if (invoice == 'paid') {
                  if (bill.date_status == 'paid') {
                    aux [pos] = bill;
                    bills_arrr.push(bill.contract);
                  }
                } else if (invoice == 'adjusted') {
                  if (bill.date_status == 'adjusted') {
                    aux [pos] = bill;
                    bills_arrr.push(bill.contract);
                  }
                }
              });              
            });

            scope.cont_general = scope.eliminarDuplicados(bills_arrr);

            aux_resp = aux;
            aux = [];
          }

          //Orden de factura (fecha, menor valor, mayor valor)
          if (typeof scope.orderModel !== 'undefined' && scope.orderModel != null && scope.orderModel != '') {            
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

          scope.invoices_mc = aux_resp;
          if(aux_resp.length == 0){
            scope.cont_general = [];
          }
        }        
      }

      if(flag==2){
        aux_resp = scope.aux_invoices_mc;
      }

      if(flag==1 || flag == 2){
        scope.auxScroll = aux_resp;
        scope.auxScroll = scope.groupInvoices(scope.auxScroll);
        scope.invoices = scope.auxScroll.slice(0, 10);
        if(flag == 2){
          jQuery( "div" ).remove( ".chip.ng-binding.ng-scope");
        }
      }
    };

    scope.eliminarDuplicados = function(arr) {
      return arr.filter(function(valor, indice) {
        return arr.indexOf(valor) == indice;
      })
    }

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
    scope.invoices_mc = [];
    scope.addressOptions = [];
    scope.suggestionsAddress = [];  
    scope.selectedIndexAddress = -1;
    scope.contractOptions = [];
    scope.suggestionsContract = [];
    scope.selectedIndexContract = -1;
    scope.referenceOptions = []
    scope.suggestionsReference = [];
    scope.selectedIndexReference = -1; //currently selected suggestion index
    scope.aux_filters;
    scope.result;

    scope.loadCount=0;
    scope.newSectionArray=[];

    scope.invoicesFilterOptions = {
      'all': 'Todas las Facturas',
      'slopes': 'Facturas pendientes',
      'overdue': 'Facturas vencidas',
      'paid': 'Facturas pagadas',
      'adjusted': 'Facturas ajustadas'
    };

    scope.searchContract = function (key_data){      
      if(scope.contrato == 0){
        document.getElementById('suggestionsContract').style.display = 'block';
        if (typeof key_data !== 'undefined' && key_data == 'contract')
        {
          var aux = [];
          scope.invoicesByContract[0].forEach(function (value, key, array) {
            value.name = value.name.replace("<strong>", "").replace("</strong>", "");
            if (value.name.search(scope[key_data]) > -1)
            {

              aux.push(efecto(value, scope[key_data]));
            }
          })
          scope.suggestionsContract = aux;
          scope.selectedIndexContract = -1;
        }
        else
        {
          scope.suggestionsContract = scope.invoicesByContract[0];
        }
        scope.suggestionsContractMobile = scope.suggestionsContract;
        document.getElementById('multiple-select-contract-mobile').style.display = 'none';
        setTimeout(function () {
          document.getElementById('multiple-select-contract-mobile').style.display = 'block';
          jQuery('#contractM').material_select();
        }, 100);
        angular.element('#contract').triggerHandler('click');
      }
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
      if(scope.contrato == 0){        
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
        }
      else {
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

  

  function orderInvoices(scope){
    if(scope.contrato == 0){
      var invoice_order = [];
      var ll = [];
      var j = 0;

      scope.invoices.forEach(function(t1, i1){
        if(t1.date_status == 'overdue'){     
          ll[j] = t1.invoiceId;
          j++;
        }
      });        
      scope.invoices.forEach(function(t2, i2){
        if(t2.date_status != 'overdue'){     
          ll[j] = t2.invoiceId;
          j++;
        }
      });

      ll.forEach(function(d, f){        
        scope.invoices.forEach(function(invoi, indexin){
          if(invoi.invoiceId == d){
            invoice_order.push(invoi);
          }
        });        
      });

      scope.invoices = invoice_order;
    }else{
      var invoice_order = [];
      var temp = [];
      var ll = [];

      var j = 0;
      scope.cont_general.forEach(function(cont, index1) {
        i=0; 
        scope.invoices_mc.forEach(function(inv, index2){
          if(cont == inv.contract){
            temp[i] = inv;
            i++
          }
        });
        temp.forEach(function(t1, i1){
          if(t1.date_status == 'overdue'){     
            ll[j] = t1.invoiceId;
            j++;
          }
        });        
        temp.forEach(function(t2, i2){
          if(t2.date_status != 'overdue'){     
            ll[j] = t2.invoiceId;
            j++;
          }
        });
        temp = [];
      });

      ll.forEach(function(d, f){        
        scope.invoices_mc.forEach(function(invoi, indexin){
          if(invoi.invoiceId == d){
            invoice_order.push(invoi);
          }
        });        
      });

      scope.invoices_mc = invoice_order;
    }
  };

  function retrieveInformation(scope, config, el) {
    scope.result = config.config_type;

    if (scope.resources.indexOf(config.url) == -1) {
      
      //Add key for this display
      var parameters = {
        estatus: "PC",
      };

      scope.hay_billing = config.hay_billing; 
      parameters['config_columns'] = config.config_columns;
      
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      
      scope.status_invoice=['PC','CA'];
      scope.cantids=scope.status_invoice.length;

      
      /* variables para manejar en la recursividad, gracias al scope*/
      scope.cicle         =0;   // Inicializacion en cero para ciclar arreglo de clientes
      scope.posi          =0;   // inicializacion en cero para posicion de arreglo de contratos
      scope.cont_general  = []; // arreglo que indica la posicion de contratos
      scope.flag_ini      =0;
      scope.can_uno       =0;   // variable para sumar cilos de varios contratos
      scope.ind_cicle     =0;   // Contabiliza los registros de faturas totales para varios contratos
      scope.cicle_contracts_borde =0;  
      scope.contratos_borde =[];
      scope.aux_invoices_mc = [];
      scope.aux_num_contract = [];


      /* Llamada a la funcion recursiva */
      scope.billing_bycontract = config.url;
      scope.config_data = config_data;

      
      $http.get(config.url, config_data)
        .then(function (resp) {
          if (resp.data.error) {
            scope.show_mesagge_data = resp.data.message;
            scope.alertas_servicios_current_invoice();
            scope.cantids--;
            if(scope.cantids>0){
              //return loadSection2(scope, config);
              return loadCA(scope, config);
            }
          }
          else {            
            //si es un solo contrato
            if(typeof(resp.data[0])!="undefined" && resp.data[0]!==null && resp.data[0]!='morecontract'){
              
              if(resp.data[1]!=""){
                scope.cicle++;
                
              
                scope.flag_ini=1;
                if(scope.contrato!=1)
                  scope.contrato = 0;
                
                scope.invoicesByContract2 = resp.data;
                
                if(scope.cicle==1){
                  scope.suggestionsContractMobile = scope.invoicesByContract2[0];
                  scope.suggestionsAddressMobile = scope.invoicesByContract2[2];
                }
                if (config.invoiceId != '') {
                
                  scope.invoicesByContract2[1].forEach(function (value, key, array) {
                    
                      if (value.invoiceId.search(config.invoiceId) > -1) {
                        scope.invoices.push(value);
                      }                 
                    
                  });
                  scope.isDetails = true;
                }
                else {
                  if(scope.cicle==1){
                    //ciclo 1 un contrato
                    
                    scope.invoices = scope.invoicesByContract2[1].slice(0, 10);
                    scope.auxScroll = scope.invoicesByContract2[1];
                    scope.pos_invoice = scope.invoices;
                    scope.isDetails = false;
                  }else{
                  
                    scope.pos_invoice=scope.invoicesByContract2[1].slice(0, 10);
                    scope.invoices = scope.invoices.concat(scope.invoicesByContract2[1].slice(0, 10));
                    scope.auxScroll = scope.auxScroll.concat(scope.invoicesByContract2[1]);
                    
                  }
                }
                if(scope.cicle==1){ 
                  scope.invoicesLength = scope.invoicesByContract2[1].length;               
                  scope.addressF = scope.invoicesByContract2[2];
                  scope.contractsF = scope.invoicesByContract2[0];
                  scope.title = config.title_invoice;
                  scope.details_url = config.details_url;
                } else{
                  scope.invoicesLength = scope.invoicesLength + scope.invoicesByContract2[1].length; 
                }
                //asigna el arreglo del contrato a arreglo total en posicion específica
                if (config.invoiceId != '') {
                  scope.cont_general[scope.posi]= scope.invoices[0].contract;
                  scope.posi++;
                }else{
                scope.cont_general[scope.posi]= scope.pos_invoice[0].contract;
                scope.posi++;
                }

                if(!resp.data[2][0].name)
                scope.contratos_borde=resp.data[2];
              }
            }else{//if son varios contratos
              if(resp.data[1]!=null){
                scope.cicle++;
                // Variables necesarias para ciclo de varios contratos
                scope.ind_begin   =0;  // contabiliza la cantidad de facturas para cada contrato
                scope.flag_varios =1;  // permite conocer si se ejecuto un ciclo de varios contratos
                scope.contrato    = 1; // notifica que zona de la plantilla user(varios contratos)
                scope.can_uno++;  // contador de veces que se ejecutan varios contratos

                if(scope.cicle==1){ 
                  scope.title = config.title_invoice;
                  scope.details_url = config.details_url;
                }

                /* Se almacena la cantidad de contratos y su valor */
                //scope.cont_general = [];//i=0;
                resp.data[1].forEach(function(invoice_array, index) {    
                  if(invoice_array!=null){    
                    invoice_array.forEach(function(invoice_one, ind){                       
                      scope.invoices_gc=invoice_one.contract;
                      //scope.invoices_mc[ind]=[];

                    });
                  }
                   
                  scope.cont_general[scope.posi] = scope.invoices_gc;
                  scope.invoices_gc = "";
                  scope.posi++;                
                });
                /* se guardan los registros en arreglo por posicion para motrar posteriormente */
                resp.data[1].forEach(function(invoice_array, index) {
                  if(invoice_array!=null){
                    invoice_array.forEach(function(invoice_one, ind){    
                      scope.invoices_mc[ind+scope.ind_cicle]=invoice_one;
                      scope.ind_begin=ind;
                    });     
                  }
                });
                scope.ind_cicle+=scope.ind_begin+1;  // se guarda la cantidad final que se suma en cada ciclo de varios contratos
                
                /* Si es la primera vez de todo el ciclo, se guarda la info en una variable general 'invoicesByContract' */
                if(scope.cicle==1){ 
                  scope.invoicesByContract=scope.invoices_mc;
                }else{
                  /* si no es la primera vez que se ejecuta la recursividad, 
                  pero si es la primera vez en el ciclo de varios contratos */
                  if(scope.flag_ini==1 && scope.can_uno==1){
                    scope.invoicesByContract=scope.invoices_mc;
                  }else{
                    scope.invoicesByContract=scope.invoicesByContract.concat(scope.invoices_mc);
                    
                  }

                }  
              
              
              }
              scope.contratos_borde=resp.data[2]; // se guarda los posibles casos borde.
            }
            
            

            /* Verificar si quedan client code por ejecutar */
            scope.cantids--;
            if(scope.cantids>0){
              if(scope.hay_billing!="1"){
                return loadCA(scope, config); // Funcion para contratos con status CA
              }else{
                if(scope.flag_ini==1 && scope.cicle>1){
                  scope.invoicesByContract=scope.invoices.concat(scope.invoicesByContract);
                  scope.invoices_mc=scope.invoices.concat(scope.invoices_mc);              
                }

                /* si solo existen casos de un solo contrato se agrega el arreglo 2 al general, 
                para que lo reconozca toda la funcionalidad de angular */
                if(scope.flag_varios!=1){
                  scope.invoicesByContract=scope.invoicesByContract2;
                }
              }
            }else{
              /* AL finalir el ciclo, si existieron clientes con un solo contrato y ademas varios contratos
                  , se anidan los casos de un solo contrato a todo el arreglo de varios contratos
               */
              if(scope.flag_ini==1 && scope.cicle>1){
                scope.invoicesByContract=scope.invoices.concat(scope.invoicesByContract);
                scope.invoices_mc=scope.invoices.concat(scope.invoices_mc);              
              }

              /* si solo existen casos de un solo contrato se agrega el arreglo 2 al general, 
              para que lo reconozca toda la funcionalidad de angular */
              if(scope.flag_varios!=1){
                scope.invoicesByContract=scope.invoicesByContract2;
              }
              scope.aux_invoices_mc = scope.invoices_mc;
              scope.aux_num_contract =scope.cont_general;                            
              console.log(scope.aux_num_contract);
            }
          }
        }, function () {
        
        scope.show_mesagge_data = "Error obteniendo los datos de Facturas";
        scope.alertas_servicios_current_invoice();
      });    
      
    }
  }

  /* Funcion para llamar a contraos con status CA */
  function loadCA(scope, config, el) {
    scope.result = config.config_type;
    

    if (scope.resources.indexOf(config.url) == -1) {
      
      //parametro de status CA y se envian posibles contratos bordes para validar si se toman en cuenta en CA
      var parameters = {
        estatus: "CA",
        contracts_borde: scope.contratos_borde,
      };


      parameters['config_columns'] = config.config_columns;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };     

      scope.billing_bycontract = config.url;
      scope.config_data = config_data;
      scope.vacio=0;

      $http.get(config.url, config_data)
        .then(function (resp) {
          if (resp.data.error) {
            scope.show_mesagge_data = resp.data.message;
            scope.alertas_servicios_current_invoice();
            scope.cantids--;
            if(scope.cantids>0){
              //return loadSection2(scope, config);
            }
          }
          else {
            
            //si es un solo contrato
            if(typeof(resp.data[0]) != "undefined" && resp.data[0] !== null && resp.data[0] != 'morecontract' ) {
              
                scope.cicle++;
                scope.flag_ini=1;
                if(scope.contrato!=1)
                  scope.contrato = 0;

            
                scope.invoicesByContract2 = resp.data;
                
                if(scope.cicle==1){
                  scope.suggestionsContractMobile = scope.invoicesByContract2[0];
                  scope.suggestionsAddressMobile = scope.invoicesByContract2[2];
                }
                if (config.invoiceId != '') {
                  scope.invoicesByContract2[1].forEach(function (value, key, array) {
                    if (value.invoiceId.search(config.invoiceId) > -1) {
                      scope.invoices.push(value);
                    }
                  });
                  scope.isDetails = true;
                }
                else {
                  if(scope.cicle==1){
                    //ciclo 1 un contrato
                    scope.invoices = scope.invoicesByContract2[1].slice(0, 10);
                    scope.auxScroll = scope.invoicesByContract2[1];
                    scope.pos_invoice = scope.invoices;
                    scope.isDetails = false;
                  }else{
    
                    scope.pos_invoice=scope.invoicesByContract2[1].slice(0, 10);
                    scope.invoices = scope.invoices.concat(scope.invoicesByContract2[1].slice(0, 10));
                    scope.auxScroll = scope.auxScroll.concat(scope.invoicesByContract2[1]);
                    
                  }
                }
                if(scope.cicle==1){ 
                  scope.invoicesLength = scope.invoicesByContract2[1].length;               
                  scope.addressF = scope.invoicesByContract2[2];
                  scope.contractsF = scope.invoicesByContract2[0];
                  scope.title = config.title_invoice;
                  scope.details_url = config.details_url;
                } else{
                  scope.invoicesLength = scope.invoicesLength + scope.invoicesByContract2[1].length; 
                }
                //asigna el arreglo del contrato a arreglo total en posicion específica
                scope.cont_general[scope.posi]= scope.pos_invoice[0].contract;
                scope.posi++;
              

            }else{//if son varios contratos
              scope.cicle++;
              
              // Variables necesarias para ciclo de varios contratos
              scope.ind_begin   =0;  // contabiliza la cantidad de facturas para cada contrato
              scope.flag_varios =1;  // permite conocer si se ejecuto un ciclo de varios contratos
              scope.contrato    = 1; // notifica que zona de la plantilla user(varios contratos)
              scope.can_uno++;  // contador de veces que se ejecutan varios contratos

              if(scope.cicle==1){ 
                scope.title = config.title_invoice;
                scope.details_url = config.details_url;
              }

              
              /* Se almacena la cantidad de contratos y su valor */
              //scope.cont_general = [];//i=0;
              
                resp.data[1].forEach(function(invoice_array, index) { 
                                
                  invoice_array.forEach(function(invoice_one, ind){                       
                    scope.invoices_gc=invoice_one.contract;
                    //scope.invoices_mc[ind]=[];
                  });
                  
                  if(scope.cont_general.length==0 ){
                    scope.cont_general[scope.posi] = scope.invoices_gc;
                    scope.invoices_gc = "";
                    scope.posi++;
                    scope.vacio=1;
                  }else{
                    
                    if(!scope.cont_general.includes(scope.invoices_gc) || scope.vacio==1){ 
                      scope.cont_general[scope.posi] = scope.invoices_gc;
                      scope.invoices_gc = "";
                      scope.posi++;
                    }
                  }
                                
                });
              
              /* se guardan los registros en arreglo por posicion para motrar posteriormente */
              resp.data[1].forEach(function(invoice_array, index) {                  
                invoice_array.forEach(function(invoice_one, ind){    
                  scope.invoices_mc[ind+scope.ind_cicle]=invoice_one;
                  scope.ind_begin=ind;
                });                
              });
              scope.ind_cicle+=scope.ind_begin+1;  // se guarda la cantidad final que se suma en cada ciclo de varios contratos

              /* Si es la primera vez de todo el ciclo, se guarda la info en una variable general 'invoicesByContract' */
              if(scope.cicle==1){ 
                scope.invoicesByContract=scope.invoices_mc;
              }else{
                /* si no es la primera vez que se ejecuta la recursividad, 
                pero si es la primera vez en el ciclo de varios contratos */
                if(scope.flag_ini==1 && scope.can_uno==1){
                  scope.invoicesByContract=scope.invoices_mc;
                }else{                 
                    scope.invoicesByContract=scope.invoices_mc;

                }

              }  
            }
            

            /* Verificar si quedan otros estatus por ejecutar */
            scope.cantids--;
            if(scope.cantids>0){
              return loadCA(scope, config);
            }else{
              if(scope.contratos_borde.length == 0){
                
                /* AL finalir el ciclo, si existieron clientes con un solo contrato y ademas varios contratos
                  , se anidan los casos de un solo contrato a todo el arreglo de varios contratos
               */
                if(scope.flag_ini==1 && scope.cicle>1){
                  scope.invoicesByContract=scope.invoices.concat(scope.invoicesByContract);
                  scope.invoices_mc=scope.invoices.concat(scope.invoices_mc);              
                }

                /* si solo existen casos de un solo contrato se agrega el arreglo 2 al general, 
                para que lo reconozca toda la funcionalidad de angular */
                if(scope.flag_varios!=1){
                  scope.invoicesByContract=scope.invoicesByContract2;
                }

                scope.aux_invoices_mc = scope.invoices_mc;
                scope.aux_num_contract = scope.cont_general; 


              }else{
                // Va caso borde
                if(Array.isArray(scope.contratos_borde)){
                  scope.cant_borde=scope.contratos_borde.length;
                }else{
                  scope.cant_borde=1;
                }
                return load_old_contracts(scope, config);
              }
              
            }

                    
                  
          }
        }, function () {
        scope.show_mesagge_data = "Error obteniendo los datos de Facturas";
        scope.alertas_servicios_current_invoice();
      });     
      
    }
  }


  function load_old_contracts(scope, config, el) {
    scope.result = config.config_type;
    /* Funcion para obtener la informacion sobre contratos casos borde*/
    if (scope.resources.indexOf(config.url) == -1) {
      
      //Add key for this display, contratos borde se envian por parametro.
      if(Array.isArray(scope.contratos_borde)){
        var parameters = {
          contract_borde:  scope.contratos_borde[scope.cicle_contracts_borde],
          contracts_borde:  scope.contratos_borde,
        };
      }else{
        var parameters = {
          contract_borde:  scope.contratos_borde,
          contracts_borde:  scope.contratos_borde,
        };
      }


      parameters['config_columns'] = config.config_columns;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };     

      scope.billing_bycontract = config.url;
      scope.config_data = config_data;

      $http.get(config.url, config_data)
        .then(function (resp) {
          if (resp.data.error) {
            scope.show_mesagge_data = resp.data.message;
            scope.alertas_servicios_current_invoice();
            scope.cant_borde--;
            if(scope.cant_borde>0){
              scope.cicle_contracts_borde++;
              return load_old_contracts(scope, config);
            }
          }
          else {
            scope.cicle_contracts_borde++; //contabilizar contratos borde
            //si es un solo contrato
            if(typeof(resp.data[0]) != "undefined" && resp.data[0] !== null && resp.data[0] != 'morecontract' ) {
                            
              scope.flag_ini=1;
              if(scope.contrato!=1)
                scope.contrato = 0;
              
              scope.invoicesByContract2 = resp.data;
              if(scope.cicle_contracts_borde==1){
                scope.suggestionsContractMobile = scope.invoicesByContract2[0];
                scope.suggestionsAddressMobile = scope.invoicesByContract2[2];
              }
              if (config.invoiceId != '') {
                scope.invoicesByContract2[1].forEach(function (value, key, array) {
                  if (value.invoiceId.search(config.invoiceId) > -1) {
                    scope.invoices.push(value);
                  }
                });
                scope.isDetails = true;
              }
              else {
                if(scope.cicle_contracts_borde==1){
                  //ciclo 1 un contrato
                  scope.invoices = scope.invoicesByContract2[1].slice(0, 10);
                  scope.auxScroll = scope.invoicesByContract2[1];
                  scope.pos_invoice = scope.invoices;
                  scope.isDetails = false;
                }else{
  
                  scope.pos_invoice=scope.invoicesByContract2[1].slice(0, 10);
                  scope.invoices = scope.invoices.concat(scope.invoicesByContract2[1].slice(0, 10));
                  scope.auxScroll = scope.auxScroll.concat(scope.invoicesByContract2[1]);
                  
                }
              }
              if(scope.cicle_contracts_borde==1){ 
                scope.invoicesLength = scope.invoicesByContract2[1].length;               
                scope.addressF = scope.invoicesByContract2[2];
                scope.contractsF = scope.invoicesByContract2[0];
                scope.title = config.title_invoice;
                scope.details_url = config.details_url;
              } else{
                scope.invoicesLength = scope.invoicesLength + scope.invoicesByContract2[1].length; 
              }
              //asigna el arreglo del contrato a arreglo total en posicion específica
              scope.invoicesByContract=scope.invoices.concat(scope.invoicesByContract);
              scope.cont_general[scope.posi]= scope.pos_invoice[0].contract;
              scope.posi++;            
              

            }else{//if son varios contratos              
              // Variables necesarias para ciclo de varios contratos
              scope.ind_begin   =0;  // contabiliza la cantidad de facturas para cada contrato
              scope.flag_varios =1;  // permite conocer si se ejecuto un ciclo de varios contratos
              scope.contrato    = 1; // notifica que zona de la plantilla user(varios contratos)
              scope.can_uno++;  // contador de veces que se ejecutan varios contratos

              if(scope.cicle_contracts_borde==1){ 
                scope.title = config.title_invoice;
                scope.details_url = config.details_url;
              }

              /* Se almacena la cantidad de contratos y su valor */
              
              resp.data[1].forEach(function(invoice_array, index) {                  
                invoice_array.forEach(function(invoice_one, ind){                       
                  scope.invoices_gc=invoice_one.contract;
                  
                });
                 
                scope.cont_general[scope.posi] = scope.invoices_gc;
                scope.invoices_gc = "";
                scope.posi++;                
              });
              /* se guardan los registros en arreglo por posicion para motrar posteriormente */
              resp.data[1].forEach(function(invoice_array, index) {                  
                invoice_array.forEach(function(invoice_one, ind){    
                  scope.invoices_mc[ind+scope.ind_cicle]=invoice_one;
                  scope.ind_begin=ind;
                });                
              });
              scope.ind_cicle+=scope.ind_begin;  // se guarda la cantidad final que se suma en cada ciclo de varios contratos
              
              /* Si es la primera vez de todo el ciclo, se guarda la info en una variable general 'invoicesByContract' */
              if(scope.cicle_contracts_borde==1){ 
                scope.invoicesByContract=scope.invoices_mc;
              }else{
                /* si no es la primera vez que se ejecuta la recursividad, 
                pero si es la primera vez en el ciclo de varios contratos */
                if(scope.flag_ini==1 && scope.can_uno==1){
                  scope.invoicesByContract=scope.invoices_mc;
                }else{
                  scope.invoicesByContract=scope.invoicesByContract.concat(scope.invoices_mc);
                  //scope.invoices_mc=scope.invoicesByContract;
                }

              }  
            }

            /* Verificar si quedan contratos borde por ejecutar */
            scope.cant_borde--;
            if(scope.cant_borde>0){
              return load_old_contracts(scope, config);
            }else{
                /* AL finalir el ciclo, si existieron clientes con un solo contrato y ademas varios contratos
                  , se anidan los casos de un solo contrato a todo el arreglo de varios contratos
               */              
                if(scope.flag_ini==1 && scope.cicle_contracts_borde>0){
                  
                  scope.invoicesByContract=scope.invoices.concat(scope.invoicesByContract);
                  scope.invoices_mc=scope.invoices.concat(scope.invoices_mc);              
                }

                /* si solo existen casos de un solo contrato se agrega el arreglo 2 al general, 
                para que lo reconozca toda la funcionalidad de angular */
                if(scope.flag_varios!=1){
                  
                  scope.invoicesByContract=scope.invoicesByContract2;
                   
                }
                scope.aux_invoices_mc = scope.invoices_mc;
                scope.aux_num_contract = scope.cont_general;              
            }

                    
                  
          }
        }, function () {
        scope.show_mesagge_data = "Error obteniendo los datos de Facturas";
        scope.alertas_servicios_current_invoice();
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
    $scope.sendDetail = function ($event, doc_type, doc_number, contractId, type, detail, payment_reference, address, city, line, invoiceId, state, country, zipcode, msisdn, client_code) {
      $event.preventDefault();
      var url = '/billing/session/data/bo?_format=json';
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
        "client_code": client_code,
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
    
    jQuery('.Contrato label').addClass('active');     

    //se eliminan los filtros aplicados en address
    jQuery( "div" ).remove( ".chip.ng-binding.ng-scope");

    var config = drupalSettings.currentInvoiceBlock[$scope.uuid_data_ng_current_invoice];

    //Get value filters
    var parameters = {};
    for (filter in config['filters']) {
      $scope[filter] = '';
    }
  });

  }
}

