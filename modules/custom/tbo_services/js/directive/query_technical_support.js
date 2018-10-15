/**
 * @file
 * Implements query technical_support directive.
 */

myApp.directive('ngQueryTechnicalSupport', ['$http', ngQueryTechnicalSupport]);

function ngQueryTechnicalSupport($http) {

  var directive = {
    restrict: 'EA',
    controller: QueryTechnicalSupportController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    // Declare variables.
    scope.config = drupalSettings.queryTechnicalSupportBlock[scope.uuid_data_ng_query_technical_support];
    scope[scope.config['uuid']] = scope.config;
    scope.orderNumber = {};
    scope.lineNumber = {};
    scope.status = {};
    scope.quantityOrderNumber = 0;
    scope.quantityLineNumber = 0;
    scope.quantityStatus = 0;
    scope.quantiyScroll = Number(scope.config['scroll']);
    scope.hover = true;
    scope.selectedOrderNumber = [];
    scope.selectedLineNumber = [];
    scope.selectedStatus = [];
    scope.exact_search = '';
    scope.queryTechnicalSupport = {};
    scope.queryTechnicalSupportAll = [];
    scope.alldata = {};
    scope.a = {};
    scope.alldata_backup = {};
    scope.auxScroll = {};
    scope.suggestionsAutocomplete = [];
    scope.selectedIndexAutocomplete = -1;
    scope.labelOrderNumber = scope.config['labelOrderNumber'];
    scope.labelLineNumber = scope.config['labelLineNumber'];
    scope.labelStatus = scope.config['labelStatus'];
    scope.show_mesagge_data_technical = "";
    scope.text_btn_detail_normal = scope.config['text_btn_detail_normal'];
    scope.text_btn_detail_expanded = scope.config['text_btn_detail_expanded'];
    scope.data_empty = false;
    scope.data_empty_rest = false;

    // Get data.
    retrieveInformation(scope, scope.config, el);

    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };

    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        if (scope.TechnicalSupportList.error) {
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

    // Get filters.
    scope.loadFilters = function () {
      // Load category and autocomplete.
      var order_number = {};
      var line_number = {};
      var status = {};
      var duplicate_order_number = [];
      var duplicate_line_number = [];
      var duplicate_status = [];
      var order_number_order = [];
      var order_number_other = {};
      var statusOrder = [];
      var statusOther = {};
      var line_number_order = [];
      var line_number_other = {};
      // Load data autocomplete.
      var autocomplete = [];
      var options = [];
      var duplicate_autocomplete = [];
      for (var i in scope.alldata) {
        if (scope.alldata.hasOwnProperty(i)) {
          for (var j in scope.alldata[i]) {
            if (scope.alldata[i].hasOwnProperty(j)) {
              // Add order.
              if (scope.alldata[i][j]['order'] && !scope.alldata[i][j]['order_null']) {
                if (!duplicate_order_number[scope.alldata[i][j]['order']]) {
                  duplicate_order_number[scope.alldata[i][j]['order']] = 'exist';
                  order_number_order.push(scope.alldata[i][j]['order']);
                }
              }

              // Add request code.
              if (scope.alldata[i][j]['line_number_without_format'] && !scope.alldata[i][j]['line_number_null']) {
                if (!duplicate_line_number[scope.alldata[i][j]['line_number_without_format']]) {
                  duplicate_line_number[scope.alldata[i][j]['line_number_without_format']] = 'exist';
                  line_number_order.push(scope.alldata[i][j]['line_number_without_format']);
                }
              }

              // Add status.
              if (scope.alldata[i][j]['status'] && !scope.alldata[i][j]['status_null']) {
                if (!duplicate_status[scope.alldata[i][j]['status']]) {
                  duplicate_status[scope.alldata[i][j]['status']] = 'exist';
                  statusOrder.push(scope.alldata[i][j]['status']);
                }
              }

              // Add search.
              if (scope.alldata[i][j]['imei'] && !scope.alldata[i][j]['imei_null']) {
                options = [];
                if (!duplicate_autocomplete[scope.alldata[i][j]['imei']]) {
                  duplicate_autocomplete[scope.alldata[i][j]['imei']] = 'exist';
                  options['name'] = String(scope.alldata[i][j]['imei']);
                  autocomplete.push(options);
                }
              }
            }
          }
        }
      }

      // Add Order Number.
      var counter = 0;
      order_number_order.sort();
      for (var i = 0; i < order_number_order.length; i++) {
        order_number_other = {};
        order_number_other['name'] = order_number_order[i];
        order_number[counter] = order_number_other;
        counter = counter + 1;
      }
      // Set quantity Order Number.
      scope.quantityOrderNumber = counter;
      // Set user.
      scope.orderNumber = order_number;

      // Add request code.
      counter = 0;
      line_number_order.sort();
      for (var i = 0; i < line_number_order.length; i++) {
        line_number_other = {};
        line_number_other['name'] = line_number_order[i];
        line_number[counter] = line_number_other;
        counter = counter + 1;
      }
      // Set quantity line number.
      scope.quantityLineNumber = counter;
      // Set line number.
      scope.lineNumber = line_number;

      // Add status.
      counter = 0;
      statusOrder.sort();
      for (var i = 0; i < statusOrder.length; i++) {
        statusOther = {};
        statusOther['name'] = statusOrder[i];
        status[counter] = statusOther;
        counter = counter + 1;
      }
      // Set quantity status.
      scope.quantityStatus = counter;
      // Set status.
      scope.status = status;

      // Set data autocomplete.
      scope.autocomplete = autocomplete;
    }

    // Function searchAutocomplete.
    scope.searchAutocomplete = function (key_data) {
      jQuery(".collections.suggestions").css("display", "block");
      if (typeof key_data !== 'undefined' && key_data == 'exact_search') {
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

    // Validate click in search.
    scope.searchAutocompleteClick = function (value) {
      if (Object.keys(scope.queryTechnicalSupport).length > 0) {
        scope.searchAutocomplete(value);
      }
    }

    function efecto(value, key_data) {
      $str = (value.name + "");
      $search = key_data + "";
      $pos = $str.indexOf($search);

      if ($pos >= 0) {
        value.name = "<strong>" + $str.substr($pos, $search.length) + "</strong>" + (($str.length - $search.length) > 0 ? $str.substr($search.length, $str.length) : "");
      }
      return value;
    }

    // Get filter for keyCode.
    scope.checkKeyDownReference = function (event, field) {
      if (event.keyCode == 8) {
      }

      if (event.keyCode === 40) {
        event.preventDefault();
        if (scope.selectedIndexAutocomplete + 1 !== scope.suggestionsAutocomplete.length) {
          scope.selectedIndexAutocomplete++;
        }
      }
      else if (event.keyCode === 38) {
        event.preventDefault();
        if (scope.selectedIndexAutocomplete - 1 !== -1) {
          scope.selectedIndexAutocomplete--;
        }
      }
      else if (event.keyCode === 13) {
        scope.resultClickedAutocomplete(scope.selectedIndexAutocomplete, field);
      }
      else {
        scope.suggestionsAutocomplete = [];
      }
    };

    // Get data for filter search.
    scope.resultClickedAutocomplete = function (index, item) {
      if (index != -1) {
        scope[item] = '';
        scope.autocompleteChange(scope.suggestionsAutocomplete[index].name);
        scope.closeSuggestions();
      }
    };

    // Load change autocomplete.
    scope.autocompleteChange = function (option) {
      scope.data_empty = false;
      scope.alldata = scope.alldata_backup;
      var length = 0;
      var a = {};
      var aux = [];
      if (option === '') {
        if (scope.selectedOrderNumber.length == 0 && scope.selectedLineNumber == 0 && scope.selectedStatus == 0) {
          a = scope.alldata;
        }
        else {
          scope.filterBySelect();
          a = scope.a;
        }
      }
      else {
        a = {};
        var aux = [];
        if (scope.selectedOrderNumber.length == 0 && scope.selectedLineNumber == 0 && scope.selectedStatus == 0) {
          scope.filterByOnlyAutocomplete(option);
          a = scope.a;
        }
        else {
          scope.filterByOnlyAutocomplete(option);
          scope.filterBySelect('autocomplete');
          a = scope.a;
        }
      }

      scope.exact_search = option;
      jQuery('#tagsList label').addClass('active');

      if (Object.keys(a).length == 0) {
        scope.data_empty = true;
      }
      scope.auxScroll = a;

      // Load data.
      scope.technical_support = scope.scrollAux();

    };

    // Function to filter data with change user to empty.
    scope.filterByOnlyAutocomplete = function (option) {
      var length = 0;
      a = {};
      var aux = [];
      for (var i in scope.alldata) {
        if (scope.alldata.hasOwnProperty(i)) {
          for (var j in scope.alldata[i]) {
            if (scope.alldata[i].hasOwnProperty(j)) {
              if (String(scope.alldata[i][j].imei) == option) {
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
        scope.a = a;
      }
      else {
        a = {};
        scope.auxScroll = a;
      }

      scope.technical_support = scope.scrollAux(length);
    };

    // Infinite scroll.
    scope.loadMore = function () {
      if (scope.sizeAuxScroll() > 0) {
        var sizeInvoice = scope.sizeInvoice();
        scope.technical_support = scope.scrollAux(sizeInvoice);
      }
      else if (typeof scope.queryTechnicalSupport[1] != 'undefined') {
        scope.technical_support = scope.scroll(sizeInvoice);
      }
    }

    // Function to filter for change selected.
    scope.changeStatus = function () {
      scope.data_empty = false;
      var length = 0;
      var a = {};

      var aux = [];
      if (scope.selectedOrderNumber.length == 0 && scope.selectedLineNumber.length == 0 && scope.selectedStatus.length == 0) {
        if (scope.exact_search == '') {
          a = scope.alldata;
        }
        else {
          scope.alldata = scope.alldata_backup;
          scope.filterByOnlyAutocomplete(scope.exact_search);
          a = scope.a;
        }
      }
      else {
        scope.alldata = scope.alldata_backup;
        if (scope.exact_search == '') {
          scope.filterBySelect();
        }
        else {
          scope.filterByOnlyAutocomplete(scope.exact_search);
          scope.filterBySelectAndAutocomplete();
        }
        a = scope.a;
      }

      if (Object.keys(a).length == 0) {
        scope.data_empty = true;
      }

      scope.auxScroll = a;

      // Load data.
      scope.technical_support = scope.scrollAux();

    }

    // Function.
    scope.filterBySelect = function (option) {
      var length = 0;
      var length_all = 0;

      if (option == 'autocomplete') {
        scope.alldata = scope.a;
      }
      a = {};

      var aux = [];
      if (scope.selectedOrderNumber.length > 0) {
        a = {};
        var aux = [];
        for (var i in scope.alldata) {
          if (scope.alldata.hasOwnProperty(i)) {
            for (var j in scope.alldata[i]) {
              if (scope.alldata[i].hasOwnProperty(j)) {
                for (var c = 0; c < scope.selectedOrderNumber.length; c++) {
                  if (scope.alldata[i][j].order == scope.selectedOrderNumber[c]) {
                    length = length + 1;
                    length_all++;
                    aux = [];
                    if (a[i]) {
                      aux = [];
                      for (key in a) {
                        if (scope.alldata.hasOwnProperty(key)) {
                          if (key == i) {
                            for (value in a[key]) {
                              if (scope.alldata[key].hasOwnProperty(value)) {
                                aux.splice(scope.selectedOrderNumber[c], 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(scope.selectedOrderNumber[c], 0, scope.alldata[i][j]);
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
        scope.a = a;

        // Load data.
        scope.technical_support = scope.scrollAux();
        scope.alldata = a;
      }

      if (scope.selectedLineNumber.length > 0) {
        length = 0;
        a = {};
        var aux = [];
        for (var i in scope.alldata) {
          if (scope.alldata.hasOwnProperty(i)) {
            for (var j in scope.alldata[i]) {
              if (scope.alldata[i].hasOwnProperty(j)) {
                for (var c = 0; c < scope.selectedLineNumber.length; c++) {
                  if (scope.alldata[i][j].line_number_without_format == scope.selectedLineNumber[c]) {
                    length = length + 1;
                    length_all++;
                    aux = [];
                    if (a[i]) {
                      aux = [];
                      for (key in a) {
                        if (scope.alldata.hasOwnProperty(key)) {
                          if (key == i) {
                            for (value in a[key]) {
                              if (scope.alldata[key].hasOwnProperty(value)) {
                                aux.splice(scope.selectedLineNumber[c], 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(scope.selectedLineNumber[c], 0, scope.alldata[i][j]);
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
        scope.a = a;

        // Load data.
        scope.technical_support = scope.scrollAux();
        scope.alldata = a;
      }

      if (scope.selectedStatus.length > 0) {
        length = 0;
        a = {};
        var aux = [];
        for (var i in scope.alldata) {
          if (scope.alldata.hasOwnProperty(i)) {
            for (var j in scope.alldata[i]) {
              if (scope.alldata[i].hasOwnProperty(j)) {
                for (var c = 0; c < scope.selectedStatus.length; c++) {
                  if (scope.alldata[i][j].status == scope.selectedStatus[c]) {
                    length = length + 1;
                    length_all++;
                    aux = [];
                    if (a[i]) {
                      aux = [];
                      for (key in a) {
                        if (scope.alldata.hasOwnProperty(key)) {
                          if (key == i) {
                            for (value in a[key]) {
                              if (scope.alldata[key].hasOwnProperty(value)) {
                                aux.splice(scope.selectedStatus[c], 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(scope.selectedStatus[c], 0, scope.alldata[i][j]);
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
        scope.a = a;

        // Load data.
        scope.technical_support = scope.scrollAux();
        scope.alldata = a;
      }

      if (length_all > 0) {
        scope.alldata = scope.alldata_backup;
      }

      // Load data.
      scope.technical_support = scope.scrollAux();

    }

    // Function filter by select and autocomplete.
    scope.filterBySelectAndAutocomplete = function (option) {
      var length = 0;
      var length_all = 0;
      a = {};

      var aux = [];
      if (scope.selectedOrderNumber.length > 0) {
        a = {};
        var aux = [];
        for (var i in scope.technical_support) {
          if (scope.technical_support.hasOwnProperty(i)) {
            for (var j in scope.technical_support[i]) {
              if (scope.technical_support[i].hasOwnProperty(j)) {
                for (var c = 0; c < scope.selectedOrderNumber.length; c++) {
                  if (scope.technical_support[i][j].order == scope.selectedOrderNumber[c]) {
                    length = length + 1;
                    length_all++;
                    aux = [];
                    if (a[i]) {
                      aux = [];
                      for (key in a) {
                        if (scope.technical_support.hasOwnProperty(key)) {
                          if (key == i) {
                            for (value in a[key]) {
                              if (scope.technical_support[key].hasOwnProperty(value)) {
                                aux.splice(scope.selectedOrderNumber[c], 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(scope.selectedOrderNumber[c], 0, scope.technical_support[i][j]);
                      a[i] = aux;
                    }
                    else {
                      aux.splice(j, 0, scope.technical_support[i][j]);
                      a[i] = aux;
                    }
                  }
                }
              }
            }
          }
        }

        scope.auxScroll = a;
        scope.a = a;

        // Load data.
        scope.technical_support = scope.scrollAux();
      }

      if (scope.selectedLineNumber.length > 0) {
        length = 0;
        a = {};
        var aux = [];
        for (var i in scope.technical_support) {
          if (scope.technical_support.hasOwnProperty(i)) {
            for (var j in scope.technical_support[i]) {
              if (scope.technical_support[i].hasOwnProperty(j)) {
                for (var c = 0; c < scope.selectedLineNumber.length; c++) {
                  if (scope.technical_support[i][j].line_number_without_format == scope.selectedLineNumber[c]) {
                    length = length + 1;
                    length_all++;
                    aux = [];
                    if (a[i]) {
                      aux = [];
                      for (key in a) {
                        if (scope.technical_support.hasOwnProperty(key)) {
                          if (key == i) {
                            for (value in a[key]) {
                              if (scope.technical_support[key].hasOwnProperty(value)) {
                                aux.splice(scope.selectedLineNumber[c], 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(scope.selectedLineNumber[c], 0, scope.technical_support[i][j]);
                      a[i] = aux;
                    }
                    else {
                      aux.splice(j, 0, scope.technical_support[i][j]);
                      a[i] = aux;
                    }
                  }
                }
              }
            }
          }
        }

        scope.auxScroll = a;
        scope.a = a;

        // Load data.
        scope.technical_support = scope.scrollAux();
      }

      if (scope.selectedStatus.length > 0) {
        length = 0;
        a = {};
        var aux = [];
        for (var i in scope.technical_support) {
          if (scope.technical_support.hasOwnProperty(i)) {
            for (var j in scope.technical_support[i]) {
              if (scope.technical_support[i].hasOwnProperty(j)) {
                for (var c = 0; c < scope.selectedStatus.length; c++) {
                  if (scope.technical_support[i][j].status == scope.selectedStatus[c]) {
                    length = length + 1;
                    length_all++;
                    aux = [];
                    if (a[i]) {
                      aux = [];
                      for (key in a) {
                        if (scope.technical_support.hasOwnProperty(key)) {
                          if (key == i) {
                            for (value in a[key]) {
                              if (scope.technical_support[key].hasOwnProperty(value)) {
                                aux.splice(scope.selectedStatus[c], 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(scope.selectedStatus[c], 0, scope.technical_support[i][j]);
                      a[i] = aux;
                    }
                    else {
                      aux.splice(j, 0, scope.technical_support[i][j]);
                      a[i] = aux;
                    }
                  }
                }
              }
            }
          }
        }

        scope.auxScroll = a;
        scope.a = a;

        // Load data.
        scope.technical_support = scope.scrollAux();
      }

      if (length_all == 0) {
        a = {};
        scope.auxScroll = a;
      }

      // Load data.
      scope.technical_support = scope.scrollAux();

    }

    // Function filters in mobile.
    scope.filtersChangeMobile = function (type, checked, $event) {
      var value = $event.target.value;
      // Validate user.
      if (type == 'orderNumber') {
        if (checked) {
          scope.selectedOrderNumber.push(value);
        }
        else {
          index = scope.selectedOrderNumber.indexOf(value);
          if (index > -1) {
            scope.selectedOrderNumber.splice(index, 1);
          }
        }

        if (scope.selectedOrderNumber.length > 1) {
          scope.selectedOrderNumber.sort();
        }
      }

      // Validate request.
      if (type == 'lineNumber') {
        if (checked) {
          scope.selectedLineNumber.push(value);
        }
        else {
          index = scope.selectedLineNumber.indexOf(value);
          if (index > -1) {
            scope.selectedLineNumber.splice(index, 1);
          }
        }

        if (scope.selectedLineNumber.length > 1) {
          scope.selectedLineNumber.sort();
        }
      }

      // Validate status.
      if (type == 'status') {
        if (checked) {
          scope.selectedStatus.push(value);
        }
        else {
          index = scope.selectedStatus.indexOf(value);
          if (index > -1) {
            scope.selectedStatus.splice(index, 1);
          }
        }

        if (scope.selectedStatus.length > 1) {
          scope.selectedStatus.sort();
        }
      }

    };

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
          scope.show_mesagge_data_technical = resp.data.message;
          scope.queryTechnicalSupportMessage();
        }
        else if (resp.data[0] && resp.data[0] == 'empty') {
          scope.data_empty_rest = true;
        }
        else {
          // Set result.
          scope.queryTechnicalSupportAll = resp.data.fixed;

          // Order date.
          scope.orderDate();

          // Assign result.
          scope.queryTechnicalSupport['data'] = scope.queryTechnicalSupportAll;

          scope.queryTechnicalSupport = resp.data;
          scope.alldata = resp.data;
          scope.alldata_backup = resp.data;

          // Scroll.
          scope.technical_support = scope.scroll();
          scope.auxScroll = resp.data;

          // Load filters.
          scope.loadFilters();
        }
        jQuery(el).parents("section").fadeIn('slow');
      }, function () {
        console.log("Error obteniendo los datos");
      });
    }
  }

  // Controller.
  QueryTechnicalSupportController.$inject = ['$scope', '$http'];

  function QueryTechnicalSupportController($scope, $http) {
    // Init vars.
    if (typeof $scope.TechnicalSupportList == 'undefined') {
      $scope.TechnicalSupportList = "";
      $scope.TechnicalSupportList.error = false;
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
      for (var i in $scope.queryTechnicalSupport) {
        aux = [];
        group = i;
        if ($scope.queryTechnicalSupport.hasOwnProperty(i)) {
          if (quantity > 0) {
            if ($scope.queryTechnicalSupport[i].length > 0) {
              var slice = quantity;
              if (quantity > $scope.queryTechnicalSupport[i].length) {
                slice = $scope.queryTechnicalSupport[i].length;
              }
              aux.splice(i, 0, $scope.queryTechnicalSupport[i].slice(0, slice));
              scroll[i] = aux[0];
              quantity = quantity - $scope.queryTechnicalSupport[i].length;
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

    // Calculate size technical_support.
    $scope.sizeInvoice = function () {
      var size = 0;
      for (var i in $scope.technical_support) {
        if ($scope.technical_support.hasOwnProperty(i)) {
          size = size + $scope.technical_support[i].length;
        }
      }

      return size;
    }

    // Show message service.
    $scope.queryTechnicalSupportMessage = function () {
      jQuery(".block-query-technical-support .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_technical + '</p></div>');
      $html_mensaje = jQuery('.block-query-technical-support .messages-only').html();
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

    // Show detail.
    $scope.showDetail = function ($event, order, line_number) {
      var getClass = $event.target.className;
      var getParentClass = jQuery($event.target).closest('.query-search-detail');
      var getId = $event.target.id;
      var validateClass = getClass.search("collapse");
      if (validateClass == -1) {
        // Add and remove class to a.
        jQuery('#' + getId).removeClass("expanded");
        jQuery('#' + getId).addClass("collapse");
        jQuery('#' + getId).text($scope.text_btn_detail_normal);
        // Add and remove class to div.
        jQuery('.' + getId).removeClass("expanded");
        jQuery('.' + getId).addClass("collapse");

        getParentClass.removeClass("expanded");
        getParentClass.addClass("collapse");

      }
      else {
        // Add and remove class to a.
        jQuery('#' + getId).removeClass("collapse");
        jQuery('#' + getId).addClass("expanded");

        // Add and remove class to div.
        jQuery('.' + getId).removeClass("collapse");
        jQuery('.' + getId).addClass("expanded");

        getParentClass.removeClass("collapse");
        getParentClass.addClass("expanded");

        jQuery('#' + getId).text($scope.text_btn_detail_expanded);

        // Save audit log.
        $scope.saveAuditLog($scope[$event.target.name], order, line_number);
      }
    }

    // Function to save audit log.
    $scope.saveAuditLog = function (config, order, line_number) {
      var params = {
        'order': order,
        'line_number': line_number
      };

      jQuery('.preloadingContainer').remove();
      if ($scope.resources.indexOf(config.url) == -1) {
        $http.get('/rest/session/token').then(function (resp) {
          $http({
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-Token': resp.data
            },
            data: params,
            url: config.url
          }).then(function successCallback(response) {
          }, function errorCallback(response) {
          });
        });
      }
    }

    // Manage filters.
    $scope.openCloseFilters = function () {
      if (document.getElementById('filters-mobile-container').className == 'filters-mobile-container closed') {
        document.getElementById('filters-mobile-container').className = 'filters-mobile-container';
        document.getElementById('closed-btn-1').className = 'prefix icon-filters-cyan';
      }
      else {
        document.getElementById('filters-mobile-container').className = 'filters-mobile-container closed';
        document.getElementById('closed-btn-1').className = 'prefix icon-filters-cyan closed';
      }
    }

    // Implements filterFunctionMobile().
    $scope.filterFunctionMobile = function () {
      $scope.changeStatus();
      document.getElementById('filters-mobile-container').className = 'filters-mobile-container closed';
      document.getElementById('closed-btn-1').className = 'prefix icon-filters-cyan closed';
    }

    // Implements closeFunction().
    $scope.closeFunction = function () {
      document.getElementById('filters-mobile-container').className = 'filters-mobile-container closed';
      document.getElementById('closed-btn-1').className = 'prefix icon-filters-cyan closed';
    }

    // Implements closeFunction().
    $scope.closeFunctionAll = function () {
      $scope.selectedOrderNumber = [];
      $scope.selectedLineNumber = [];
      $scope.selectedStatus = [];
      $scope.exact_search = '';
      $scope.checked = false;
      $scope.initFilters();
      $scope.changeStatus();
      document.getElementById('filters-mobile-container').className = 'filters-mobile-container closed';
      document.getElementById('closed-btn-1').className = 'prefix icon-filters-cyan closed';
    }

    $scope.initFilters = function () {
      jQuery('.filter-check:checked').click();
    }

    // Implements showHideFilter().
    var aux = 'filterM-';
    $scope.showHideFilter = function (identifier) {
      aux_filter = aux.concat(identifier);
      document.getElementById(aux_filter).style.display = 'block';
      document.getElementById('mobile-menu-filters').style.display = 'none';
      document.getElementById('form-filtros-interno').style.display = 'block';
    }

    // Implements hideFilter().
    $scope.hideFilter = function (identifier) {
      aux_filter = aux.concat(identifier);
      document.getElementById(aux_filter).style.display = 'none';
      document.getElementById('mobile-menu-filters').style.display = 'block';
    }

    $scope.orderDate = function () {
      $scope.queryTechnicalSupportAll.sort(function (a, b) {
        if (a['timestamp'] == b['timestamp']) {
          return 0;
        }

        if (a['timestamp'] !== null && a['timestamp'] !== undefined) {
          if (b['timestamp'] !== null && b['timestamp'] !== undefined) {
            var _a = a['timestamp'];
            var _b = b['timestamp'];

            return (_a > _b) ? -1 : 1;

          }
        }
      });
    }

    // Download contract invoice detail.
    $scope.downloadTechnicalSupport = function ($event, type) {

      var config = $scope[$event.target.name];
      var params = {
        download: true,
        type: type
      };

      $http.get('/rest/session/token').then(function (resp) {
        $http({
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': resp.data
          },
          url: config.url,
          data: params
        }).then(function (response) {
          if (!response.data.file_name) {
            $scope.show_mesagge_data_technical = Drupal.t("Problema en la descarga de las ordenes de soporte.");
            $scope.queryTechnicalSupportMessage();
          }
          else {
            window.open('/adf_core/download-example/' + response.data.file_name + '/NULL', '_blank');
          }
        });

      });
    };
  }
}
