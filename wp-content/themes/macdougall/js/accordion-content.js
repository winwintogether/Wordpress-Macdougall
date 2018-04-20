/* Accordion Content */
 
 
jQuery(document).ready(function($)  {
	 $('.accordion-item-title').click(function() {	 
		 if ($(this).parent().is('.open')){
			 $(this).closest('.accordion-content').find('.accordion-item-text').animate({'height':'0'},500);
			 $(this).closest('.accordion-content').removeClass('open');
			 $(this).parent().find('.accordion-button-icon').removeClass('fa-minus').addClass('fa-plus');
		 }
		 else{
			 var newHeight =$(this).closest('.accordion-content').find('.accordion-item-text-wrap').height() +'px';
			 $(this).closest('.accordion-content').find('.accordion-item-text').animate({'height':newHeight},500);
			 $(this).closest('.accordion-content').addClass('open');
			 $(this).parent().find('.accordion-button-icon').removeClass('fa-plus').addClass('fa-minus');
		}	 
	 });
});


