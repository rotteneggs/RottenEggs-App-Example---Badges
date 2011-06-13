// JavaScript Document
$(document).ready(function() {
					
	// initialize scrollable third-party jquery with mousewheel support
	$(".scrollable").scrollable({ vertical: true, mousewheel: true });		
					
	$('.unclaimed').click(function() {
		$(this).html('Claiming..');
		var url = $(this).attr('url');
		var badge = $(this).attr('id');
		$.get(url, function(data){																	
														
			// Show the results in the unclaimed button
			$('#'+badge).fadeOut(function() {$(this).html(data);$(this).css({backgroundColor:"#063"});$(this).fadeIn();});				
  				
				
 		});
		
	});


});