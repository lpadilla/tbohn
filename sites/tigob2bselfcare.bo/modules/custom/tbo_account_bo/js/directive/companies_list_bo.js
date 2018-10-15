(function ($) {	
	Drupal.behaviors.cleanFilters = {
		attach: function (context) {
      jQuery('.click-filter-reset').click(function () {
        //reset filters        
        jQuery('.input-field label').addClass('active');
           
        if (jQuery('.form-item-user label').hasClass('active')) {              
          jQuery('.form-item-user label').removeClass('active');       
      }  
      }); 

      $("#edit-user-create").click(function () {
        $(".form-item-user-fielset-user-form-admin-name label").addClass('active');
        $(".form-item-user-fielset-user-form-admin-mail label").addClass('active');
        $(".form-item-user-fielset-user-form-admin-phone label").addClass('active');
      });

      $("#edit-user-fielset-user-form-admin-name").focusout(function () {
        $(".form-item-user-fielset-user-form-admin-mail label").addClass('active');
        $(".form-item-user-fielset-user-form-admin-phone label").addClass('active');
      });

      $("#edit-user-fielset-user-form-admin-mail").focus(function () {
        $(".form-item-user-fielset-user-form-admin-name label").addClass('active');
        $(".form-item-user-fielset-user-form-admin-phone label").addClass('active');
      });


      $("#edit-user-fielset-user-form-admin-phone").focus(function () {
        $(".form-item-user-fielset-user-form-admin-mail label").addClass('active');
        $(".form-item-user-fielset-user-form-admin-name label").addClass('active');
      });

      $("#edit-user-fielset-user-form-admin-name").blur(function () {
        $(".form-item-user-fielset-user-form-admin-name label").addClass('active');
      });

      $("#edit-user-fielset-user-form-admin-mail").blur(function () {
        $(".form-item-user-fielset-user-form-admin-mail label").addClass('active');
      });


      $("#edit-user-fielset-user-form-admin-phone").blur(function () {
        $(".form-item-user-fielset-user-form-admin-phone label").addClass('active');
      });
                
		}
	}
})(jQuery);
