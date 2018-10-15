(function ($) {

  $(document).ready(function($) {
    $('.next-step').click(function() {
      $('#second-step').fadeIn(400);
      $('#first-step').hide();
    });
    $('.back').click(function() {
      $('#second-step').hide();
      $('#first-step').fadeIn(400);
    });

    $('div#tigoid-new-user a').on("click",function(event){
      // Add event GTM
      dataLayer.push({
        'event' : 'tigoIdEvent',
        'selfcareCategory' : "Flow TigoID migration",
        'selfcareAction' : "Selet type user",
        'selfcareLabel' : "New user",
      });
    });

    $('div#tigoid-user-migration a').on("click",function( event ){
      // Add event GTM
      dataLayer.push({
        'event' : 'tigoIdEvent',
        'selfcareCategory' : "Flow TigoID migration",
        'selfcareAction' : "Selet type user",
        'selfcareLabel' : "Already migrated",
      });
    });
    $('div#tigoid-old-user a').on("click",function( event ){
      // Add event GTM
      dataLayer.push({
        'event' : 'tigoIdEvent',
        'selfcareCategory' : "Flow TigoID migration",
        'selfcareAction' : "Selet type user",
        'selfcareLabel' : "Old user",
      });
    });
  });

})(jQuery);

