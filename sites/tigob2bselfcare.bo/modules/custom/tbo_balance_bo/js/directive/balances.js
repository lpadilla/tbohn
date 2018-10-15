/**
 * Created by lyzmarpadilla on 07/12/17.
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

      $('#submitBtn').click(function(){        
        var a = $("div.linea>div>div>select option:selected").text();
        $("input[name='sender']").val(a);
        
        formName=$(this).data('target');
        $('#'+formName).submit();
      });

      $('#open-md').click(function(){

        var a = $("div.form-item-amount>div>ul").find("li.active.selected").text();

        if(a == null || a == "" || a == undefined){
          a = $("#edit-amount>option:first-child").text();
        }

        var am= a.split(" Bs");
        var amount = parseFloat(am[0]).toFixed(2);

        var c = $("input[name='commission']").val();
        var commission = parseFloat(c).toFixed(2);

        var t = parseFloat(amount) + parseFloat(commission);
        var total = parseFloat(t).toFixed(2);

        $("input[name='amount_select']").val(amount);

        $('div#num-dest-md').text($('#edit-number').val());
        $('div#mon-tf-md').text(amount);
        $('div#monco-tf-md').text(commission);
        $('div#tot-tf-md').text(total);
      });

      $('ul.tabs1 li').click(function(){
        var tab_id = $(this).attr('data-tab');
        if(tab_id == "tab-1"){
          $("ul.tabs1>li#tb2").removeClass('options-active');
          $('ul.tabs1>li#tb1').addClass('options-active');
                         
          $('#tab-2').addClass('current');
          $('#tab-1').removeClass('current');
        }else{
          $("ul.tabs1>li#tb2").addClass('options-active');
          $('ul.tabs1>li#tb1').removeClass('options-active');
                         
          $('#tab-2').removeClass('current');
          $('#tab-1').addClass('current');
        }
      });
            
      $('select').material_select();            
            
      $('#lineasid').change(function() {
        var l=$(':selected').text();  
        var array_lines = $("#line-array").text();
        
        var all_lines = jQuery.parseJSON(array_lines);
        var url = window.location.href;    
        var url_ss= url.split("?");

        url =url_ss[0]+ "?" + "l=" + all_lines[l];
        window.location.href = url;
      });

      $("#edit-amount").change(function(){
        var a = $("div.form-item-amount>div>ul").find("li.active.selected").text();
        var am= a.split(" Bs");
        var amount = parseFloat(am[0]).toFixed(2);

        var c = $("input[name='commission']").val();
        var commission = parseFloat(c).toFixed(2);
        
        var t = parseFloat(amount) + parseFloat(commission);
        var total = parseFloat(t).toFixed(2);

        $('div#mon-tf-md').text(amount);
        $('div#monco-tf-md').text(commission);
        $('div#tot-tf-md').text(total);
      });
    }
  }
})(jQuery);