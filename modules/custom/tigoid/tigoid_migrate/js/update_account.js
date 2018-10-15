(function ($) {
  $(document).ready(function($) {
    $('input#edit-openid-connect-client-tigoid-connect').on("click",function( event ){
      // Add event GTM
      dataLayer.push({
        'event' : 'tigoIdEvent',
        'selfcareCategory' : "Flow TigoID migration",
        'selfcareAction' : "Suggest migration",
        'selfcareLabel' : "Start migration",
      });
    });


    $( "body" ).click(function( event ) {
        // Add event GTM
      var target = $( event.target );
      if ( target.is( "a" )) {
        if( target.parent().hasClass('in-other-moment') ) {
          var label =  "In another moment";
        }
        else {
          var label = "Click in another link";
        }
        dataLayer.push({
          'event' : 'tigoIdEvent',
          'selfcareCategory' : "Flow TigoID migration",
          'selfcareAction' : "Suggest migration",
          'selfcareLabel' : label,
        });
      }
    });

  });

})(jQuery);

