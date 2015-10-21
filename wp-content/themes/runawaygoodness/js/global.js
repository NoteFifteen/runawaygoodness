jQuery(function( $ ){

	$(".site-header").after('<div class="bumper"></div>');
    
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
    $('#rgsignupform').submit(function() {
        //do validation

        if(jQuery.trim($('#lp-email').val())===''){
            alert('Please supply an email address to receive your free book.');
            return false;
        }

        return true;
    });
});


jQuery(document).ready(function($) {
    // hours entry form validation
    $('.after-dark-home #mc-embedded-subscribe-form').submit(function() {
        //do validation
        
        if(jQuery.trim($('#bt-mce-EMAIL').val())===''){
            alert('Please supply an email address to receive your free book.');
            return false;
        }

        if ( ! jQuery('#over18').attr('checked')) {
            alert('The After Dark newsletter contains explicit material and is only intended for people over the age of 18. Please visit runawaygoodness.com to sign up for a more age appropriate newsletter.');
            window.location.replace("http://runawaygoodness.com/");
            return false;
        }

        return true;
    });
});
