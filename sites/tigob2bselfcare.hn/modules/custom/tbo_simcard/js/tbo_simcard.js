/**
 * Created by romanalonso on 27/10/17.
 */
(function ($) {
    Drupal.behaviors.tboSimcard = {
        attach: function (context) {
            $('.modal').modal({
                    dismissible: true, // Modal can be dismissed by clicking outside of the modal
                    opacity: .5, // Opacity of modal background
                    inDuration: 300, // Transition in duration
                    outDuration: 200, // Transition out duration
                    startingTop: '4%', // Starting top style attribute
                    endingTop: '10%' // Ending top style attribute
                }
            );
            $('#submitBtn').click(function(){
                formName=$(this).data('target');
                $('#'+formName).submit();
            })
        }
    }
})(jQuery);