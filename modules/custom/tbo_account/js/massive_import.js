(function ($) {
  //
  Drupal.behaviors.massiveImport = {
    attach: function (context) {
      $(context).on( 'submit', '#import-data-form',function(h) {
        console.log('submit');

        var config = drupalSettings.importMassive;
        console.log(config);

        console.log(h);

        var file = $('#massiveImport');
        console.log(file);

        var file_data = file.prop('files')[0];
        var type_data = file_data.type;
        var fd = new FormData();
        console.log(file_data);
        console.log(type_data);

        fd.append('file', file[0].files[0]);
        console.log(fd);

        var myData = {
          other: 'hi'
        };
        $.ajax({
          type: 'GET',
          url: '/rest/session/token',
          success: function(data_resp) {
            console.log(data_resp);
            $.ajax({
              url: '/adf/process_data?_format=json',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': data_resp
              },
              dataType: 'json',
              processData: false,
              //contentType: false,
              data: myData,
              type: 'POST',
              success: function(resp) {
                console.log(resp);
                console.log('exito');
              }
            });
          }
        });
        /*$.ajax({
          url: config.url,
          dataType: 'script',
          processData: false,
          contentType: false,
          cache: false,
          data: form_data,
          type: 'POST',
          success: function() {
            console.log('exito');
          }
        });*/


      });
    }
  }
})(jQuery);