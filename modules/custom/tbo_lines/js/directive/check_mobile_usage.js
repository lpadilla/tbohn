myApp.directive('ngCheckMobileUsage', ['$http', ngCheckMobileUsage]);


function ngCheckMobileUsage($http) {
    var directive = {
        restrict: 'EA',
        controller: CheckMobileUsage,
        link: linkFunc
    };
    
    return directive;

    function linkFunc(scope, el, attr, ctrl) {
        var config = drupalSettings.checkMobileUsage[scope.uuid_data_ng_check_mobile_usage];
        scope.msisdh = config.msisdh;
        scope.msisdh_con_formato = validateFormatPhone(config.msisdh)['phone'];
        scope.celularInvalida = config.mensajes_de_error.numero_celular_invalido;
        scope.maximoInsuficiente = config.mensajes_de_error.saldo_maximo_insuficiente;
        scope.minimoInsuficiente = config.mensajes_de_error.saldo_minimo_insuficiente;
        scope.saldo_insuficiente = config.mensajes_de_error.saldo_insuficiente;
        scope.status_button = 0;
        scope.transfer_balance = config.transfer_balance;
        scope.cancel_transfer_balance = config.cancel_transfer_balance;
        scope.urlDeSCMU = config.url;
        scope.show_mesagge_data_test = "";

        retrieveType(scope, config, el);

        scope.actualizarSaldo = function() {
          var parameters = [];
          parameters['getDataFinish'] = 'yes';
          var config_data = {
              params: parameters,
              headers: {'Accept': 'application/json'}
          };
          $http.get(scope.urlDeSCMU, config_data)
              .then(function (resp) {
                if (resp.data.error) {
                  scope.show_mesagge_data_test = resp.data.message;
                  scope.alertas_servicios_lines();
                }
                else {
                  // scope.valor_en_saldo = resp.data.saldo;
                  scope.valor_en_saldo = resp.data.saldo_con_formato;
                  scope.fecha_actualizado = resp.data.actualizado;
                  scope.saldo = resp.data.saldo;
                }
              }, function () {
                  console.log("Error obteniendo los datos");
              });
        }
        scope.actualizarSaldo();


        //Formato para el número de teléfono
        scope.phone_first_validate=true;
        scope.onBlurPhone = function() {
          scope.phone_first_validate=false;
        }
        scope.validatePhone = function () {
            document.getElementById('phone').value=document.getElementById('phone').value.split(' ').join('');
            document.getElementById('phone').value=document.getElementById('phone').value.replace(')',') ');
            response = validateFormatPhone(scope.phone);
            var numeroSinFormato = String(scope.removeFormatToPhone(scope.phone));
            if (9 < numeroSinFormato.length) {
              scope.phone_first_validate=false;
            }
            else if(numeroSinFormato.length == 0) {
              scope.phone_first_validate=true;
              statusNormal('phone');
            }
            
            scope.phone = response['phone'];
            if (!scope.phone_first_validate) {
              if (numeroSinFormato.length < 10) {
                document.querySelector('.error-phone').innerHTML=Drupal.t('Debe ingresar 10 dígitos');
              }
              
                scope.phoneStatus = response['status'];
                var elemento_phone = 'phone';
                if (scope.phoneStatus === false) {
                    statusError(elemento_phone);
                } else {
                    statusValid(elemento_phone);
                }
                scope.validateFormShare();
            }
            
            // evita error en samsum
            setTimeout(function () {
                var elemento = document.getElementById('phone');
                elemento.setSelectionRange(scope.phone.length, scope.phone.length);
            }, 100);

            if (numeroSinFormato[0] != '3') {
              document.querySelector('.error-phone').innerHTML=Drupal.t('Número de celular invalido');
            }
        }

        scope.removeFormatToPhone = function(phone) {
          phone = phone.replace(/\s/g, '');
          return phone = phone.replace(/\D/g, '');
        }
         
        scope.realizarTransferencia = function() {
          number_destiny = scope.phone;
          number_destiny = scope.removeFormatToPhone(number_destiny);
          scope.lanzoTrans = true;
          
          url = scope.transfer_balance;
          url = url.replace('{phone_number_origin}', scope.msisdh);
          url = url.replace('{phone_number_destiny}', number_destiny);
          url = url.replace('{value}', scope.montoSinFormato);

          $http.get(url)
            .then(function (resp) {
                var tipo = '';
                switch(resp.data.tipoDeMensaje) {
                  case 'exito':                    
                  var tipo = 'success';
                  scope.actualizarSaldo();
                  break;
                  case 'alerta':
                  var tipo = 'pending';
                  break;
                  case 'error':
                  var tipo = 'danger';
                  break;
                };
                var message = resp.data.mensaje;
                
                scope.setMessage({ type: tipo, message: message });
            }, function () {
                console.log("Error en la trasnferencia");
            });
        }

        //  setMessage({ selector: '#modal2', type: '[success|pending|danger]', message: 'Guardado exitoso', deleteOthersInThisPos: true })
        //  - Si se deja sin "selector" el lo coloca en el ".main-top", que es el area superior de la pagina
        //    donde normalmente vemos los bloques
        //  - Si se deja sin 'tipo' el coloca el tipo 'success'
        scope.setMessage = function (options) {
          options = jQuery.extend({
            // These are the defaults.
            selector: ".main-top",
            type: "success",
            message: "",
            deleteOthersInThisPos: true,
          }, options);

          if (options.deleteOthersInThisPos) { jQuery(options.selector).parent().find('.our-global-messages').remove(); }
          jQuery(options.selector).prepend(
              '<div class="our-global-messages messages clearfix messages--success alert alert-'+options.type+'" role="contentinfo" aria-label="">'+
              '  <button type="button" class="close prefix icon-x-cyan" data-dismiss="alert" aria-hidden="true" onclick="this.parentElement.parentElement.removeChild(this.parentElement)" >'+
              '    <span class="path1"></span><span class="path2"></span>'+
              '  </button>'+
              '  <div class="text-alert">'+
              '    <div class="icon-alert">'+
              '      <span class="icon-1"></span>'+
              '      <span class="icon-2"></span>'+
              '    </div>'+
              '    <div class="txt-message"><p>'+options.message+'</p></div>'+
              '  </div>'+
              '</div>'
          );
        }
        //  Quita los mensajes
        //  removeMessages({ selector: '#modal2' })
        scope.removeMessages = function (options) {
          options = jQuery.extend({
            // These are the defaults.
            selector: ".main-top",
          }, options);

          jQuery(options.selector).parent().find('.our-global-messages').remove();
        };

        //Formato para el Monto
        scope.borrarEnModal = function () {
          scope.phone = '';
          scope.validateFormShare();
          scope.monto = '';
          scope.montoSinFormato = '';
          statusNormal('monto');
          statusNormal('phone');
          scope.removeMessages({ selector: '.share-balance' });
          scope.phone_first_validate=true;
        }
        scope.validateMonto = function () {
            response = validateFormatMonto(scope.monto);
            response['monto'] = response['monto'].split(' ').join('');
            scope.monto = response['monto'];
            scope.montoSinFormato = scope.monto;
            scope.montoStatus = response['status'];
            var elemento_monto = 'monto';
            if (response['monto'] == '') {
                statusNormal(elemento_monto);
            }
            else if (scope.montoStatus === false) {
                statusError(elemento_monto);
            } else {
                statusValid(elemento_monto);
            }

            ///ajuste para equipo samsum
            setTimeout(function () {
                var elemento = document.getElementById('monto');
                elemento.setSelectionRange(scope.monto.length, scope.monto.length);
            }, 100);

            scope.removeMessages({ selector: ".share-balance" });
        }

        //en foco :
        scope.recuperarValor = function () {
            scope.monto = scope.montoSinFormato;
            scope.tieneFoco = true;
        }

        // pierde el foco y vuelvo a valor sin el formato
        scope.validateFormat = function () {
            var valor = scope.monto;
            scope.montoSinFormato = parseInt(valor);
            scope.tieneFoco = false;

            if (scope.monto != '') {

                scope.darFormatoMonedaAlValor(valor,
                    function(valorConFormatoMoneda,scope) {
                        // this callback will be called asynchronously
                        // when the response is available
                        if (scope.tieneFoco == false) {
                            scope.monto = valorConFormatoMoneda;
                        }
                    }, function(response) {
                        // called asynchronously if an error occurs
                        // or server returns response with an error status.
                        console.log('error obteniendo datos');
                    });
            }
            scope.validateFormShare();
        }

        scope.antesDeMostrarMensajeDeConfirmar = function () {
            scope.total_transaccion = scope.montoSinFormato - scope.total_operacion;
            scope.total_transaccion_sin_formato = scope.total_transaccion;

            //  Realiza la petición para cambiar de moneda
            scope.darFormatoMonedaAlValor(scope.total_transaccion, function(currency, scope) {
                scope.total_transaccion = currency;
            });
        }

        scope.darFormatoMonedaAlValor = function(valorOriginal, succesCallback, errorCallback) {
            var mySuccesCallback = succesCallback;
            var myErrorCallback = (errorCallback?errorCallback:function() {  });

            if (valorOriginal != '') {
                if (jQuery.isNumeric(valorOriginal)) {
                    $http({
                        method: 'GET',
                        url: '/tbolines/validate-and-mask-currency/' + valorOriginal + '?_format=json'
                    }).then(function successCallback(response) {
                        mySuccesCallback(response.data.currency, scope);
                    }, function errorCallback(response) {
                        myErrorCallback(scope);
                    });
                }
            }
        }

        // sin foco: valido si el monto está dentro de los rangos (menor que saldo - maximo - minimo )
        scope.esMontoValido = function () {
            if (scope.saldo >= scope.montoSinFormato) {
                if (scope.montoSinFormato <= scope.valor_maximo) {
                    if (scope.montoSinFormato >= scope.valor_minimo) {
                        //if (!jQuery('.messages-share-balance').hasClass('closed-messages')) {
                          //  jQuery('.messages-share-balance').addClass('closed-messages');
                        //}
                        scope.removeMessages({ selector: ".share-balance" });
                        return true;
                    } else {
                        scope.error_popup = scope.maximoInsuficiente;
                       // jQuery(".messages-share-balance").removeClass('closed-messages');
                        scope.setMessage({ selector: ".share-balance", type: 'danger', message: scope.minimoInsuficiente });
                        return false;
                    }
                } else {
                    scope.error_popup = scope.maximoInsuficiente;
                   // jQuery(".messages-share-balance").removeClass('closed-messages');
                    scope.setMessage({ selector: ".share-balance", type: 'danger', message: scope.maximoInsuficiente });
                    return false;
                }
            }
            var error_elemento= 'monto';
            statusError(error_elemento);
            scope.setMessage({ selector: ".share-balance", type: 'danger', message: scope.saldo_insuficiente });
            return false;
        }

        //validacion para habilitar el botón compartir:
        scope.validateFormShare = function (){

           var phone_status = false;
            var monto_status = false;

            if (typeof scope.phone !== 'undefined' && scope.phone.length == 14 && scope.phoneStatus) {
                phone_status = true;
            }

            if (typeof scope.monto !== 'undefined' && scope.montoStatus) {
                if (scope.esMontoValido()) {
                    monto_status = true;
                }
            }

            if (phone_status && monto_status){
                scope.status_button = 1;
            }else{
                scope.status_button = 0;
            }
        }

        jQuery('.confirm-share-balance.trasnfer-balance').modal({
          complete: function() {
            if(!scope.lanzoTrans) {
              number_destiny = scope.phone;
              number_destiny = scope.removeFormatToPhone(number_destiny);
              
              url = scope.cancel_transfer_balance;
              url = url.replace('{phone_number_origin}', scope.msisdh);
              url = url.replace('{phone_number_destiny}', number_destiny);
              url = url.replace('{value}', scope.montoSinFormato);
    
              $http.get(url)
                .then(function (resp) {
                  
                }, function () {
                    console.log("Error en la trasnferencia");
                });
            }
            scope.lanzoTrans = false;

          } // Callback for Modal close
        });

        //funciones para validar el estado los input :(error)
        function statusNormal(elemento) {
            jQuery('#'+elemento).removeClass('invalid-'+elemento);
            jQuery('#'+elemento).removeClass('error');
            jQuery('#'+elemento).removeClass('valid-'+elemento);
            jQuery('#'+elemento).removeClass('valid');
        }
        //funciones para validar el estado los input :(error)
        function statusError(elemento,tipoError) {
            jQuery('#'+elemento).removeClass('invalid-'+elemento);
            jQuery('#'+elemento).removeClass('error');
            jQuery('#'+elemento).removeClass('valid-'+elemento);
            jQuery('#'+elemento).removeClass('valid');
            jQuery('#'+elemento).addClass('invalid-'+elemento);
            jQuery('#'+elemento).addClass('error');
        }
        //: (valido)
        function statusValid(elemento) {
            jQuery('#'+elemento).removeClass('invalid-'+elemento);
            jQuery('#'+elemento).removeClass('error');
            jQuery('#'+elemento).removeClass('valid-'+elemento);
            jQuery('#'+elemento).removeClass('valid');
            jQuery('#'+elemento).addClass('valid-'+elemento);
            jQuery('#'+elemento).addClass('valid');
        }
    }

    function retrieveType(scope, config, el) {
        //Add key for this display
        var parameters_t = {};
        var config_data_t = {
          params: parameters_t,
          headers: {'Accept': 'application/json'}
        };
      $http.get('/tboapi/lines/info/line?_format=json', config_data_t)
        .then(function (resp) {
            scope.type_service = resp.data;
            console.info(resp.data);
        }, function () {  
        console.log("Error obteniendo el tipo");
      });
    }

    CheckMobileUsage.$inject = ['$scope', '$http'];
    function CheckMobileUsage($scope, $http) {
      //Show message service
      $scope.alertas_servicios_lines = function () {
        jQuery(".set-up-mobile-usage .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_test + '</p></div>');
        $html_mensaje = jQuery('.set-up-mobile-usage .messages-only').html();
        jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

        jQuery('.messages .close').on('click', function() {
          jQuery('.messages').hide();
        });
      }
    }
}