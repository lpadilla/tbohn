(function ($) { 
	Drupal.behaviors.simcard = {  
		attach: function (context) {   
			$('.num-simcard').on("input",function(e){
				$('.enterprice').val($('.num-simcard').val());
			});
			$('.num-simcard').change(function(){
				$('.enterprice').val($(this).val());
				var h="/change_simcard/"+$(this).val();
				$('.content-modal a').attr("href", h);
				$('.content-modal').css('display','block');
				$('.url_sim').css('display','block');
			});

      		$('.modal').modal({
	            dismissible: true, // Modal can be dismissed by clicking outside of the modal
	            opacity: .5, // Opacity of modal background
	            inDuration: 300, // Transition in duration
	            outDuration: 200, // Transition out duration
	            startingTop: '4%', // Starting top style attribute
	            endingTop: '10%', // Ending top style attribute
            	
            	complete: function() {            	
            		$('#tbo-billing-change-simcard-hn').submit();
				} // Callback for Modal close
          	});			
		} 
	}
})(jQuery);