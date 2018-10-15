/**
 * @file
 * Custom scripts for theme.
 */
(function ($) {
    //tamaño del menu lateral version desktop
    Drupal.behaviors.active_menu = {
        attach: function (context) {

            search_mobile();

            /**** duplico el menu usuario para version mobile ***/
            if($('.account_dropdown_wrapper ul#account_dropdown').length > 0) {
                $html_menuAccount = $('.account_dropdown_wrapper ul#account_dropdown').html();
                $('header .user-menu .material-icons').after('<div class="menu_usuario_mobile">' + $html_menuAccount + '</div>');
            }

            /****** Inicializo el menu y submenu del menu usuario en desktop ****/
            $('.dropdown-account').dropdown({
                inDuration: 300,
                outDuration: 225,
                constrainWidth: false, // Does not change width of dropdown to that of the activator
                gutter: 0, // Spacing from edge
                belowOrigin: false, // Displays dropdown below the button
                alignment: 'left', // Displays dropdown with edge aligned to the left of button
                stopPropagation: false // Stops event propagation
            });

            /**** sub-menu ***/
            $(".dropdown-button2").bind('click', function(e) {
                e.stopPropagation();

                if ($(this).hasClass('click')){
                    $(this).removeClass('icon-up');
                    $(this).addClass('icon-down');
                    $('.dropdown-button2').removeClass('click');
                }else{
                    //$('.dropdown-button2').removeClass('click');
                    //$('.dropdown-button2').removeClass('icon-down');
                    //$('.dropdown-button2').addClass('icon-up');

                    $(this).addClass('click');
                    $(this).removeClass('icon-down');
                    $(this).addClass('icon-up');
                }

            });

            /****** inicializo el menu mobile ****/
             $(".button-collapse").sideNav();
             $(".collapsible").collapsible();
             $swiche_submenu=true;
             $("#slide-out .collapsible").bind('click', function(e) {
                 if($swiche_submenu){
                     $(this).find('.collapsible-body').css({'display':'block'});
                     $(this).find('.collapsible-header.is_expanded').removeClass('icon-down');
                     $(this).find('.collapsible-header.is_expanded').addClass('icon-up');
                     $swiche_submenu=false;
                 }else{
                     $(this).find('.collapsible-body').css({'display':'none'});
                     $(this).find('.collapsible-header.is_expanded').removeClass('icon-up');
                     $(this).find('.collapsible-header.is_expanded').addClass('icon-down');
                     $swiche_submenu=true;
                 }
             });

            /****** función para dektop y mobile, show-hide los elementos de cuenta ****/
            $swiche_options=true;
            $el=$('#account_dropdown, #slide-out');
            $("#account_dropdown .options, #slide-out .options").bind('click', function(e) {
                e.stopPropagation();
                if($swiche_options){
                    $($el).find('.menu-option2').stop(true,true).slideDown(400);
                    $(this).find('.prefix').removeClass('icon-downside');
                    $(this).find('.prefix').addClass('icon-upside');
                    $swiche_options=false;
                }else{
                    $($el).find('.menu-option2').stop(true,true).slideUp();
                    $(this).find('.prefix').removeClass('icon-upside ');
                    $(this).find('.prefix').addClass('icon-downside');
                    $swiche_options=true;
                }
            });


            /****** Inicializo el menu de ayuda tarjeta de credito****/
            $('.help-card').dropdown({
                inDuration: 300,
                outDuration: 225,
                constrainWidth: false, // Does not change width of dropdown to that of the activator
                hover: true, // Activate on hover
                gutter: 0, // Spacing from edge
                belowOrigin: false, // Displays dropdown below the button
                alignment: 'center', // Displays dropdown with edge aligned to the left of button
                stopPropagation: false // Stops event propagation
            });

            function search_mobile() {

              $searchButton = $('#search-block-form #edit-actions')
              $searchForm = $('#search-block-form .form-type-search')

              if ($(window).width() < 993) {
                $searchButton.bind('click', function() {
                  if($searchButton.hasClass('active')) {
                    $searchForm.slideUp()
                    $searchButton.removeClass('active')
                    return false
                  }
                  $searchForm.slideDown(200)
                  $searchButton.addClass('active')
                  return false
                });

              }
            }

            $( window ).resize(search_mobile);


            /****** activo el icon.down cuando está activo la factura ****/
            if ($('ul.submenu li').hasClass('active')){

                if (!$('ul.menu-side li.is-active').hasClass('active')){
                    $('ul.menu-side li.is-active').addClass('active');
                    $('ul.menu-side li.is-active .collapsible-header').addClass('active');
                }
            }

        }
    }



    //menu desktop
    Drupal.behaviors.menu_desktop = {
        attach: function (context) {
            $swich=false;

            $(".hambuger-menu").bind('click', function(e) {
                e.preventDefault();
                if(!$swich){
                    $('.tbo_main_menu').removeClass('open');
                    $('.tbo_main_menu').addClass('closed');
                    $('main').addClass('fluid');
                    $swich=true;
                }else{
                    $('.tbo_main_menu').removeClass('closed');
                    $('.tbo_main_menu').addClass('open');
                    $('main').removeClass('fluid');
                    $swich=false;
                }
            });
        }
    }


    // on-off los label de los formularios
    Drupal.behaviors.onof_formulario = {
      attach: function (context) {
        if($('form').length > 0){
          $('.btn-clear').bind('click', function(e) {
              $('form label.active').removeClass('active');
          });
        }
      }
    }


    // on-off los label de los formularios
    Drupal.behaviors.ini_scroll = {
        attach: function (context) {
            if($('.scroll-pane').length > 0){
               
                $('.scroll-pane').jScrollPane({autoReinitialise: true});
            }
        }
    }


    /***** titulos de los pop up ***/
    Drupal.behaviors.title_modal= {
        attach: function (context) {
            if($('.modal > h2').length > 0){
                $title_modal = $('.modal > h2').html();
                $('.modal .modal-content').find('> div, > form').before('<h2 class="title-modal">' + $title_modal + '</h2>');
                $('.modal > h2').remove();
            }
        }
    }

    /***** anexo todos los formularios que necesitan ajustar sus clases de materialize ***/

    Drupal.behaviors.formularios = {
        attach: function (context) {
            /***** formulario de tarjeta de credito domiciliar ***/
            if($('.form-custom').length > 0){
              $('.form-custom .items .input-field').removeClass('input-field col s12 m6 l6');
            }
            /**** formulario de carga masiva ***/
            if($('form#create-massive-enterprise').length > 0){
                $('form#create-massive-enterprise').find('.input-field').removeClass('input-field col s12 m6 l6');
                $('form#create-massive-enterprise').find('#ajax-wrapper, .btn-massive').addClass('input-field col s12 m6 l6');
            }
        }
    }


    /***** anexo todos los formularios que necesitan ajustar sus clases de materialize ***/

    Drupal.behaviors.mover_element = {
      attach: function (context) {
      /***** formulario de tarjeta de credito domiciliar ***/
       // $('#body-payment-box-mobile .scroll-pane').jScrollPane();
        if(($(window).width()) < 993){
          $('#footer-top').on('click', function () {
            if ($('#body-payment-box-mobile').hasClass('hide-table')){
               // $('#body-payment-box-mobile .scroll-pane').jScrollPane();
              $('#body-payment-box-mobile').removeClass('hide-table');
            }else{
              $('#body-payment-box-mobile').addClass('hide-table');
            }
          });
        }
      }
    }



    //menu en mobile.
    Drupal.behaviors.MenuMobile_swiper = {
        attach: function (context) {

            var $posicion = null;

            // Calcular el ancho de cada slide dado el largo del texto del a interno
            $(".box-menu-mobile .swiper-slide").each(function (index, value) {
                var width_a = $("a", this).outerWidth() + 4;
                $(this).width(width_a);
            });

            //Busco la posicion del active para inicializar el menu en mobile
            $('.swiper-slide').each(function () {
                if ($(this).hasClass('active')) {
                    $posicion = $(this).index();
                }
            });

            menu_mobile_ini($posicion);


            var swiper = '';

            function menu_mobile_ini($posicion) {

                swiper = new Swiper('.swiper-container', {
                    setWrapperSize: true,
                    initialSlide: $posicion,
                    slidesPerView: 'auto',
                    paginationClickable: true,
                    spaceBetween: 0,
                    slideActiveClass: 'active'
                });

            }

            $(window).on('resize', function () {
                //swiper.slideTo($posicion, 200);

            });


        }//cierro attach
    }//cierro MenuMobile_swiper

    //filtros en version mobile
    Drupal.behaviors.filtros_mobile = {
        attach: function (context) {

            if($('.block-currentinvoice').length > 0){
                if($('.filters-mobile').length > 0){
                    if($('header .filters-mobile').length == 0){
                        $html=$('.filters-mobile').html();
                        $('header').after('<div class="filters-mobile">'+$html+'</div>');
                    }
                }
            }


            /**** duplico filtros de portafolio para version mobile ***/

            if ($('.block-portfolio').length > 0) {
                if ($('.wrapper-filter').length > 0) {
                    if ($('header .filters-mobile').length == 0) {
                        $html_filtros = $('.filter-portfolio').html();
                        $('header').after('<div class="filters-mobile"><form class="filter-portfolio">' + $html_filtros + '</form></div>');
                        if ($('.filters-mobile .box-filter').length > 0) {
                            $('.filters-mobile .row .input-field:first-child').addClass('hide-on-med-and-down');
                        }
                    }

                }
            }



            $('a.closed').on('click', function() {
                $('.icon-filter .material-icons, .filters-mobile .filters-mobile-container').addClass('closed');
            });

            //cerrar ventanas de alerta

            $('.messages .close').on('click', function() {
                $('.messages').hide();
            });





            //segunda ventana
            $('.items-filter .icons.closed').on('click', function() {

                if ($(this).hasClass('closed')){
                    $(this).removeClass('closed');
                    $id= $(this).attr("id");
                    $('.window-second .filters-mobile .row .'+$id).css({'display':'block'});
                    $('.window-second').show(300);
                }
            });

            $('span.volver').on('click', function() {
                $('.window-second').hide(300);
                $('.window-second .filters-mobile .filter-mobile').css({'display':'none'});
                $('.items-filter .icons').addClass('closed');
            });



        }
    }


    closeMessage = function () {
      jQuery(".message-payment-result" ).remove();
    }


})(jQuery);
