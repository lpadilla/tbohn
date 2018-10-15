
/**
 * @file
 * Custom scripts for theme.
 */

(function ($) {

  Drupal.behaviors.selfcareGTM = {
    attach: function (context, settings) {
      //alert(drupalSettings.gtm);

      $(drupalSettings.gtm).each(function(element){
        console.log(drupalSettings.gtm[element]);
        dataLayer.push({
          "event" : drupalSettings.gtm[element].event,
          "selfcareCategory" : drupalSettings.gtm[element].category,
          "selfcareAction" : drupalSettings.gtm[element].action,
          "selfcareLabel" : drupalSettings.gtm[element].label
        });
      });
    }
  }
})(jQuery);
