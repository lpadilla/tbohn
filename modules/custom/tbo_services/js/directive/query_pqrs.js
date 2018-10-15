/**
 * @file
 * Implements query pqrs directive.
 */

myApp.directive('ngQueryPqrs', ['$http', 'apiBatch', 'dataCollector', ngQueryPqrs]);

function ngQueryPqrs($http, apiBatch, dataCollector) {

  var directive = {
    restrict: 'EA',
    controller: CurrentInvoiceController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    // Declare variables.
    scope.config = drupalSettings.queryPqrsBlock[scope.uuid_data_ng_query_pqrs];
    scope[scope.config['uuid']] = scope.config;
    scope.type_result = scope.config['type'];
    scope.environment_enterprise = scope.config['environment_enterprise'];
    scope.user = {};
    scope.requestCode = {};
    scope.status = {};
    scope.quantityRequestCode = 0;
    scope.counter_movil = 0;
    scope.quantityStatus = 0;
    scope.quantiyScroll = Number(scope.config['scroll']);
    scope.hover = true;
    scope.selectedUser = [];
    scope.selectedRequestCode = [];
    scope.selectedStatus = [];
    scope.exact_search = '';
    scope.queryPqrs = {};
    scope.queryPqrsAll = [];
    scope.alldata = {};
    scope.a = {};
    scope.alldata_backup = {};
    scope.auxScroll = {};
    scope.both = false;
    scope.resultFixed = false;
    scope.movilFixed = false;
    scope.loadingInit = true;
    scope.suggestionsAutocomplete = [];
    scope.selectedIndexAutocomplete = -1;
    scope.labelUser = scope.config['labelUser'];
    scope.labelRequestCode = scope.config['labelRequestCode'];
    scope.labelStatus = scope.config['labelStatus'];
    scope.show_mesagge = false;
    scope.show_mesagge_fixed = false;
    scope.show_mesagge_movil = false;
    scope.show_mesagge_data = "";
    scope.show_mesagge_data_batch = "";
    scope.text_btn_detail_normal = scope.config['text_btn_detail_normal'];
    scope.text_btn_detail_expanded = scope.config['text_btn_detail_expanded'];
    scope.checked_request = false;
    scope.checked_status = false;
    scope.data_empty = false;
    scope.data_empty_rest = false;
    scope.data_empty_rest_both = false;
    scope.data_empty_rest_validate_fixed = false;
    scope.data_empty_rest_validate_movil = false;

    // Validate environment.
    if (scope.config['environment_enterprise'] == 'both') {
      scope.both = true;
      scope.apiBatchBoth(el);
    }
    else if (scope.config['environment_enterprise'] == 'movil') {
      var search_key = {};
      search_key = {};

      var api = new apiBatch("get_pqrs_movil_data", search_key);

      api.init();

      scope.dataCollector = function () {
        return dataCollector.getData();
      }

      scope.$watch(scope.dataCollector, function (v) {
        if (Object.keys(v).length > 0) {
          if (Object.keys(v)[0] == 0) {
            if (v[0] == 'empty') {
              scope.data_empty_rest = true;
            }
            else {
              scope.show_mesagge_movil = true;
              scope.show_mesagge_data = v[0];
              scope.queryPqrsMessage();
            }
          }
          else {
            // Set result.
            scope.queryPqrsAll = v.movil;

            // Order date.
            scope.orderDate();

            // Assign result.
            scope.queryPqrs['data'] = scope.queryPqrsAll;

            scope.invoicesByContract = v;
            scope.alldata = v;
            scope.alldata_backup = v;

            // Scroll.
            scope.invoices = scope.scroll();
            scope.auxScroll = v;

            // Load filters.
            scope.loadFilters();
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
        if (scope.pqrsList.error) {
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

    // Get filters.
    scope.loadFilters = function () {
      // Load category and autocomplete.
      var user = {};
      var requestCode = {};
      var status = {};
      var duplicate = [];
      var duplicate_user = [];
      var duplicate_request_code = [];
      var duplicate_status = [];
      var userOrder = [];
      var userOther = {};
      var statusOrder = [];
      var statusOther = {};
      var requestCodeOrder = [];
      var requestCodeOther = {};
      // Load data autocomplete.
      var autocomplete = [];
      var options = [];
      var duplicate_autocomplete = [];
      for (var i in scope.alldata) {
        if (scope.alldata.hasOwnProperty(i)) {
          for (var j in scope.alldata[i]) {
            if (scope.alldata[i].hasOwnProperty(j)) {
              // Add user.
              if (scope.alldata[i][j]['user'] && !scope.alldata[i][j]['user_null']) {
                if (!duplicate_user[scope.alldata[i][j]['user']]) {
                  duplicate_user[scope.alldata[i][j]['user']] = 'exist';
                  userOrder.push(scope.alldata[i][j]['user']);
                }
              }

              // Add request code.
              if (scope.alldata[i][j]['request_code'] && !scope.alldata[i][j]['request_code_null']) {
                if (!duplicate_request_code[scope.alldata[i][j]['request_code']]) {
                  duplicate_request_code[scope.alldata[i][j]['request_code']] = 'exist';
                  requestCodeOrder.push(scope.alldata[i][j]['request_code']);
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
              if (scope.alldata[i][j]['filing_number'] && !scope.alldata[i][j]['filing_number_null']) {
                options = [];
                if (!duplicate_autocomplete[scope.alldata[i][j]['filing_number']]) {
                  duplicate_autocomplete[scope.alldata[i][j]['filing_number']] = 'exist';
                  options['name'] = scope.alldata[i][j]['filing_number'];
                  autocomplete.push(options);
                }
              }
            }
          }
        }
      }

      // Add user.
      var counter = 0;
      userOrder.sort();
      for (var i = 0; i < userOrder.length; i++) {
        userOther = {};
        userOther['name'] = userOrder[i];
        user[counter] = userOther;
        counter = counter + 1;
      }
      // Set quantity user.
      scope.quantiyUser = counter;
      // Set user.
      scope.user = user;

      // Add request code.
      counter = 0;
      requestCodeOrder.sort();
      for (var i = 0; i < requestCodeOrder.length; i++) {
        requestCodeOther = {};
        requestCodeOther['name'] = requestCodeOrder[i];
        requestCode[counter] = requestCodeOther;
        counter = counter + 1;
      }
      // Set quantity request code.
      scope.quantityRequestCode = counter;
      // Set request code.
      scope.requestCode = requestCode;

      // Add status.
      counter = 0;
      statusOrder.sort();
      for (var i = 0; i < statusOrder.length; i++) {
        statusOther = {};
        statusOther['name'] = statusOrder[i];
        status[counter] = statusOther;
        counter = counter + 1;
      }
      // Set quantity user.
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
      if (Object.keys(scope.queryPqrs).length > 0) {
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
        if (scope.selectedUser.length == 0 && scope.selectedRequestCode == 0 && scope.selectedStatus == 0) {
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
        if (scope.selectedUser.length == 0 && scope.selectedRequestCode == 0 && scope.selectedStatus == 0) {
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
      scope.pqrs = scope.scrollAux();

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
              if (scope.alldata[i][j].filing_number == option) {
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
        a['No existen datos que coincidan con los criterios de busqueda'] = {};
        scope.auxScroll = a;
      }

      scope.pqrs = scope.scrollAux(length);
    };

    // Infinite scroll.
    scope.loadMore = function () {
      if (scope.sizeAuxScroll() > 0) {
        var sizeInvoice = scope.sizeInvoice();
        scope.pqrs = scope.scrollAux(sizeInvoice);
      }
      else if (typeof scope.queryPqrs[1] != 'undefined') {
        scope.pqrs = scope.scroll(sizeInvoice);
      }
    }

    // Function to filter for user.
    scope.changeUser = function () {
      scope.data_empty = false;
      var length = 0;
      var a = {};

      var aux = [];
      if (scope.selectedUser.length == 0 && scope.selectedRequestCode.length == 0 && scope.selectedStatus.length == 0) {
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
      scope.pqrs = scope.scrollAux();

    }

    // Function to filter for request code.
    scope.changeRequestCode = function () {
      scope.data_empty = false;
      var length = 0;
      var a = {};

      var aux = [];
      if (scope.selectedUser.length == 0 && scope.selectedRequestCode.length == 0 && scope.selectedStatus.length == 0) {
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
      scope.pqrs = scope.scrollAux();

    }

    // Function to filter for request code.
    scope.changeStatus = function () {
      scope.data_empty = false;
      var length = 0;
      var a = {};

      var aux = [];
      if (scope.selectedUser.length == 0 && scope.selectedRequestCode.length == 0 && scope.selectedStatus.length == 0) {
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
      scope.pqrs = scope.scrollAux();

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
      if (scope.selectedUser.length > 0) {
        a = {};
        var aux = [];
        for (var i in scope.alldata) {
          if (scope.alldata.hasOwnProperty(i)) {
            for (var j in scope.alldata[i]) {
              if (scope.alldata[i].hasOwnProperty(j)) {
                for (var c = 0; c < scope.selectedUser.length; c++) {
                  if (scope.alldata[i][j].user == scope.selectedUser[c]) {
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
                                aux.splice(scope.selectedUser[c], 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(scope.selectedUser[c], 0, scope.alldata[i][j]);
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
        scope.pqrs = scope.scrollAux();
        scope.alldata = a;
      }

      if (scope.selectedRequestCode.length > 0) {
        length = 0;
        a = {};
        var aux = [];
        for (var i in scope.alldata) {
          if (scope.alldata.hasOwnProperty(i)) {
            for (var j in scope.alldata[i]) {
              if (scope.alldata[i].hasOwnProperty(j)) {
                for (var c = 0; c < scope.selectedRequestCode.length; c++) {
                  if (scope.alldata[i][j].request_code == scope.selectedRequestCode[c]) {
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
                                aux.splice(scope.selectedRequestCode[c], 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(scope.selectedRequestCode[c], 0, scope.alldata[i][j]);
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
        scope.pqrs = scope.scrollAux();
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
        scope.pqrs = scope.scrollAux();
        scope.alldata = a;
      }

      if (length_all > 0) {
        scope.alldata = scope.alldata_backup;
      }

      // Load data.
      scope.pqrs = scope.scrollAux();

    }

    // Function filter by select and autocomplete.
    scope.filterBySelectAndAutocomplete = function (option) {
      var length = 0;
      var length_all = 0;
      a = {};

      var aux = [];
      if (scope.selectedUser.length > 0) {
        a = {};
        var aux = [];
        for (var i in scope.pqrs) {
          if (scope.pqrs.hasOwnProperty(i)) {
            for (var j in scope.pqrs[i]) {
              if (scope.pqrs[i].hasOwnProperty(j)) {
                for (var c = 0; c < scope.selectedUser.length; c++) {
                  if (scope.pqrs[i][j].user == scope.selectedUser[c]) {
                    length = length + 1;
                    length_all++;
                    aux = [];
                    if (a[i]) {
                      aux = [];
                      for (key in a) {
                        if (scope.pqrs.hasOwnProperty(key)) {
                          if (key == i) {
                            for (value in a[key]) {
                              if (scope.pqrs[key].hasOwnProperty(value)) {
                                aux.splice(scope.selectedUser[c], 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(scope.selectedUser[c], 0, scope.pqrs[i][j]);
                      a[i] = aux;
                    }
                    else {
                      aux.splice(j, 0, scope.pqrs[i][j]);
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
        scope.pqrs = scope.scrollAux();
      }

      if (scope.selectedRequestCode.length > 0) {
        length = 0;
        a = {};
        var aux = [];
        for (var i in scope.pqrs) {
          if (scope.pqrs.hasOwnProperty(i)) {
            for (var j in scope.pqrs[i]) {
              if (scope.pqrs[i].hasOwnProperty(j)) {
                for (var c = 0; c < scope.selectedRequestCode.length; c++) {
                  if (scope.pqrs[i][j].request_code == scope.selectedRequestCode[c]) {
                    length = length + 1;
                    length_all++;
                    aux = [];
                    if (a[i]) {
                      aux = [];
                      for (key in a) {
                        if (scope.pqrs.hasOwnProperty(key)) {
                          if (key == i) {
                            for (value in a[key]) {
                              if (scope.pqrs[key].hasOwnProperty(value)) {
                                aux.splice(scope.selectedRequestCode[c], 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(scope.selectedRequestCode[c], 0, scope.pqrs[i][j]);
                      a[i] = aux;
                    }
                    else {
                      aux.splice(j, 0, scope.pqrs[i][j]);
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
        scope.pqrs = scope.scrollAux();
      }

      if (scope.selectedStatus.length > 0) {
        length = 0;
        a = {};
        var aux = [];
        for (var i in scope.pqrs) {
          if (scope.pqrs.hasOwnProperty(i)) {
            for (var j in scope.pqrs[i]) {
              if (scope.pqrs[i].hasOwnProperty(j)) {
                for (var c = 0; c < scope.selectedStatus.length; c++) {
                  if (scope.pqrs[i][j].status == scope.selectedStatus[c]) {
                    length = length + 1;
                    length_all++;
                    aux = [];
                    if (a[i]) {
                      aux = [];
                      for (key in a) {
                        if (scope.pqrs.hasOwnProperty(key)) {
                          if (key == i) {
                            for (value in a[key]) {
                              if (scope.pqrs[key].hasOwnProperty(value)) {
                                aux.splice(scope.selectedStatus[c], 0, a[key][value]);
                              }
                            }
                          }
                        }
                      }
                      aux.splice(scope.selectedStatus[c], 0, scope.pqrs[i][j]);
                      a[i] = aux;
                    }
                    else {
                      aux.splice(j, 0, scope.pqrs[i][j]);
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
        scope.pqrs = scope.scrollAux();
      }

      if (length_all == 0) {
        a['No existen datos que coincidan con los criterios de busqueda'] = {};
        scope.auxScroll = a;
      }

      // Load data.
      scope.pqrs = scope.scrollAux();

    }

    // Function filters in mobile.
    scope.filtersChangeMobile = function (type, checked, $event) {
      var value = $event.target.value;
      // Validate user.
      if (type == 'user') {
        if (checked) {
          scope.selectedUser.push(value);
        }
        else {
          index = scope.selectedUser.indexOf(value);
          if (index > -1) {
            scope.selectedUser.splice(index, 1);
          }
        }

        if (scope.selectedUser.length > 1) {
          scope.selectedUser.sort();
        }
      }

      // Validate request.
      if (type == 'requestCode') {
        if (checked) {
          scope.selectedRequestCode.push(value);
        }
        else {
          index = scope.selectedRequestCode.indexOf(value);
          if (index > -1) {
            scope.selectedRequestCode.splice(index, 1);
          }
        }

        if (scope.selectedRequestCode.length > 1) {
          scope.selectedRequestCode.sort();
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
          scope.show_mesagge_data = resp.data.message;
          scope.queryPqrsMessage();
        }
        else if (resp.data[0] && resp.data[0] == 'empty') {
          scope.data_empty_rest = true;
        }
        else {
          // Set result.
          scope.queryPqrsAll = resp.data.fixed;

          // Order date.
          scope.orderDate();

          // Assign result.
          scope.queryPqrs['data'] = scope.queryPqrsAll;

          scope.queryPqrs = resp.data;
          scope.alldata = resp.data;
          scope.alldata_backup = resp.data;

          // Scroll.
          scope.pqrs = scope.scroll();
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
        if (resp.data[0] && resp.data[0] == 'empty') {
          scope.data_empty_rest_validate_fixed = true;
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

  // Controller.
  CurrentInvoiceController.$inject = ['$scope', '$http'];

  function CurrentInvoiceController($scope, $http) {
    // Init vars.
    if (typeof $scope.pqrsList == 'undefined') {
      $scope.pqrsList = "";
      $scope.pqrsList.error = false;
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
      for (var i in $scope.queryPqrs) {
        aux = [];
        group = i;
        if ($scope.queryPqrs.hasOwnProperty(i)) {
          if (quantity > 0) {
            if ($scope.queryPqrs[i].length > 0) {
              var slice = quantity;
              if (quantity > $scope.queryPqrs[i].length) {
                slice = $scope.queryPqrs[i].length;
              }
              aux.splice(i, 0, $scope.queryPqrs[i].slice(0, slice));
              scroll[i] = aux[0];
              quantity = quantity - $scope.queryPqrs[i].length;
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

    // Calculate size pqrs.
    $scope.sizeInvoice = function () {
      var size = 0;
      for (var i in $scope.pqrs) {
        if ($scope.pqrs.hasOwnProperty(i)) {
          size = size + $scope.pqrs[i].length;
        }
      }

      return size;
    }

    // Show message service.
    $scope.queryPqrsMessage = function () {
      jQuery(".block-query-pqrs .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
      $html_mensaje = jQuery('.block-query-pqrs .messages-only').html();
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
            if ($scope.data_empty_rest_validate_movil && $scope.data_empty_rest_validate_fixed) {
              $scope.data_empty_rest_both = true;
            }

            $scope.loadingInit = false;

            if ($scope.movilFixed.movil != undefined) {
              for (var i in $scope.movilFixed.movil) {
                $scope.queryPqrsAll.push($scope.movilFixed.movil[i]);
              }
            }

            if ($scope.resultFixed.error) {
              $scope.resultFixed = false;
            }

            if ($scope.resultFixed.fixed != undefined) {
              for (var j in $scope.resultFixed.fixed) {
                $scope.queryPqrsAll.push($scope.resultFixed.fixed[j]);
              }
            }

            // Order date.
            $scope.orderDate();

            // Assign result.
            $scope.queryPqrs['data'] = $scope.queryPqrsAll;

            if (Object.keys($scope.queryPqrs).length > 0) {
              // Order date.
              $scope.alldata = $scope.queryPqrs;
              $scope.alldata_backup = $scope.queryPqrs;
              $scope.auxScroll = $scope.queryPqrs;

              // Scroll.
              $scope.pqrs = $scope.scroll();

              // Load filters.
              $scope.loadFilters();
            }
          }
        }
      });

      var search_key = {};
      search_key = {};

      var api = new apiBatch("get_pqrs_movil_data", search_key);

      api.init();

      $scope.dataCollector = function () {
        return dataCollector.getData();
      }

      $scope.$watch($scope.dataCollector, function (v) {
        if (Object.keys(v).length > 0 || $scope.counter > 1) {
          if (v != "") {
            if (v[0]) {
              if (v[0] == 'empty') {
                $scope.data_empty_rest_validate_movil = true;
              }
              else {
                $scope.show_mesagge_movil = true;
                $scope.show_mesagge_data = v[0];
                $scope.queryPqrsMessage();
              }
            }
            else {
              $scope.movilFixed = v;
            }
          }

          retrieveInformationBoth($scope, $scope.config, el);
        }
      });
    }

    // Show detail.
    $scope.showDetail = function ($event, requestCode, environment) {
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
        $scope.saveAuditLog($scope[$event.target.name], requestCode, environment);
      }
    }

    // Function to save audit log.
    $scope.saveAuditLog = function (config, requestCode, environment) {
      var params = {
        'requestCode': requestCode,
        'environment': environment
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
      $scope.changeUser();
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
      $scope.selectedUser = [];
      $scope.selectedRequestCode = [];
      $scope.selectedStatus = [];
      $scope.exact_search = '';
      $scope.checked = false;
      $scope.initFilters();
      $scope.changeUser();
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
      $scope.queryPqrsAll.sort(function (a, b) {
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
  }
}
