myApp.directive('ngServicePortfolio', ['$http', 'apiBatch', 'dataCollector', ngCurrentInvoice]);

function ngCurrentInvoice($http, apiBatch, dataCollector) {

	var directive = {
		restrict: 'EA',
		controller: CurrentInvoiceController,
		link: linkFunc
	};

	return directive;

	function linkFunc(scope, el, attr, ctrl) {
		//Declare variables
		scope.config = drupalSettings.b2bBlock[scope.uuid];
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
		scope.selectedIndexAutocomplete = -1; //currently selected suggestion index
        scope.selectedIndexAutocompletelinea = -1; //currently selected suggestion index
        scope.selectedIndexAutocompleteaddress = -1; //currently selected suggestion index
        scope.selectedIndexAutocompletecontract = -1; //currently selected suggestion index
		scope.labelCategory = scope.config['labelCategory'];
		scope.loadFilter = false;
		scope.notfilter = false;
		scope.show_mesagge = false;
		scope.show_mesagge_fixed = false;
		scope.show_mesagge_movil = false;
		scope.show_mesagge_data = "";
		scope.show_mesagge_data_batch = "";
		//Get paramter for filter
		scope.parameterFilter = getParameterByName('category');

		//Validate environment
		if (scope.config['environment_enterprise'] == 'both')
		{
			retrieveInformation(scope, scope.config, el);
		}
		else if (scope.config['environment_enterprise'] == 'movil')
		{
			retrieveInformation(scope, scope.config, el);	
            		
		}
		else if (scope.config['environment_enterprise'] == 'fijo')
		{
			retrieveInformation(scope, scope.config, el);
		}

		scope.apiIsLoading = function ()
		{
			return $http.pendingRequests.length > 0;
		};

		scope.$watch(scope.apiIsLoading, function (v)
		{
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
		 
		
		//
		var hasRegistered = false;
			scope.$watch(function() {
				if (hasRegistered) return;
					hasRegistered = true;
					scope.$$postDigest(function() {
						hasRegistered = false;
						jQuery('select').material_select();
					});
			}); 
		//Closet suggestion
		scope.closeSuggestions = function ()
		{
            jQuery("#suggestionsAutocompletelinea.collections.suggestions").css("display", "none");
            jQuery("#suggestionsAutocompleteaddress.collections.suggestions").css("display", "none");
            jQuery("#suggestionsAutocompletecontract.collections.suggestions").css("display", "none");
		}

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

		//Get categories and another data
		scope.categoryAutocomplete = function ()
		{
			
			//Load category and autocomplete
			var category = {};
			var duplicate = [];
			var categoryOrder = [];
			var categoryOther = {};
			//Load data autocomplete
            var autocompletelinea = [];
            var autocompleteaddress = [];
            var autocompletecontract = [];
			var options = [];
            var duplicate_autocompletelinea = [];
            var duplicate_autocompleteaddress = [];
            var duplicate_autocompletecontract = [];
			var counter = 0;
			for (var i in scope.alldata)
			{
				if (scope.alldata.hasOwnProperty(i))
				{
					for (var j in scope.alldata[i])
					{
						if (scope.alldata[i].hasOwnProperty(j))
						{
							
                            if (scope.alldata[i][j]['msisdn'])
                            {
                                options = [];
                                if (!duplicate_autocompletelinea[scope.alldata[i][j]['msisdn']])
                                {
                                    duplicate_autocompletelinea[scope.alldata[i][j]['msisdn']] = 'exist';
                                    options['name'] = scope.alldata[i][j]['msisdn'];
									autocompletelinea.push(options);
									
                                }
                            }

							if (scope.alldata[i][j]['address'])
							{
								options = [];
								if (!duplicate_autocompleteaddress[scope.alldata[i][j]['address']])
								{
								  duplicate_autocompleteaddress[scope.alldata[i][j]['address']] = 'exist';
								  options['name'] = scope.alldata[i][j]['address'];
								  autocompleteaddress.push(options);
								 
								}
							}
							if (scope.alldata[i][j]['contract'])
							{
								options = [];
								if (!duplicate_autocompletecontract[scope.alldata[i][j]['contract']])
								{
									duplicate_autocompletecontract[scope.alldata[i][j]['contract']] = 'exist';
									options['name'] = scope.alldata[i][j]['contract'];
									autocompletecontract.push(options);
								

								}
							}
						}
					}
				}
			}
			scope.addressF=autocompletelinea;
			scope.contracts=autocompletecontract;

			
			//Order Category
			categoryOrder.sort();
			for (var i = 0; i < categoryOrder.length; i++)
			{
				categoryOther = {};
				categoryOther['name'] = categoryOrder[i];
				category[counter] = categoryOther;
				counter = counter + 1;
			}
			//Set quantity category
			scope.quantiyCategory = counter;
			//Set category
			scope.category = category;

			//Validate paramter filter in url
			if (scope.parameterFilter)
			{
				for (var i in scope.category)
				{
					if (scope.category[i]['name'] == scope.categoryNameId[scope.parameterFilter])
					{
						scope.labelCategory = scope.categoryNameId[scope.parameterFilter];
						scope.loadFilter = true;
						scope.changeCategory();
					}
				}
			}

			//Set data autocomplete
            scope.autocompletelinea = autocompletelinea;
            scope.autocompleteaddress = autocompleteaddress;
            scope.autocompletecontract = autocompletecontract;
		}

		//Function searchAutocomplete
		scope.searchAutocomplete = function (key_data)
		{
			if (typeof key_data !== 'undefined' && key_data == 'linea')
			{
                jQuery("#suggestionsAutocompletelinea.collections.suggestions").css("display", "block");
                if ((scope[key_data] === '') || ( scope[key_data] === undefined ))
				{
					scope.autocompleteChange('');
                    scope.suggestionsAutocompletelinea = scope.autocompletelinea;
                    scope.selectedIndexAutocompletelinea = -1;
				}
				else
				{
					var aux = [];
                    scope.autocompletelinea.forEach(function (value, key, array)
                    {
                        if (value.name.search(scope[key_data]) > -1)
                        {
                            aux.push(value);
                        }
                    });
					scope.suggestionsAutocompletelinea = aux;
					scope.selectedIndexAutocompletelinea = -1;
				}
			}
            if (typeof key_data !== 'undefined' && key_data == 'address')
            {
                jQuery("#suggestionsAutocompleteaddress.collections.suggestions").css("display", "block");
                if ((scope[key_data] === '') || ( scope[key_data] === undefined ))
            	{
                    scope.autocompleteChange('');
                    scope.suggestionsAutocompleteaddress = scope.autocompleteaddress;
                    scope.selectedIndexAutocompleteaddress = -1;
                }
                else
                {
                    var aux = [];
                    scope.autocompleteaddress.forEach(function (value, key, array)
					{
                        if (value.name.search(scope[key_data]) > -1)
                        {
                        	aux.push(value);
                        }
                    });
                    scope.suggestionsAutocompleteaddress = aux;
                    scope.selectedIndexAutocompleteaddress = -1;
                }
            }
            if (typeof key_data !== 'undefined' && key_data == 'contract')
            {
                jQuery("#suggestionsAutocompletecontract.collections.suggestions").css("display", "block");
                if ((scope[key_data] === '') || ( scope[key_data] === undefined ))
                {///angular.isUndefined(value)
                    scope.autocompleteChange('');
                    scope.suggestionsAutocompletecontract = scope.autocompletecontract;
                    scope.selectedIndexAutocompletecontract = -1;
                }
                else
				{
                    var aux = [];
                    scope.autocompletecontract.forEach(function (value, key, array)
                    {
                        if (scope.autocompletecontract.indexOf(scope[key_data]) >= -1)
                        {
                            aux.push(value);
                        }
                    });
                    scope.suggestionsAutocompletecontract = aux;
                    scope.selectedIndexAutocompletecontract = -1;
                }
            }
		};

		//Validate click in search
		scope.searchAutocompleteClick = function (value)
		{

		}

		//Get filter for keyCode
		scope.checkKeyDownReference = function (event, field)
		{
			if (event.keyCode == 8)
			{
				scope.notfilter = false;
			}

			if (event.keyCode === 40)
			{//down key, increment selectedIndex
			
				event.preventDefault();
                if (field == 'contract')
                {
                    if (scope.selectedIndexAutocompletecontract + 1 !== scope.suggestionsAutocompletecontract.length)
                    {
                        scope.selectedIndexAutocompletecontract++;
                    }
				}
                if (field == 'address')
                {
                    if (scope.selectedIndexAutocompleteaddress + 1 !== scope.suggestionsAutocompleteaddress.length)
                    {
                        scope.selectedIndexAutocompleteaddress++;
                    }
                }
                if (field == 'linea')
                {
			        if (scope.selectedIndexAutocompletelinea + 1 !== scope.suggestionsAutocompletelinea.length)
                    {
                        scope.selectedIndexAutocompletelinea++;
                    }
                }
			}
			else if (event.keyCode === 38)
			{ //up key, decrement selectedIndex
				event.preventDefault();
                if (field == 'linea')
                {
                    if (scope.selectedIndexAutocompletelinea - 1 !== -1)
                    {
                        scope.selectedIndexAutocompletelinea--;
                    }
                }
                if (field == 'contract')
                {
                    if (scope.selectedIndexAutocompletecontract - 1 !== -1)
                    {
                        scope.selectedIndexAutocompletecontract--;
                    }
                }
                if (field == 'address')
                {
                    if (scope.selectedIndexAutocompleteaddress - 1 !== -1)
                    {
                        scope.selectedIndexAutocompleteaddress--;
                    }
                }
			}
			
			else
			{
                if (field == 'contract')
                {
                    scope.suggestionsAutocompletecontract = [];
                }
                if (field == 'address')
                {
                    scope.suggestionsAutocompleteaddress = [];
                }
			}
		};

		//Get filter for keyCode
	    scope.checkKeyDownReference2 = function ()
		{	  
			 linea = scope['linea'];
			 contrato= scope['contract'];			

			 if ((contrato === undefined) || (contrato == null) || (contrato == "undefined"))
			 {
				contrato = '';
			 }
			 if ((linea === undefined) || (linea == null) || (linea == "undefined"))
			 {				
				linea = '';
			 }		
			
			  scope.autocompleteChange(linea, contrato);
			
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

		scope.cleanValues = function ()	{	//oegi function that unselect check	
			
		
			if (jQuery('.export label').hasClass('active')) {            
			  jQuery('.export label').removeClass('active');      
			 
			}     
			if (jQuery('.field-user_role label').hasClass('active')) {            
			  jQuery('.field-user_role label').removeClass('active');       			 
			}  
		
			if(scope.hasOwnProperty('linea')) {
			  scope['linea'] = '';
			}
		
			if(scope.hasOwnProperty('contract')) {
			  scope['contract'] = '';
			}
			
			retrieveInformation(scope, scope.config, el);	
			
			//scope.auxScroll = scope.alldata;
			jQuery('.dropdown-content.multiple-select-dropdown input[type="checkbox"]:checked').not(':disabled').trigger('click');		
		};

		//get data for filter search
		scope.resultClickedAutocomplete = function (index, item)
		{			
			if (index != -1)
			{
				field = item.slice(0, 9).trim();
				display = item.slice(10, 16).trim();			
				if (item == 'linea')
				{			 
				      scope[field] = scope.suggestionsAutocompletelinea[index].name;				  
				}
                if (item == 'address')
				{				
				   scope[field] = scope.suggestionsAutocompleteaddress[index].name;
				}
				if (item == 'contract')
				{				
				   scope[field] =scope.suggestionsAutocompletecontract[index].name;
				}				
				scope.closeSuggestions();
			}
		};



		scope.autocompleteChange = function (linea,contrato){			
			var length = 0;
			var lengthline=0;
			var lengthcontract=0;
			var val = false;
			a = {};
			var aux = [];
			var notFilter = false;
			if (linea === '' && contrato === '')
			{				
				if (scope.selectedCategory.length == 0)
				{
					a = scope.alldata;
					notFilter = true;
				} else {
					notFilter = true;
					scope.changeCategory();
				}
			}
			else
			{				
				scope.notfilter = false;
				a = {};
				var aux = [];				
				if (scope.selectedCategory.length == 0)
				{
					for (var i in scope.alldata)
					{
						
						if (scope.alldata.hasOwnProperty(i))
						{
							
							
							for (var j in scope.alldata[i])
							{
								
								if (scope.alldata[i].hasOwnProperty(j))
								{
								  
								  lengthline=linea.length;
								  lengthcontract=linea.contract;								

								  if(lengthline > 1 | lengthcontract > 1 ){  //case when contract > 1 or line > 1 oegi
									

									if(lengthcontract > 1 && lengthline > 1){ //case when both >1
									
										for (var w = 0, lengthcontract; w < lengthcontract; w++) {
											for(var z = 0, lengthline; z < lengthline; z++){
												if(linea[z] == scope.alldata[i][j].msisdn && contrato[w] == scope.alldata[i][j].contract ){
													val = true;
													length = length + 1;
													aux.splice(linea, 0, scope.alldata[i][j]); 
													a[i] = aux;
													
												}
											}
											
										
										}
									}else if(lengthcontract > 1){ //case when only contract is set more that once
								
									 	if(linea == ''){
											for(var w = 0, lengthcontract; w < lengthcontract; w++){

												if( contrato[w] == scope.alldata[i][j].contract ){
													val = true;
													length = length + 1;
													aux.splice(linea, 0, scope.alldata[i][j]); //delete 0 elements en line and insert scope.alldata[i][j]
													a[i] = aux;
													
												}
											}
									 	}else{
											for(var w = 0, lengthcontract; w < lengthcontract; w++){

												if(linea == scope.alldata[i][j].msisdn && contrato[w] == scope.alldata[i][j].contract ){
													val = true;
													length = length + 1;
													aux.splice(linea, 0, scope.alldata[i][j]); 
													a[i] = aux;
													
												}
											}	
								        }
									}else if(lengthline > 1){ //case when only line is set more that once
										if(contrato == ''){ //several lines and not set contracts
											for(var z = 0, lengthline; z < lengthline; z++){

												if( linea[z] == scope.alldata[i][j].msisdn ){
													val = true;
													length = length + 1;
													aux.splice(linea, 0, scope.alldata[i][j]); 
													a[i] = aux;
													
												}
											}
									 	}else{ //several lines, a single contract
											for(var z = 0, lengthline; z < lengthline; z++){

												if(linea[z] == scope.alldata[i][j].msisdn && contrato == scope.alldata[i][j].contract ){
													val = true;
													length = length + 1;
													aux.splice(linea, 0, scope.alldata[i][j]); 
													a[i] = aux;
													
												}
											}	
								        }

									}

								  }else if (scope.alldata[i][j].contract == contrato | scope.alldata[i][j].msisdn == linea ){//only one line or one contract
                                         
								
										if( scope.alldata[i][j].msisdn == linea && scope.alldata[i][j].contract == contrato){ //caso donde ambos estan seteados
										    //debe retornar solo la linea 
											
											val = true;
											length = length + 1;
											aux.splice(linea, 0, scope.alldata[i][j]);
											a[i] = aux;
											break;									
										
										}else if( linea == scope.alldata[i][j].msisdn) //caso donde la linea esta seteada
										{
											
											
											if(contrato == ''){
												//debe retornar solo la linea
											
												val = true;
												length = length + 1;
												aux.splice(linea, 0, scope.alldata[i][j]);
												a[i] = aux;
												break;
											}							

											
										}
										else if( linea == '' )//caso donde el contrato esta seteado pero linea es diferente a linea y es undefined
										{
											if(scope.alldata[i][j].contract == contrato )
											{
												val = true;
												length = length + 1;
												a = scope.alldata;   
												break;
											}			
										}						
									
									}





								}
							}
						}
					}
				}
			
			}
			scope.search = linea;
			jQuery('#tagsList label').addClass('active');

			if (length > 0  &&  val == true){
				
				scope.auxScroll = a;

				
			}else if (length > 0  && val == false){
				scope.auxScroll = a;
				
			}else if (linea === '' && contrato === '')
			{
				scope.auxScroll = a;
				
			}else{
				a['No existen datos que coincidan con los criterios de busqueda'] = {};
				scope.auxScroll = a;
				
			}

			//Load data
			scope.invoices = scope.scrollAux();

			if (!notFilter)
			{
				if (length > 0)
				{
					
					scope.saveAuditLog(linea, scope.selectedCategory);
				}
			}
		};

	

		//function to filter data
		scope.autocompleteChangeCategory = function (option) {
			var length = 0;
			a = {};
			var aux = [];
			for (var i in scope.alldata) {
				if (scope.alldata.hasOwnProperty(i)) {
					for (var j in scope.alldata[i]) {
						if (scope.alldata[i].hasOwnProperty(j)) {
							if (scope.alldata[i][j].contract == option || scope.alldata[i][j].msisdn == option || scope.alldata[i][j].address == option) {
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
			} else {
				a['No existen datos que coincidan con los criterios de busqueda'] = {};
				scope.auxScroll = a;
			}

			scope.invoices = scope.scrollAux(length);
			//Save audit log
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
			//Load data
			scope.invoices = scope.scrollAux(1000);
		}

		//Infinite scroll
		scope.loadMore = function () {
			if (scope.sizeAuxScroll() > 0) {
				var sizeInvoice = scope.sizeInvoice();
				scope.invoices = scope.scrollAux(sizeInvoice);
			} else if (typeof scope.invoicesByContract[1] != 'undefined') {
				scope.invoices = scope.scroll(sizeInvoice);
			}
		}

		//function to filter for category
		scope.changeCategory = function ($event)
		{
			var length = 0;
			var notFilter = false;
			if (scope.loadFilter)
			{
				scope.selectedCategory[0] = scope.categoryNameId[scope.parameterFilter];
				scope.loadFilter = false;
			}

			var aux = [];
			if (scope.selectedCategory.length == 0)
			{
				if (scope.search == '')
				{
					a = scope.alldata;
					notFilter = true;
				}
				else
				{
					notFilter = true;
					scope.notfilter = false;
					scope.autocompleteChangeCategory(scope.search);
					a = scope.invoices;
				}
			}
			else
			{
				a = {};
				var aux = [];
				if (scope.search == '')
				{
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
				} else {
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
			} else if (scope.selectedCategory.length === 0) {
				scope.auxScroll = a;
			} else {
				a['No existen datos que coincidan con los criterios de busqueda'] = {};
				scope.auxScroll = a;
			}

			//Load data
			scope.invoices = scope.scrollAux();

			if (!notFilter) {
				if (length > 0) {
					scope.saveAuditLog(scope.search, scope.selectedCategory);
				}
			}
		}

		
	}




	function getParameterByName(name)
	{
		name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			results = regex.exec(location.search);
		return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, ""));
	}

	//function por retrieve information in fixed
	function retrieveInformation(scope, config, el)	{
		
		scope.result = config.config_type;
		if (scope.resources.indexOf(config.url) == -1){	
			//Add key for this display
			var parameters = {};
			parameters['config_columns'] = config.config_columns;
			var config_data =
			{
				params: parameters,
				headers: {'Accept': 'application/json'}
			};

			$http.get(config.url, config_data)
				.then(function (resp){
					
					if (resp.data.error){	
						
						scope.show_mesagge_data = resp.data.message;
						scope.alertas_servicios();
					}else{
					
						scope.invoicesByContract = resp.data;
						scope.alldata = resp.data;
						
						//Scroll
						scope.invoices = scope.scroll();
						scope.auxScroll = resp.data;
						//Load category and autocomplete
						scope.categoryAutocomplete();
					}
					jQuery(el).parents("section").fadeIn('slow');
				},
				function(){console.log("Error obteniendo los datos");
				}
			);
		}
	}

	//Retrieve information both
	function retrieveInformationBoth(scope, config, el) {
		
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
						scope.resultFixed = true;
						scope.show_mesagge_data = resp.data.message;
						scope.alertas_servicios();
					}
					
					if (resp.data == '') {
						scope.resultFixed = true;
					} else {
						scope.resultFixed = resp.data;
						
					}
				}, function () {
					console.log("Error obteniendo los datos");
				});
		}
	}

	//Controller
	CurrentInvoiceController.$inject = ['$scope', '$http'];

	function CurrentInvoiceController($scope, $http)
	{
		// Init vars
		if (typeof $scope.invoicesList == 'undefined')
		{
			$scope.invoicesList = "";
			$scope.invoicesList.error = false;
		}

		if (typeof $scope.resources == 'undefined')
		{
			$scope.resources = [];
		}

		$scope.names = ["Emil", "Tobias", "Linus"];

		var package = {};

		//Add config to url
		var config_data =
		{
			params: package,
			headers: {'Accept': 'application/json'}
		};

		//Send data btn detail -- pending implementation
		$scope.sendDetail = function ($event, contractId, address, category, status, plan, productId, subscriptionNumber, serviceType) {
			$event.preventDefault();
			var url = '/billing/session/data?_format=json';
			//Add key for this display
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
				"serviceType": serviceType
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

		//Function to load scroll
		$scope.scroll = function (size) {
			var aux = [];
			scroll = {};

			if (size === undefined) {
				var quantity = $scope.quantiyScroll;
			} else {
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

		//Scroll Aux
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

		//Calculate size invoices
		$scope.sizeInvoice = function () {
			var size = 0;
			for (var i in $scope.invoices) {
				if ($scope.invoices.hasOwnProperty(i)) {
					size = size + $scope.invoices[i].length;
				}
			}
			return size;
		}

		//Show message service
		$scope.alertas_servicios = function () {
			jQuery(".block-portfolio .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data + '</p></div>');
			$html_mensaje = jQuery('.block-portfolio .messages-only').html();
			jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

			jQuery('.messages .close').on('click', function() {
				jQuery('.messages').hide();
			});
		}

		//Show message service mobile batch
		$scope.alertas_servicios_batch = function () {
			jQuery(".block-portfolio .messages-batch .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_batch + '</p></div>');
			$html_mensaje = jQuery('.block-portfolio .messages-batch').html();
			jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

			jQuery('.messages .close').on('click', function() {
				jQuery('.messages').hide();
			});
		}

		//Calculate size aux scroll
		$scope.sizeAuxScroll = function () {
			var size = 0;
			for (var i in $scope.auxScroll) {
				if ($scope.auxScroll.hasOwnProperty(i)) {
					size = size + $scope.auxScroll[i].length;
				}
			}

			return size;
		}

		//function to enterprise both
		$scope.apiBatchBoth = function (el)
		{
			$scope.counter = 0;
			$scope.apiIsLoadingBoth = function ()
			{
				return $http.pendingRequests.length > 0;
			};

			$scope.$watch($scope.apiIsLoadingBoth, function (v)
			{
				if ($scope.loadingInit == true)
				{
					$scope.counter = $scope.counter + 1;
					if ($scope.counter > 1 && $scope.resultFixed != false)
					{
						$scope.loadingInit = false;

						for (var i in $scope.movilFixed)
						{
							$scope.invoicesByContract[i] = $scope.movilFixed[i];
						}

						if ($scope.resultFixed.error)
						{
							$scope.resultFixed = false;
						}
						for (var i in $scope.resultFixed)
						{
							$scope.invoicesByContract[i] = $scope.resultFixed[i];
						}

						if (Object.keys($scope.invoicesByContract).length > 0)
						{
							$scope.alldata = $scope.invoicesByContract;
							$scope.auxScroll = $scope.invoicesByContract;

							//Scroll
							$scope.invoices = $scope.scroll();

							//Load category and autocomplete
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
						} else {
							$scope.movilFixed = v;
						}
					}

					retrieveInformationBoth($scope, $scope.config, el);
				}
			});

		}

		//function to save audit log
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


 //Clean filters if model exists

 $scope.filterServices = function () {
      var config = drupalSettings.b2bBlock[$scope.uuid];
    var package = {};

    for (filter in config['filters']) {
     
      if (!$scope[filter] == '' || !$scope[filter] === undefined) {
        package[filter] = $scope[filter];
        $scope.filter_status = 2;
      }
    }

	package['config_columns'] = config.config_columns;
	
	$http.get('/rest/session/token').then(function (resp) {
		$http({
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Accept': 'application/json',
				'X-CSRF-Token': resp.data
			},
			data:package,
			url: $scope.config.url
		}).then(function successCallback(response) {
			console.log('success obteniendo el servicio metodo post');
		}, function errorCallback(response) {
			console.log('error obteniendo el servicio metodo post');
		});
	});


  } 

}

	
}
