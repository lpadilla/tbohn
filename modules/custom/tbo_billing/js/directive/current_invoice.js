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
        scope.delete_from_cache = 0;
        scope.isDetailsNew = config['isDetail'];

        if (scope.environment != '') {
            retrieveInformation(scope, config, el);
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

        scope.showContent = function (event) {
            var $parent = jQuery(event.target).closest('.js-dropdown-button')
            var $button = jQuery('.js-dropdown-button')
            if ($parent.hasClass('active')) {
                $button.removeClass('active')
                $button.addClass('inactive')
            } else {
                $parent.addClass('active')
                $parent.removeClass('inactive')
            }
        }

        var aux_contract = [];
        var aux_address = [];
        var aux_invoices = [];
        var aux_contractD = [];
        var aux_addressD = [];

        scope.closeSuggestions = function () {
            document.getElementById('suggestionsContract').style.display = 'none';
            document.getElementById('suggestionsAddress').style.display = 'none';
            document.getElementById('suggestionsReferences').style.display = 'none';
        }
        setTimeout(scope.closeSuggestions)

        scope.filtersChangeDesktop = function (type, checked, $event) {

            if (type == 'contract' && checked) {
                if (scope.contractOptions.length < 5) {
                    scope.suggestionsContract.forEach(function (item, key, array) {
                        item.name = item.name.replace("<strong>", "").replace("</strong>", "");
                        jQuery('#check-' + item.name).removeAttr('disabled');
                    })

                    aux_contractD.push($event.target.value.replace("<strong>", "").replace("</strong>", ""));
                    if (scope.contractOptions.length == 5) {
                        scope.suggestionsContract.forEach(function (item, key, array) {
                            if (aux_contractD.indexOf(item.name) == -1) {
                                item.name = item.name.replace("<strong>", "").replace("</strong>", "");
                                jQuery('#check-' + item.name).attr('disabled', 'true');
                            }
                        })
                    }
                } else {
                    scope.suggestionsContract.forEach(function (item, key, array) {
                        // console.log(item.name);
                        if (aux_contractD.indexOf(item.name) == -1) {
                            item.name = item.name.replace("<strong>", "").replace("</strong>", "");
                            jQuery('#check-' + item.name).attr('disabled', 'true');
                        }
                    })
                }
            } else if (type == 'contract' && !checked) {
                index = aux_contractD.indexOf($event.target.value);
                if (index > -1) {
                    aux_contractD.splice(index, 1);
                    scope.suggestionsContract.forEach(function (item, key, array) {
                        item.name = item.name.replace("<strong>", "").replace("</strong>", "");
                        jQuery('#check-' + item.name).removeAttr('disabled');
                    })
                }
            }

            if (type == 'address' && checked) {
                if (scope.addressOptions.length < 5) {

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
            } else if (type == 'address' && !checked) {
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
            scope.contractOptions = aux_contractD;
            scope.filtersChange();

        }

        scope.filtersChangeMobile = function (type, checked, $event) {

            if (type == 'contract' && checked) {
                aux_contract.push($event.target.id.replace("<strong>", "").replace("</strong>", ""));

            } else if (type == 'contract' && !checked) {
                index = aux_contract.indexOf($event.target.id);
                aux_contract.splice(index);
            }

            if (type == 'address' && checked) {

                // console.log('YO:'+$event.target.id);
                aux_address.push($event.target.id.replace("<strong>", "").replace("</strong>", ""));

            } else if (type == 'address' && !checked) {
                index = aux_address.indexOf($event.target.id);
                // console.log('YO2:'+index);
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
            scope.contractOptions = aux_contract;
            scope.filtersChange();
        };

        scope.filtersChange = function () {
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

            if (typeof scope.contractOptions !== 'undefined' && scope.contractOptions.length > 0) {
                scope.aux_filters = 1;
                scope.contractOptions.forEach(function (filter, key, array) {
                    aux_resp.forEach(function (bill, pos, array1) {
                        if (filter == bill.contract) {
                            aux [pos] = bill;
                        }
                    })
                })
                aux_resp = aux;
                aux = [];
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
                                //console.log(a.invoice_value, b.invoice_value);
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
            scope.auxScroll = aux_resp;
            scope.auxScroll = scope.groupInvoices(scope.auxScroll);
            scope.invoices = scope.auxScroll.slice(0, 10);
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
                        if (i == 0) {
                            result[invoice][i]['address_show'] = 1;
                        } else {
                            result[invoice][i]['address_show'] = 0;
                        }
                        group_array.push(result[invoice][i]);
                    }
                }
            }
            return group_array;
        }

        scope.invoicesToPay = [];
        scope.multiple_status = true;

        scope.addRemoveInvoicePayment = function (invoice, index) {
            if (scope.multiple_status && scope.firts_delete_multiple) {
                if (scope.getOrigin() == 0 && scope.delete_from_cache == 0) {
                    scope.invoicesToPay = [];
                } else {
                    scope.invoicesToPay = scope.getObjectInvoices();
                }
                scope.multiple_status = false;
            } else {
                scope.invoicesToPay = scope.getObjectInvoices();
            }

            if (scope.invoices[index]['add_multiple'] == 0) {
                var index_erase;
                scope.invoicesToPay.forEach(function (item, key, array) {
                    if (item.invoiceId == invoice.invoiceId) {
                        index_erase = key;
                    }
                })
                scope.invoicesToPay.splice(index_erase, 1);
                scope.invoices[index]['add_multiple'] = 1;
            } else {
                scope.invoices[index]['add_multiple'] = 0;
                scope.invoicesToPay.push(scope.invoices[index]);
            }

            scope.animar_bton(index);
            scope.firts_delete_multiple = false;
            scope.setInvoicesToPay(scope.invoicesToPay, 'selected');
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
        scope.referenceOptions = []
        scope.suggestionsReference = [];
        scope.selectedIndexReference = -1; //currently selected suggestion index
        scope.aux_filters;
        scope.result;
        scope.type_delete_multiple = true;
        scope.firts_delete_multiple = true;
        scope.total_invoices_pending = 0;

        scope.invoicesFilterOptions = {
            'all': 'Todas las Facturas',
            'slopes': 'Facturas pendientes',
            'overdue': 'Facturas vencidas',
            'paid': 'Facturas pagadas',
            'adjusted': 'Facturas ajustadas'
        };

        scope.searchContract = function (key_data) {
            document.getElementById('suggestionsContract').style.display = 'block';
            if (typeof key_data !== 'undefined' && key_data == 'contract') {
                var aux = [];
                scope.invoicesByContract[0].forEach(function (value, key, array) {
                    value.name = value.name.replace("<strong>", "").replace("</strong>", "");
                    if (value.name.search(scope[key_data]) > -1) {

                        aux.push(efecto(value, scope[key_data]));
                        //aux.push(value);
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
                        // value.name += "<strong>hola</strong>";
                        //aux.push(value);
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

                scope.filtersChange();
                scope.suggestionsAddress = [];
                scope.suggestionsAddressMobile = scope.suggestionsAddress;
                jQuery('#addressM').material_select();
                angular.element('#address').triggerHandler('click');
            }
        };

        scope.removeChipAddress = function (key) {
            var index = scope.addressOptions.indexOf(key);
            scope.addressOptions.splice(index, 1);
            scope.filtersChange();
        };

        scope.searchReference = function (key_data) {
            document.getElementById('suggestionsReferences').style.display = 'block';
            if (typeof key_data !== 'undefined' && key_data == 'reference') {
                var aux = [];
                scope.invoicesByContract[2].forEach(function (value, key, array) {
                    value.name = value.name.replace("<strong>", "").replace("</strong>", "");
                    if (value.name.search(scope[key_data]) > -1) {
                        //aux.push(value);
                        aux.push(efecto(value, scope[key_data]));
                    }
                });
                scope.invoicesByContract[0].forEach(function (value, key, array) {
                    value.name = value.name.replace("<strong>", "").replace("</strong>", "");
                    if (value.name.search(scope[key_data]) > -1) {
                        //aux.push(value);
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
        function efecto(value, key_data) {
            $str = (value.name + "");
            $search = key_data + "";
            $pos = $str.indexOf($search);

            if ($pos >= 0) {
                value.name = "<strong>" + $str.substr($pos, $search.length) + "</strong>" + (($str.length - $search.length) > 0 ? $str.substr($search.length, $str.length) : "");
                //value.name += ($str.length - $search.length)>0?$str.substr($search.length, $str.length):"";
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
            //scope.referenceOptions.push(scope.suggestionsAddress[index].name);
            //scope.referenceChange(scope.suggestionsReference[index].name);
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

        scope.status_group = true;
        scope.type_delete = true;

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
            scope.invoices.forEach(function (item, key, array) {
                scope.invoices[key]['add_multiple'] = scope.getTypeMultiple() == 'all' ? scope.invoices[key]['add_multiple'] : scope.getStatusMultiple(item);
            })


            if (scope.type_delete) {
                if (scope.status_group && scope.multiple_status && scope.firts_delete_multiple) {
                    scope.setTotal('normal');
                    if (typeof scope.invoicesByContract != 'undefined') {
                        scope.status_group = ((scope.invoices.length == scope.invoicesByContract[1]) ? false : true);
                    }
                }
                else{
                    scope.setTotal('init');
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
                // alert('hola');
                document.getElementById('filters-mobile-container').className = 'filters-mobile-container';
                document.getElementById('closed-btn-1').className = 'prefix icon-filters-cyan';
                // document.getElementById('.icon-filter > .material-icons, .filters-mobile .filters-mobile-container').removeClass('closed');
            }
            else {
                //alert('hola2');
                document.getElementById('filters-mobile-container').className = 'filters-mobile-container closed';
                document.getElementById('closed-btn-1').className = 'prefix icon-filters-cyan closed';
                //document.getElementById('.icon-filter > .material-icons, .filters-mobile .filters-mobile-container').addClass('closed');
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
            //document.getElementById('filters-mobile-container').style.display = 'none'
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
        scope.all_invoices = [];

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
                        scope.day_payment = 'lol';
                        scope.invoicesByContract = resp.data;
                        scope.all_invoices = resp.data[1];
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
                        scope.invoicesToPay = scope.invoices;
                        aux_invoices_payment = [];

                        scope.all_invoices.forEach(function (item, key, array) {
                            if (item.status != "PAGADA") {
                                if (scope.getOrigin() == 1) {
                                    item.add_multiple = 0;
                                }
                                if(item.invoice_value > 0) {
                                  aux_invoices_payment.push(item);
                                  scope.total_invoices_pending = scope.total_invoices_pending + 1;
                                }
                            }
                        })

                        classes = jQuery("#footer-top").attr('class');
                        if (classes.search('hide') != -1) {
                            jQuery(".footer-top.fijo").removeClass('hide');
                        }
                        scope.setTotal('normal');
                        scope.setInvoicesToPay(aux_invoices_payment, 'all');
                    }
                }, function () {
                    jQuery(".footer-top.fijo").addClass('hide');
                    console.log("Error obteniendo los datos");
                });
        }
    }


    CurrentInvoiceController.$inject = ['$scope', '$http', '$rootScope', 'multipleInvoices'];

    function CurrentInvoiceController($scope, $http, $rootScope, multipleInvoices) {
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
        $scope.sendDetail = function ($event, doc_type, doc_number, contractId, type, detail, payment_reference, address, city, line, invoiceId, state, country, zipcode) {
            $event.preventDefault();
            var url = '/billing/session/data?_format=json';
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
                "zipcode": zipcode
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

            //
        }

        //Show message service
        $scope.alertas_servicios_current_invoice = function () {
            jQuery(".block-currentinvoice .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
            $html_mensaje = jQuery('.block-currentinvoice .messages-only ').html();
            jQuery('.main-top').append('<div class="messages messages--success alert alert-pending">' + $html_mensaje + '</div>');
        }

        $scope.setInvoicesToPay = function (newValue, type) {
            multipleInvoices.setObject(newValue);
            multipleInvoices.setString(type);
            multipleInvoices.setType($scope.result);
            $rootScope.$emit("InvoicesToPayMethod", {});
        };

        $rootScope.$on("InvoicesToRemovePayMethod", function (event, invoice) {
            $scope.deleteInvoicePaymentInCurrent(invoice);
        });

        $scope.deleteInvoicePaymentInCurrent = function (invoice) {
            if (multipleInvoices.getString() == 'selected' && (!$scope.type_delete_multiple || $scope.getOrigin() == 1)) {
                $scope.invoices.forEach(function (item, key, array) {
                    if (item['payment_reference'] == invoice['payment_reference']) {
                        $scope.invoices[key]['add_multiple'] = 1;
                    }
                })
                $scope.multiple_status = false;
            } else {
                $scope.invoices.forEach(function (item, key, array) {
                    if (item['payment_reference'] != invoice['payment_reference']) {
                        if ($scope.firts_delete_multiple) {
                            $scope.invoices[key]['add_multiple'] = 0;
                        }
                    } else {
                        $scope.invoices[key]['add_multiple'] = 1;
                    }
                })
                $scope.type_delete_multiple = false;
            }
            $scope.firts_delete_multiple = false;
        }

        $scope.getObjectInvoices = function () {
            return multipleInvoices.getObject().data;
        }

        $scope.getOrigin = function () {
            return multipleInvoices.getOrigin();
        }

        $scope.setOrigin = function (value) {
            return multipleInvoices.setOrigin(value);
        }
        /***** efecto animar boton***/
        $scope.animar_bton = function (index) {


            var cart = jQuery('#button-payment');
            var imgtodrag = jQuery("#btn-add-" + index);


            if (imgtodrag) {
                var imgclone = imgtodrag.clone()
                    .offset({
                        top: imgtodrag.offset().top,
                        left: imgtodrag.offset().left
                    })
                    .css({
                        'opacity': '0.5',
                        'position': 'absolute',
                        'height': '36px',
                        'width': '138px',
                        'z-index': '10'
                    })

                    .appendTo(jQuery('body .wrapper-page'))
                    .animate({
                        'top': cart.offset().top + 10,
                        'left': cart.offset().left + 10,
                        'width': '138px',
                        'height': '36px'
                    }, 1000, 'easeInOutExpo');


                jQuery('#button-payment').addClass('shake-efect');

                /* setTimeout(function () {
                 cart.effect("shake", {
                 times: 2
                 }, 200);
                 }, 1500);
                 */

                imgclone.animate({'z-index': '0', 'opacity': '0'},
                    function () {
                        jQuery(this).detach();
                        jQuery('#button-payment').removeClass('shake-efect');
                    });

                //jQuery('#button-payment').removeClass('pruebas');
            }
        }

        $scope.setTotal = function (value) {
            multipleInvoices.setTotal($scope.total_invoices_pending);

            if (value == 'init') {
                $rootScope.$emit("InvoicesToPayMethod", {});
            }
        }

        $scope.getStatusMultiple = function (invoice) {
            data_invoices =  multipleInvoices.getObject();
            invoices_to_pay = data_invoices['data'];
            response = 1;

            invoices_to_pay.forEach(function (item, key, array) {
                if (item['payment_reference'] == invoice['payment_reference']){
                    response = 0;
                }
            })
            return response;
        }
        $scope.getTypeMultiple = function () {
            return multipleInvoices.getString();
        }

      $rootScope.$on("changeButtonByCache", function (event, data) {
        $scope.changeButtonByCache(data);
      });

      $scope.changeButtonByCache = function (cache_invoices) {
        $scope.auxScroll.forEach(function (item, key, array) {
          cache_invoices.forEach(function (value, index, arreglo) {
            if(item['payment_reference'] == value['payment_reference']){
                $scope.auxScroll[key]['add_multiple'] = 0;
                $scope.delete_from_cache = 1;
            }
          })
        })
      }
    }
}
