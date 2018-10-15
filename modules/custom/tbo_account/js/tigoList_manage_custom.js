/**
 * Created by bits on 16/04/17.
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
        width: 470,
        height: 300,
        modal: true,
        resizable: false,
        draggable: false,
        title: 'Desactivar Tigo Admin',
        close: function () {
        }
      });

      $(".ui-dialog-titlebar-close").hide();
    }
  }
})(jQuery);