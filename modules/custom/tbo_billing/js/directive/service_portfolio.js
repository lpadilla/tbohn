/**
 * @file
 * Implements directive ngServicePortfolio.
 */

myApp.directive('ngServicePortfolio', ['$http', 'apiBatch', 'dataCollector', ngCurrentInvoice]);

function ngCurrentInvoice($http, apiBatch, dataCollector) {

  var directive = {
    restrict: 'EA',
    controller: CurrentInvoiceController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    // Declare variables.
    scope.config = drupalSettings.b2bBlock[scope.uuid_data_ng_service_portfolio];
    scope.environment = scope.config['environment'];
    scope.environment_enterprise = scope.config['environment_enterprise'];
    scope.category = {};
    scope.categoryNameId = {};
    scope.quantiyCategory = 0;
    scope.quantiyScroll = Number(scope.config['scroll']);
    scope.hover = true;
    scope.selectedCategory = [];
    scope.search = '';
    scope.invoicesByContract = {};
    scope.alldata = {};
    scope.auxScroll = {};
    scope.both = false;
    scope.resultFixed = false;
    scope.movilFixed = false;
    scope.loadingInit = true;
    scope.suggestionsAutocomplete = [];
    scope.selectedIndexAutocomplete = -1;
    scope.labelCategory = scope.config['labelCategory'];
    scope.loadFilter = false;
    scope.notfilter = false;
    scope.show_mesagge = false;
    scope.show_mesagge_fixed = false;
    scope.show_mesagge_movil = false;
    scope.show_mesagge_data = "";
    scope.show_mesagge_data_batch = "";

    // Get paramter for filter.
    scope.parameterFilter = getParameterByName('category');

    // Validate environment.
    if (scope.config['environment_enterprise'] == 'both') {
      scope.both = true;
      scope.apiBatchBoth(el);
    }
    else if (scope.config['environment_enterprise'] == 'movil') {
      var search_key = {};
      search_key = {
        key: scope.config['company']['number'],
        document_type: scope.config['company']['document']
      };

      var api = new apiBatch("get_portfolio_movil_data", search_key);

      api.init();

      scope.dataCollector = function () {
        return dataCollector.getData();
      }

      scope.$watch(scope.dataCollector, function (v) {
        if (Object.keys(v).length > 0) {
          if (Object.keys(v)[0] == 0) {
            scope.show_mesagge_movil = true;
            scope.show_mesagge_data = "En este momento no podemos obtener la información de tus servicios movil.";
            scope.alertas_servicios();
          }
          else {
            scope.invoicesByContract = v;
            scope.alldata = v;

            // Scroll.
            scope.invoices = scope.scroll();
            scope.auxScroll = v;

            // Load category and autocomplete.
            scope.categoryAutocomplete();
          }
        }
      });
    }
    else if (scope.config['environment_enterprise'] == 'fijo') {
      retrieveInformation(scope, scope.config, el);
    }

    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };

    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        if (scope.invoicesList.error) {
          jQuery("div.actions", el).hide();
        }

        jQuery('.messages .close').on('click', function () {
          jQuery('.main-top .messages').hide();
        });
      }
    });

    // Closet suggestion.
    scope.closeSuggestions = function () {
      jQuery(".collections.suggestions").css("display", "none");
    }
    setTimeout(scope.closeSuggestions)

    /**
     * @param String name
     * @return String
     */
    function getParameterByName(name) {
      name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
      var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
      return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }

    // Get categories and another data.
    scope.categoryAutocomplete = function () {
      // Load category and autocomplete.
      var category = {};
      var duplicate = [];
      var categoryOrder = [];
      var categoryOther = {};

      // Load data autocomplete.
      var autocomplete = [];
      var options = [];
      var duplicate_autocomplete = [];
      var counter = 0;
      for (var i in scope.alldata) {
        if (scope.alldata.hasOwnProperty(i)) {
          for (var j in scope.alldata[i]) {
            if (scope.alldata[i].hasOwnProperty(j)) {
              if (!duplicate[scope.alldata[i][j]['category_name']]) {
                duplicate[scope.alldata[i][j]['category_name']] = 'exist';
                scope.categoryNameId[scope.alldata[i][j]['productId']] = scope.alldata[i][j]['category_name'];
                categoryOrder.push(scope.alldata[i][j]['category_name']);
              }

              if (scope.alldata[i][j]['msisdn']) {
                options = [];
                if (!duplicate_autocomplete[scope.alldata[i][j]['msisdn']]) {
                  duplicate_autocomplete[scope.alldata[i][j]['msisdn']] = 'exist';
                  options['name'] = scope.alldata[i][j]['msisdn'];
                  autocomplete.push(options);
                }
              }

              if (scope.alldata[i][j]['service_contract']) {
                options = [];
                if (!duplicate_autocomplete[scope.alldata[i][j]['service_contract']]) {
                  duplicate_autocomplete[scope.alldata[i][j]['service_contract']] = 'exist';
                  options['name'] = scope.alldata[i][j]['service_contract'];
                  autocomplete.push(options);
                }
              }

              if (scope.alldata[i][j]['address']) {
                options = [];
                if (!duplicate_autocomplete[scope.alldata[i][j]['address']]) {
                  duplicate_autocomplete[scope.alldata[i][j]['address']] = 'exist';
                  options['name'] = scope.alldata[i][j]['address'];
                  autocomplete.push(options);
                }
              }
            }
          }
        }
      }

      // Order Category.
      categoryOrder.sort();
      for (var i = 0; i < categoryOrder.length; i++) {
        categoryOther = {};
        categoryOther['name'] = categoryOrder[i];
        category[counter] = categoryOther;
        counter = counter + 1;
      }
      // Set quantity category.
      scope.quantiyCategory = counter;
      // Set category.
      scope.category = category;

      // Validate paramter filter in url.
      if (scope.parameterFilter) {
        for (var i in scope.category) {
          if (scope.category[i]['name'] == scope.categoryNameId[scope.parameterFilter]) {
            scope.labelCategory = scope.categoryNameId[scope.parameterFilter];
            scope.loadFilter = true;
            scope.changeCategory();
          }
        }
      }

      // Set data autocomplete.
      scope.autocomplete = autocomplete;
    }

    // Function searchAutocomplete.
    scope.searchAutocomplete = function (key_data) {
      jQuery(".collections.suggestions").css("display", "block");
      if (typeof key_data !== 'undefined' && key_data == 'search') {
        if (scope[key_data] === '') {
          scope.autocompleteChange('');
          scope.suggestionsAutocomplete = scope.autocomplete;
          scope.selectedIndexAutocomplete = -1;
        }
        else {
          var aux = [];
          scope.autocomplete.forEach(function (value, key, array) {
            if (value.name.search(scope[key_data]) > -1) {
              aux.push(value);
            }
          });
          scope.suggestionsAutocomplete = aux;
          scope.selectedIndexAutocomplete = -1;
        }
      }
    };

    function efecto(value, key_data) {
      $str = (value.name + "");
      $search = key_data + "";
      $pos = $str.indexOf($search);

      if ($pos >= 0) {
          value.name = "<strong>" + $str.substr($pos, $search.length) + "</strong>" + (($str.length - $search.length) > 0 ? $str.substr($search.length, $str.length) : "");
      }
      return value;
    }

    // Validate click in search.
    scope.searchAutocompleteClick = function (value) {
      if (Object.keys(scope.invoicesByContract).length > 0) {
        scope.notfilter = true;
        scope.searchAutocomplete(value);
      }
    }

    // Get filter for keyCode.
    scope.checkKeyDownReference = function (event, field) {
      if (event.keyCode == 8) {
        scope.notfilter = false;
      }

      if (event.keyCode === 40) {
        // Down key, increment selectedIndex.
        event.preventDefault();
        if (scope.selectedIndexAutocomplete + 1 !== scope.suggestionsAutocomplete.length) {
          scope.selectedIndexAutocomplete++;
        }
      }
      else if (event.keyCode === 38) {
        // Up key, decrement selectedIndex.
        event.preventDefault();
        if (scope.selectedIndexAutocomplete - 1 !== -1) {
          scope.selectedIndexAutocomplete--;
        }
      }
      else if (event.keyCode === 13) {
        // Enter pressed.
        scope.resultClickedAutocomplete(scope.selectedIndexAutocomplete, field);
      }
      else {
        scope.suggestionsAutocomplete = [];
      }
    };

    // Get data for filter search.
    scope.resultClickedAutocomplete = function (index, item) {
      if (index != -1) {
        field = item.slice(0, 9).trim();
        display = item.slice(10, 16).trim();
        scope[field] = '';
        scope.autocompleteChange(scope.suggestionsAutocomplete[index].name);
        scope.closeSuggestions();
      }
    };

    scope.autocompleteChange = function (option) {
      var length = 0;
      a = {};
      var aux = [];
      var notFilter = false;
      if (option === '') {
        if (scope.selectedCategory.length == 0) {
          a = scope.alldata;
          notFilter = true;
        }
        else {
          notFilter = true;
          scope.changeCategory();
        }
      }
      else {
        scope.notfilter = false;
        a = {};
        var aux = [];
        if (scope.selectedCategory.length == 0) {
          for (var i in scope.alldata) {
            if (scope.alldata.hasOwnProperty(i)) {
              for (var j in scope.alldata[i]) {
                if (scope.alldata[i].hasOwnProperty(j)) {
                  if (scope.alldata[i][j].msisdn == option || scope.alldata[i][j].service_contract == option || scope.alldata[i][j].address == option) {
                    length = length + 1;
                    aux = [];
                    if (a[i]) {
                      aux = [];
                      for (key in a) {
                        if (scope.alldata.hasOwnProperty(key)) {
                          if (key == i) {
                            for (value in a[key]) {
                              if (scope.alldata[key].hasOwnProperty(value)) {
                                aux.splice(option, 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(option, 0, scope.alldata[i][j]);
                      a[i] = aux;
                    }
                    else {
                      aux.splice(i, 0, scope.alldata[i][j]);
                      a[i] = aux;
                    }
                  }
                }
              }
            }
          }
        }
        else {
          scope.loadDataAutocomplete();
          a = {};
          for (var i in scope.invoices) {
            if (scope.invoices.hasOwnProperty(i)) {
              for (var j in scope.invoices[i]) {
                if (scope.invoices[i].hasOwnProperty(j)) {
                  if (scope.invoices[i][j].msisdn == option || scope.invoices[i][j].service_contract == option || scope.invoices[i][j].address == option) {
                    length = length + 1;
                    aux = [];
                    if (a[i]) {
                      aux = [];
                      for (key in a) {
                        if (scope.invoices.hasOwnProperty(key)) {
                          if (key == i) {
                            for (value in a[key]) {
                              if (scope.invoices[key].hasOwnProperty(value)) {
                                aux.splice(option, 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(option, 0, scope.invoices[i][j]);
                      a[i] = aux;
                    }
                    else {
                      aux.splice(j, 0, scope.invoices[i][j]);
                      a[i] = aux;
                    }
                  }
                }
              }
            }
          }
        }
      }
      scope.search = option;
      jQuery('#tagsList label').addClass('active');

      if (length > 0) {
        scope.auxScroll = a;
      }
      else if (option === '') {
        scope.auxScroll = a;
      }
      else {
        a['No existen datos que coincidan con los criterios de busqueda'] = {};
        scope.auxScroll = a;
      }

      // Load data.
      scope.invoices = scope.scrollAux();

      if (!notFilter) {
        if (length > 0) {
          scope.saveAuditLog(option, scope.selectedCategory);
        }
      }
    };

    // Function to filter data.
    scope.autocompleteChangeCategory = function (option) {
      var length = 0;
      a = {};
      var aux = [];
      for (var i in scope.alldata) {
        if (scope.alldata.hasOwnProperty(i)) {
          for (var j in scope.alldata[i]) {
            if (scope.alldata[i].hasOwnProperty(j)) {
              if (scope.alldata[i][j].msisdn == option || scope.alldata[i][j].service_contract == option || scope.alldata[i][j].address == option) {
                length = length + 1;
                aux = [];
                if (a[i]) {
                  aux = [];
                  for (key in a) {
                    if (scope.alldata.hasOwnProperty(key)) {
                      if (key == i) {
                        for (value in a[key]) {
                          if (scope.alldata[key].hasOwnProperty(value)) {
                            aux.splice(option, 0, a[key][value]);
                          }
                        }
                      }
                    }
                  }
                  aux.splice(option, 0, scope.alldata[i][j]);
                  a[i] = aux;
                }
                else {
                  aux.splice(j, 0, scope.alldata[i][j]);
                  a[i] = aux;
                }
              }
            }
          }
        }
      }

      if (length > 0) {
        scope.auxScroll = a;
      }
      else {
        a['No existen datos que coincidan con los criterios de busqueda'] = {};
        scope.auxScroll = a;
      }

      scope.invoices = scope.scrollAux(length);
      // Save audit log.
      if (scope.selectedCategory.length == 0) {
        if (length > 0) {
          scope.saveAuditLog(option, []);
        }
      }
    };

    scope.loadDataAutocomplete = function () {
      a = {};
      var aux = [];
      for (var i in scope.alldata) {
        if (scope.alldata.hasOwnProperty(i)) {
          for (var j in scope.alldata[i]) {
            if (scope.alldata[i].hasOwnProperty(j)) {
              for (var c = 0; c < scope.selectedCategory.length; c++) {
                if (scope.alldata[i][j].category_name == scope.selectedCategory[c]) {
                  length = length + 1;
                  aux = [];
                  if (a[i]) {
                    aux = [];
                    for (key in a) {
                      if (scope.alldata.hasOwnProperty(key)) {
                        if (key == i) {
                          for (value in a[key]) {
                            if (scope.alldata[key].hasOwnProperty(value)) {
                              aux.splice(scope.selectedCategory[c], 0, a[key][value]);
                            }
                          }
                        }
                      }
                    }
                    aux.splice(scope.selectedCategory[c], 0, scope.alldata[i][j]);
                    a[i] = aux;
                  }
                  else {
                    aux.splice(j, 0, scope.alldata[i][j]);
                    a[i] = aux;
                  }
                }
              }
            }
          }
        }
      }
      scope.auxScroll = a;
      // Load data.
      scope.invoices = scope.scrollAux(1000);
    }

    // Infinite scroll.
    scope.loadMore = function () {
      if (scope.sizeAuxScroll() > 0) {
        var sizeInvoice = scope.sizeInvoice();
        scope.invoices = scope.scrollAux(sizeInvoice);
      }
      else if (typeof scope.invoicesByContract[1] != 'undefined') {
        scope.invoices = scope.scroll(sizeInvoice);
      }
    }

    // Function to filter for category.
    scope.changeCategory = function ($event) {
      var length = 0;
      var notFilter = false;

      if (scope.loadFilter === false) {
        scope.labelCategory = scope.config['labelCategory'];
      }

      if (scope.loadFilter) {
        scope.selectedCategory[0] = scope.categoryNameId[scope.parameterFilter];
        scope.loadFilter = false;
      }

      var aux = [];
      if (scope.selectedCategory.length == 0) {
        if (scope.search == '') {
          a = scope.alldata;
          notFilter = true;
        }
        else {
          notFilter = true;
          scope.notfilter = false;
          scope.autocompleteChangeCategory(scope.search);
          a = scope.invoices;
        }
      }
      else {
        a = {};
        var aux = [];
        if (scope.search == '') {
          if (scope.notfilter) {
            notFilter = true;
          }
          scope.notfilter = false;
          for (var i in scope.alldata) {
            if (scope.alldata.hasOwnProperty(i)) {
              for (var j in scope.alldata[i]) {
                if (scope.alldata[i].hasOwnProperty(j)) {
                  for (var c = 0; c < scope.selectedCategory.length; c++) {
                    if (scope.alldata[i][j].category_name == scope.selectedCategory[c]) {
                      length = length + 1;
                      aux = [];
                      if (a[i]) {
                        aux = [];
                        for (key in a) {
                          if (scope.alldata.hasOwnProperty(key)) {
                            if (key == i) {
                              for (value in a[key]) {
                                if (scope.alldata[key].hasOwnProperty(value)) {
                                  aux.splice(scope.selectedCategory[c], 0, a[key][value]);
                                }
                              }
                            }
                          }
                        }
                        aux.splice(scope.selectedCategory[c], 0, scope.alldata[i][j]);
                        a[i] = aux;
                      }
                      else {
                        aux.splice(j, 0, scope.alldata[i][j]);
                        a[i] = aux;
                      }
                    }
                  }
                }
              }
            }
          }
        }
        else {
          scope.autocompleteChangeCategory(scope.search);
          a = [];
          for (var i in scope.invoices) {
            if (scope.invoices.hasOwnProperty(i)) {
              for (var j in scope.invoices[i]) {
                if (scope.invoices[i].hasOwnProperty(j)) {
                  for (var c = 0; c < scope.selectedCategory.length; c++) {
                    if (scope.invoices[i][j].category_name == scope.selectedCategory[c]) {
                      length = length + 1;
                      aux = [];
                      if (a[i]) {
                        aux = [];
                        for (key in a) {
                          if (scope.invoices.hasOwnProperty(key)) {
                            if (key == i) {
                              for (value in a[key]) {
                                if (scope.invoices[key].hasOwnProperty(value)) {
                                  aux.splice(scope.selectedCategory[c], 0, a[key][value]);
                                }
                              }
                            }
                          }
                        }
                        aux.splice(scope.selectedCategory[c], 0, scope.invoices[i][j]);
                        a[i] = aux;
                      }
                      else {
                        aux.splice(j, 0, scope.invoices[i][j]);
                        a[i] = aux;
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }

      if (length > 0) {
        scope.auxScroll = a;
      }
      else if (scope.selectedCategory.length === 0) {
        scope.auxScroll = a;
      }
      else {
        a['No existen datos que coincidan con los criterios de busqueda'] = {};
        scope.auxScroll = a;
      }

      // Load data.
      scope.invoices = scope.scrollAux();

      if (!notFilter) {
        if (length > 0) {
          scope.saveAuditLog(scope.search, scope.selectedCategory);
        }
      }
    }
  }

  function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
      results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, ""));
  }

  // Function por retrieve information in fixed.
  function retrieveInformation(scope, config, el) {
    scope.result = config.config_type;

    if (scope.resources.indexOf(config.url) == -1) {
      // Add key for this display.
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
            scope.alertas_servicios();
          }
          else {
            scope.invoicesByContract = resp.data;
            scope.alldata = resp.data;
            scope.category_name = resp.category_name;

            // Scroll.
            scope.invoices = scope.scroll();
            scope.auxScroll = resp.data;

            // Load category and autocomplete.
            scope.categoryAutocomplete();
          }
          jQuery(el).parents("section").fadeIn('slow');
        }, function () {
          console.log("Error obteniendo los datos");
        });
    }
  }

  // Retrieve information both.
  function retrieveInformationBoth(scope, config, el) {
    scope.result = config.config_type;

    if (scope.resources.indexOf(config.url) == -1) {
      // Add key for this display.
      var parameters = {};
      parameters['config_columns'] = config.config_columns;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      $http.get(config.url, config_data)
        .then(function (resp) {
          if (resp.data.error) {
            scope.resultFixed = true;
            scope.show_mesagge_data = resp.data.message;
            scope.alertas_servicios();
          }
          if (resp.data == '') {
            scope.resultFixed = true;
          }
          else {
            scope.resultFixed = resp.data;
          }
        }, function () {
          console.log("Error obteniendo los datos");
        });
    }
  }

  // Controller CurrentInvoiceController.
  CurrentInvoiceController.$inject = ['$scope', '$http'];

  function CurrentInvoiceController($scope, $http) {
    // Init vars.
    if (typeof $scope.invoicesList == 'undefined') {
      $scope.invoicesList = "";
      $scope.invoicesList.error = false;
    }

    if (typeof $scope.resources == 'undefined') {
      $scope.resources = [];
    }

    var package = {};

    // Add config to url.
    var config_data = {
      params: package,
      headers: {'Accept': 'application/json'}
    };

    // Send data btn detail - pending implementation.
    $scope.sendDetail = function ($event, contractId, address, category, status, plan, productId, subscriptionNumber, serviceType, measuringElement) {
      $event.preventDefault();
      var url = '/billing/session/data?_format=json';
      var parameters = {};

      parameters['data'] = JSON.stringify({
        "service_detail": 1,
        "contractId": contractId,
        "address": address,
        "category": category,
        "status": status,
        "plan": plan,
        "productId": productId,
        "subscriptionNumber": subscriptionNumber,
        "serviceType": serviceType,
        "measuringElement": measuringElement,
      });

      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      $http.get(url, config_data).then(function (resp) {
        window.location.href = $event.target.href;
      }, function () {
        console.log("Error obteniendo los datos");
      });
    }

    // Function to load scroll.
    $scope.scroll = function (size) {
      var aux = [];
      scroll = {};

      if (size === undefined) {
        var quantity = $scope.quantiyScroll;
      }
      else {
        var quantity = size + $scope.quantiyScroll;
      }

      var group;
      for (var i in $scope.invoicesByContract) {
        aux = [];
        group = i;
        if ($scope.invoicesByContract.hasOwnProperty(i)) {
          if (quantity > 0) {
            if ($scope.invoicesByContract[i].length > 0) {
              var slice = quantity;
              if (quantity > $scope.invoicesByContract[i].length) {
                slice = $scope.invoicesByContract[i].length;
              }
              aux.splice(i, 0, $scope.invoicesByContract[i].slice(0, slice));
              scroll[i] = aux[0];
              quantity = quantity - $scope.invoicesByContract[i].length;
              if (quantity < 0) {
                scroll[i] = aux[0];
              }
            }
          }
        }
      }

      if (quantity > 0) {
        scroll[group] = aux[0];
        aux = [];
      }

      return scroll;
    }

    // Scroll Aux.
    $scope.scrollAux = function (size) {
      var aux = [];
      scroll = {};

      if (size === undefined) {
        var quantity = $scope.quantiyScroll;
      }
      else {
        var quantity = size + $scope.quantiyScroll;
      }
      var group;
      for (var i in $scope.auxScroll) {
        aux = [];
        group = i;
        if ($scope.auxScroll.hasOwnProperty(i)) {
          if (quantity > 0) {
            if ($scope.auxScroll[i].length > 0) {
              var slice = quantity;
              if (quantity > $scope.auxScroll[i].length) {
                slice = $scope.auxScroll[i].length;
              }
              aux = [];
              aux.splice(i, 0, $scope.auxScroll[i].slice(0, slice));
              scroll[i] = aux[0];
              quantity = quantity - $scope.auxScroll[i].length;
              if (quantity == 0 || quantity < 0) {
                scroll[i] = aux[0];
              }
            }
          }
        }
      }

      if (quantity > 0) {
        scroll[group] = aux[0];
        aux = [];
      }

      return scroll;
    }

    // Calculate size invoices.
    $scope.sizeInvoice = function () {
      var size = 0;
      for (var i in $scope.invoices) {
        if ($scope.invoices.hasOwnProperty(i)) {
          size = size + $scope.invoices[i].length;
        }
      }

      return size;
    }

    // Show message service.
    $scope.alertas_servicios = function () {
      jQuery(".block-portfolio .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
      $html_mensaje = jQuery('.block-portfolio .messages-only').html();
      jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

      jQuery('.messages .close').on('click', function () {
        jQuery('.messages').hide();
      });
    }

    // Show message service mobile batch.
    $scope.alertas_servicios_batch = function () {
      jQuery(".block-portfolio .messages-batch .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_batch + '</p></div>');
      $html_mensaje = jQuery('.block-portfolio .messages-batch').html();
      jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

      jQuery('.messages .close').on('click', function () {
        jQuery('.messages').hide();
      });
    }

    // Calculate size aux scroll.
    $scope.sizeAuxScroll = function () {
      var size = 0;
      for (var i in $scope.auxScroll) {
        if ($scope.auxScroll.hasOwnProperty(i)) {
          size = size + $scope.auxScroll[i].length;
        }
      }

      return size;
    }

    // Function to enterprise both.
    $scope.apiBatchBoth = function (el) {
      $scope.counter = 0;
      $scope.apiIsLoadingBoth = function () {
        return $http.pendingRequests.length > 0;
      };

      $scope.$watch($scope.apiIsLoadingBoth, function (v) {
        if ($scope.loadingInit == true) {
          $scope.counter = $scope.counter + 1;
          if ($scope.counter > 1 && $scope.resultFixed != false) {
            $scope.loadingInit = false;

            for (var i in $scope.movilFixed) {
              $scope.invoicesByContract[i] = $scope.movilFixed[i];
            }

            if ($scope.resultFixed.error) {
              $scope.resultFixed = false;
            }
            for (var i in $scope.resultFixed) {
              $scope.invoicesByContract[i] = $scope.resultFixed[i];
            }

            if (Object.keys($scope.invoicesByContract).length > 0) {
              $scope.alldata = $scope.invoicesByContract;
              $scope.auxScroll = $scope.invoicesByContract;

              // Scroll.
              $scope.invoices = $scope.scroll();

              // Load category and autocomplete.
              $scope.categoryAutocomplete();
            }
          }
        }
      });

      var search_key = {};
      search_key = {
        key: $scope.config['company']['number'],
        document_type: $scope.config['company']['document']
      };

      var api = new apiBatch("get_portfolio_movil_data", search_key);

      api.init();

      $scope.dataCollector = function () {
        return dataCollector.getData();
      }

      $scope.$watch($scope.dataCollector, function (v) {
        if (Object.keys(v).length > 0 || $scope.counter > 1) {
          if (v != "") {
            if (v.error) {
              $scope.show_mesagge_data_batch = v.message;
              $scope.alertas_servicios_batch();
            }
            else {
              $scope.movilFixed = v;
            }
          }

          retrieveInformationBoth($scope, $scope.config, el);
        }
      });
    }

    // Function to save audit log.
    $scope.saveAuditLog = function (exactSearch, category) {
      if ($scope.quantiyCategory == 1) {
        category = [$scope.category[0]['name']];
      }

      var params = {
        'exactSearch': exactSearch,
        'category': category,
      };

      if ($scope.resources.indexOf($scope.config.url) == -1) {
        $http.get('/rest/session/token').then(function (resp) {
          $http({
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': resp.data
            },
            data: params,
            url: $scope.config.url
          }).then(function successCallback(response) {
          }, function errorCallback(response) {
          });
        });
      }
    }

    // Donwnload portfolio.
    $scope.downloadPortfolio = function () {
      var config = drupalSettings.b2bBlock[$scope.uuid_data_ng_service_portfolio];

      jQuery("#download_portfolio").attr('disabled', 'disabled');

      jQuery(".messages").remove();
      jQuery(".block-portfolio .messages-only .text-alert .txt-message").remove();

      $http.get('/rest/session/token')
        .then(function (response) {
          $http({
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': response.data
            },
            data: {},
            url: config['exportPortfolioUrl'],
          })
          .then(
            function successCallback(response) {
              if (!response.data.error) {
                if (response.data.file != '') {
                  window.open('/adf_core/download-example/' + response.data.file + '/' + response.data.path, '_blank');
                }
                else {
                  $scope.show_mesagge_data = Drupal.t('No se encontraron servicios.');
                  $scope.alertas_servicios();
                }
              }
              else {
                $scope.show_mesagge_data = response.data.message_error;
                $scope.alertas_servicios();
              }

              jQuery("#download_portfolio").removeAttr('disabled');
            },
            function errorCallback(response) {
              // Called asynchronously if an error occurs
              // or server returns response with an error status.
              jQuery("#download_portfolio").removeAttr('disabled');
              $scope.show_mesagge_data = Drupal.t('Ha ocurrido un error.<br>La solicitud no pudo procesarse correctamente, por favor inténtelo más tarde.');
              $scope.alertas_servicios();
            }
          );
        });
    }
  }
}
