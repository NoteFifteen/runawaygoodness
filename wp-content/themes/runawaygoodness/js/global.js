jQuery(function( $ ){

	$(".site-header").after('<div class="bumper"></div>');

	$(window).scroll(function () {
	  if ($(document).scrollTop() > 1 ) {
	    $('.site-header').addClass('shrink');
	  } else {
	    $('.site-header').removeClass('shrink');
	  }
	});
    
    $("header .genesis-nav-menu").addClass("responsive-menu").before('<div id="responsive-menu-icon"></div>');
    
    $("#responsive-menu-icon").click(function(){
    	$("header .genesis-nav-menu").slideToggle();
    });
    
    $(window).resize(function(){
    	if(window.innerWidth > 600) {
    		$("header .genesis-nav-menu").removeAttr("style");
    	}
    });
	
});

jQuery(document).ready(function($) {
    // hours entry form validation
    $('#mc-embedded-subscribe-form').submit(function() {
        //do validation
        if ( ! document.getElementById('over18').checked ) {
            alert('The After Dark newsletter contains explicit material and is only intended for people over the age of 18. Please visit runawaygoodness.com to sign up for a more age appropriate newsletter.');
            window.location.replace("http://runawaygoodness.com/");
            return false;
        }
        return true;
    });
});
