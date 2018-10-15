/**
 * @file
 * Custom scripts for theme.
 */
(function ($) {


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



    //menu en mobile.
    Drupal.behaviors.MenuMobile_swiper = {
        attach: function (context) {

            var $posicion = null;
            console.log('pruebas')

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

            // console.log('valor'+$posicion)

            var swiper = '';

            function menu_mobile_ini($posicion) {

                swiper = new Swiper('.swiper-container', {
                    setWrapperSize: true,
                    initialSlide: $posicion,
                    slidesPerView: 'auto',
                    paginationClickable: true,
                    spaceBetween: 0,
                    //slideActiveClass: 'active'
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

            if($('.filters-mobile').length > 0){
                if($('header .filters-mobile').length == 0){
                    $html=$('.filters-mobile').html();
                    $('header').after('<div class="filters-mobile">'+$html+'</div>');
                }
            }


            //primera ventana

         /*   $('.icon-filter > .material-icons').on('click', function() {
                if ($(this).hasClass('closed')){
                    $('.icon-filter > .material-icons, .filters-mobile .filters-mobile-container').removeClass('closed');
                }else{
                    $('.icon-filter > .material-icons, .filters-mobile .filters-mobile-container').addClass('closed');
                }

            });

*/
            $('a.closed').on('click', function() {
                $('.icon-filter .material-icons, .filters-mobile .filters-mobile-container').addClass('closed');

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



})(jQuery);
