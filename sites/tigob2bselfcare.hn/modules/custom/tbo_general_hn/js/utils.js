function drupal_set_message(message, type, uuid, el){
  type = typeof type !== 'undefined' ? type : 'success';
  //jQuery(".ajax-status-message-region").hide();
  remove_drupal_message(uuid);
  if(type == 'error') {
    type = 'danger';
  }
  var html = '<div class="row"><div aria-label="" role="contentinfo" class="messages messages--'+type+' alert alert-'+type+' uuid-'+ uuid +'"><div class="container"><div class="icon"></div><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>'+ message +'</div></div></div>';
  jQuery('.ajax-status-message-region').prepend(html);
  jQuery(".ajax-status-message-region").slideDown(400);
}

function remove_drupal_message(uuid){
  jQuery(".uuid-"+ uuid).remove();
}

function formatDateMoments(){

  function buildMoment(){
    var hasFields = 0;
    jQuery('.format-moments').each(function () {
      hasFields = 1;
      var date = jQuery(this).attr("data-moment-date");      
      if(moment(date).isValid()){
        var value =moment( date ).fromNow();
        jQuery(this).text(value);
      }
    });
    // Set timer to update moment date after 1 minute
    if (hasFields == 1) {
      setTimeout(function () {
        buildMoment();
      }, 60000);
    }
  }

  setTimeout(function () {
    buildMoment();
  }, 10);
}