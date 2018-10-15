(function ($) {	
	Drupal.behaviors.cleanFilters = {
		attach: function (context) {
      jQuery('.click-filter-reset').click(function () {
        jQuery('.input-field label').addClass('active');                                                 
                                                                                                     
        if (jQuery('.export label').hasClass('active')) {                                                   
          jQuery('.export label').removeClass('active');                                      
        }                                                                                                   
        if (jQuery('.field-user_role label').hasClass('active')) {                                          
          jQuery('.field-user_role label').removeClass('active');                              
        }
        if (jQuery('.Tipo label').hasClass('active')) {                                          
          jQuery('.Tipo label').removeClass('active');                              
        } 

                                                                                                        

      });           
		}
	}
})(jQuery);

 


