jQuery(document).ready(
		function($) {

			function exist_element(oID) {
				return jQuery(oID).length > 0;
			}

			if (!exist_element(".essb_displayed_float")) {
				return;
			}

			var top = $('.essb_displayed_float').offset().top
					- parseFloat($('.essb_displayed_float').css('marginTop')
							.replace(/auto/, 0));
			var basicElementWidth = '';
			
			var hide_float_percent = (typeof(essb_settings['hide_float']) != "undefined") ? essb_settings['hide_float'] : '';
			var custom_top_postion = (typeof(essb_settings['float_top']) != "undefined") ? essb_settings['float_top'] : '';
			var hide_float_active = false;
			if (hide_float_percent != '') {
				if (Number(hide_float_percent)) {
					hide_float_percent = parseInt(hide_float_percent);
					hide_float_active = true;
				}
			}
			var active_custom_top = false;
			if (custom_top_postion != '') {
				if (Number(custom_top_postion)) {
					custom_top_postion = parseInt(custom_top_postion);
					active_custom_top = true;
				}
			}
			
			$(window).scroll(
					function(event) {
						// what the y position of the scroll is
						var y = $(this).scrollTop();
						
						if (active_custom_top) {
							y -= custom_top_postion;
						}
						
						var height = $(document).height()-$(window).height();
						var percentage = y/height*100;
						// whether that's below the form
						if (y >= top) {
							// if so, ad the fixed class
							if (basicElementWidth == '') {
								var widthOfContainer = $('.essb_displayed_float').width();
								basicElementWidth = widthOfContainer;
								$('.essb_displayed_float').width(widthOfContainer);
							}
							$('.essb_displayed_float').addClass('essb_fixed');

						} else {
							// otherwise remove it
							$('.essb_displayed_float').removeClass('essb_fixed');
							if (basicElementWidth != '') {
								$('.essb_displayed_float').width(basicElementWidth);
							}
						}
						
						if (hide_float_active) {
							if (percentage >= hide_float_percent && !$('.essb_displayed_float').hasClass('hidden-float')) {
								$('.essb_displayed_float').addClass('hidden-float');
								$('.essb_displayed_float').fadeOut(100);
								return;
							}
							if (percentage < hide_float_percent && $('.essb_displayed_float').hasClass('hidden-float')) {
								$('.essb_displayed_float').removeClass('hidden-float');
								$('.essb_displayed_float').fadeIn(100);
								return;
							}
						}
					});

		});
