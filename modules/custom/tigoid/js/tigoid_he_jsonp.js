function jsonpTigoIDHECallback(json){
  if(typeof json !== 'undefined' && typeof json['authorized'] !== 'undefined'){
    console.log(json);
    if(json['authorized']){
      location.reload();
    }
  }
}


!function($) {

  function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i <ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0)==' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length,c.length);
      }
    }
    return "";
  }

  var $validate = getCookie("he");
  var $allow_session = getCookie('SESSION_CLOSED');

  if( $validate == "" && $allow_session == "") {

    var httpRequestTest = new XMLHttpRequest();
    httpRequestTest.open('GET', drupalSettings.tigoid_he.authorization_endpoint, false);
    httpRequestTest.send();

    if (httpRequestTest.status == '200') {
      var autorization = JSON.parse(httpRequestTest.responseText);

      $.ajax({
        url: autorization.url,
        type: "GET",
        dataType: "jsonp",
        jsonpCallback: jsonpTigoIDHECallback,
        jsonp: false,
        contentType: "application/json",
      });
    }
  }

}(window.jQuery);
