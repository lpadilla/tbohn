/**
 * Created by bits on 21/06/17.
 */
/**
 * @file
 * Custom scripts for theme.
 */
(function ($) {
  //
  Drupal.behaviors.custoModal = {
    attach: function (context) {
      $('#modal').dialog({
        autoOpen: false,
        width: 572,
        height: 270,
        modal: true,
        resizable: false,
        draggable: false,
        open: function(){
          $('.ui-widget-overlay').bind('click',function(){
						$('.preloading-category-services').attr('style', 'display: none !important');
            $('#modal').dialog('close');
          })
        }
      });

      $('#modalForm').dialog({
        autoOpen: false,
        width: 280,
        height: 464,
        modal: true,
        resizable: false,
        draggable: false,
        open: function(){
          $('.ui-widget-overlay').bind('click',function(){
						$('.preloading-category-services').attr('style', 'display: none !important');
            $('#modalForm').dialog('close');
          })
        },
        buttons: [
          {
            text: "Cancelar",
            class: 'btn-primary btn waves-effect waves-light segment-click',
            'data-segment-event':'TBO - Te llamamos',
            'data-segment-properties':'{"category":"Dashboard","label":"Cancelar {[{ select_category }]}","site":"New"}',
            click: function() {
							$('.preloading-category-services').attr('style', 'display: none !important');
              $( this ).dialog( "close" );
            }
          }
        ]
      });

      $(".ui-dialog-titlebar").hide();

      $('#modal').on('click', '.buttons', function(){
				$('.preloading-category-services').attr('style', 'display: none !important');
        $('#modal').dialog('close');
      }).on('click', 'a.popup', function(event){
        event.preventDefault();
        var url = $(this).attr('href');
        $('#modalForm').html('<iframe src="'+url+'" width="260px" height="370px">').dialog('open');

        var $btnModal = $('div[aria-describedby="modalForm"] .ui-dialog-buttonpane .ui-dialog-buttonset > button');

        $btnModal.removeClass('ui-button ui-widget');
        $btnModal.removeClass('ui-state-default');
        $btnModal.removeClass('ui-corner-all');
        $btnModal.removeClass('ui-button-text-only');
        $btnModal.removeClass('button');

				$('.preloading-category-services').attr('style', 'display: none !important');
        $('#modal').dialog('close');
      });
    }
  }
})(jQuery);
