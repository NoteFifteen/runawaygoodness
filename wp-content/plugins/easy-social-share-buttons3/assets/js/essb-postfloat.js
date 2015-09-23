jQuery(document).ready(function($){

	function exist_element(oID) {
		return jQuery(oID).length > 0;
	}
		 
	var essb_postfloat_height_break = 0;
	if ($('.essb_break_scroll').length) {
		var break_position = $('.essb_break_scroll').position();
		var break_top = break_position.top;
		
	}

	if (!exist_element(".essb_displayed_postfloat")) { return; }

	var top = $('.essb_displayed_postfloat').offset().top - parseFloat($('.essb_displayed_postfloat').css('marginTop').replace(/auto/, 0));
	var basicElementWidth = '';
	var postfloat_always_onscreen = false;
	if (typeof(essb_settings) != "undefined") {
		postfloat_always_onscreen = essb_settings.essb3_postfloat_stay;
	}
	var custom_user_top = 0;
	if (typeof(essb_settings) != "undefined") {
		if (typeof(essb_settings['postfloat_top']) != "undefined") {
			custom_user_top = essb_settings["postfloat_top"];
			custom_user_top = parseInt(custom_user_top);
			
			top -= custom_user_top;
		}
	}
	
	$(window).scroll(function (event) {
    // what the y position of the scroll is
		var y = $(this).scrollTop();

    // whether that's below the form
		if (y >= top) {
      // if so, ad the fixed class
			$('.essb_displayed_postfloat').addClass('essb_postfloat_fixed');
      
			var element_position = $('.essb_displayed_postfloat').offset();
			var element_height = $('.essb_displayed_postfloat').outerHeight();
			var element_top = parseInt(element_position.top) + parseInt(element_height);
			
			if (!postfloat_always_onscreen) {
			if (element_top > break_top) {
				if (!$('.essb_displayed_postfloat').hasClass("essb_postfloat_breakscroll")) {
					$('.essb_displayed_postfloat').addClass("essb_postfloat_breakscroll");
				}
			}
			else {
				if ($('.essb_displayed_postfloat').hasClass("essb_postfloat_breakscroll")) {
					$('.essb_displayed_postfloat').removeClass("essb_postfloat_breakscroll");
				}
			}
			}
		} 
		else {
      // otherwise remove it
      $('.essb_displayed_postfloat').removeClass('essb_postfloat_fixed');
    }
  });


});
