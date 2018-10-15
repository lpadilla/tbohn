/**
 * Created by bits on 3/05/17.
 */
/**
 * @file
 * Custom scripts for theme.
 */
(function ($) {
	Drupal.behaviors.toolsB2b = {
		attach: function (context) {
			$('.modal-close-b2b').on('click', function (event) {
				event.preventDefault();
				window.location.reload();
			})
		}
	}
})(jQuery);
