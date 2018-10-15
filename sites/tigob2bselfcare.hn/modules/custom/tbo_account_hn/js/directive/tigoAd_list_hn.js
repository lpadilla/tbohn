/**
 * @file
 * Custom scripts for filters.
 */
(function ($) {
	//
	Drupal.behaviors.cleanfilter = {
		attach: function (context) {

			jQuery("#tagsList").click(function(){ 				
					$('.row').css('max-height', '800px'); //set max height			
			});
				//Reser filters
				jQuery('.click-filter-reset').click(function () {
					//reset filters
					jQuery('.input-field label').addClass('active');
					if (jQuery('.field-status label').hasClass('active')) {            
						jQuery('.field-status label').removeClass('active');       
					}  
					
					if (jQuery('.form-item-rol label').hasClass('active')) {   
						jQuery('.form-item-rol label').removeClass('active');       
					}
					
					if (jQuery('.Estado label').hasClass('active')) {   
						jQuery('.Estado label').removeClass('active');       
					}
					if (jQuery('.form-item-document-type label').hasClass('active')) {   
						jQuery('.form-item-document-type label').removeClass('active');       
					}
				//	var config = drupalSettings.TigoAdminListBlock[$scope.uuid];

					//Get value filters
					

				});
			
		}
	}
})(jQuery);