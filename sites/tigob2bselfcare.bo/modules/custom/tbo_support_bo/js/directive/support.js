/**
 * Created by lyzmarpadilla on 22/03/18.
 */
(function ($) {
  Drupal.behaviors.tboBalance = {
    attach: function (context) {
      $('.modal').modal({
        dismissible: true, // Modal can be dismissed by clicking outside of the modal
        opacity: .5, // Opacity of modal background
        inDuration: 300, // Transition in duration
        outDuration: 200, // Transition out duration
        startingTop: '4%', // Starting top style attribute
        endingTop: '10%' // Ending top style attribute
      });

      $("#open-md").click(function(){
        $("input[name='subject']").val("");
        $("textarea[name='body_mail']").val("");
      });


      $('#submitBtn').click(function(){  
        var a = $("span.agent-span.email").html();  
        $("input[name='mail']").val(a);  

        $('#support-form').submit();
      });

      $('.form-group.form-type-textfield.form-item-subject').removeClass("m6 l6");
      $('.form-group.form-type-textfield.form-item-subject').addClass("m10 l10");

      $('.form-group.form-type-textfield.form-item-body-mail').removeClass("m6 l6");
      $('.form-group.form-type-textfield.form-item-body-mail').addClass("m10 l10");

      
    }
  }
})(jQuery);