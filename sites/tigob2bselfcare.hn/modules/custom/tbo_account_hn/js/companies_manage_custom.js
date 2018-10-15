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
				width: 400,
				height: 250,
				modal: true,
				resizable: false,
				draggable: false,
				title: 'Gestión de empresas',
				close: function () {
				}
			});

            $(".ui-dialog-titlebar-close").hide();

		}
	}
})(jQuery);