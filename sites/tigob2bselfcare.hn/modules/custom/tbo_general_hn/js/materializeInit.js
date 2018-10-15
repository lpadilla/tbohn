/**
 * @file
 * Custom scripts for theme.
 */
(function ($) {

  Drupal.behaviors.materialcss = {
    attach: function (context) {

      select();
      modal();
      calendario();

      if ($(window).width() > 1200) {
        dropdown_box();
      }

      init_tooltip();


      function select(){
        $('select').material_select();
      }

      function modal(){
        $('.modal').modal({
          dismissible: true, // Modal can be dismissed by clicking outside of the modal
          opacity: .5, // Opacity of modal background
          inDuration: 300, // Transition in duration
          outDuration: 200, // Transition out duration
          startingTop: '4%', // Starting top style attribute
          endingTop: '10%', // Ending top style attribute
        });
      }

			$('.modal2').modal({
				dismissible: true, // Modal can be dismissed by clicking outside of the modal
				opacity: .5, // Opacity of modal background
				inDuration: 300, // Transition in duration
				outDuration: 200, // Transition out duration
				startingTop: '4%', // Starting top style attribute
				endingTop: '10%', // Ending top style attribute
			});

      function calendario(){
        $('.datepicker').pickadate({
          selectMonths: true, // Creates a dropdown to control month
          selectYears: 15 // Creates a dropdown of 15 years to control year
        });
        $('.collapsible').collapsible();
      }

      function dropdown_box() {
        $('.dropdown-button').dropdown({
          inDuration: 300,
          outDuration: 225,
          constrainWidth: false, // Does not change width of dropdown to that of the activator
          hover: true, // Activate on hover
          gutter: 0, // Spacing from edge
          belowOrigin: false, // Displays dropdown below the button
          alignment: 'left', // Displays dropdown with edge aligned to the left of button
          stopPropagation: false // Stops event propagation
        });
      }

      function init_tooltip() {
				$('.tooltipped').tooltip({delay: 50});
			}
    }
  }

})(jQuery);
