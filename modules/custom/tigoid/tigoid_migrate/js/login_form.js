(function ($) {

  $(document).ready(function($) {
    $('input#edit-submit').on("click",function(){
      // Add event GTM
      dataLayer.push({
        'event' : 'tigoIdEvent',
        'selfcareCategory' : "Flow TigoID migration",
        'selfcareAction' : "Start authentication",
        'selfcareLabel' : "Old user",
      });
    });
  });

})(jQuery);

