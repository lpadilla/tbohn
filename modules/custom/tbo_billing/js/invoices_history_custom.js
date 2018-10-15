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
			$('#modal_history').dialog({
				autoOpen: false,
				width: 400,
				height: 300,
				modal: true,
				resizable: false,
				draggable: false,
				dialogClass: 'noTitleStuff',
				close: function () {
				}
			});

			$(".ui-dialog-titlebar").hide();
		}
	}
})(jQuery);