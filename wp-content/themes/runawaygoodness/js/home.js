
jQuery(function( $ ){
	
    $('.home-featured .wrap') .css({'height': (($(window).height()))+'px'});
    $(window).resize(function(){
        $('.home-featured .wrap') .css({'height': (($(window).height()))+'px'});
    });
    
    $(".home-featured .home-widgets-1 .widget:last-child").after('<p class="arrow"><a href="#home-widgets"></a></p>');
    
    $.localScroll({
    	duration: 750
    });
	
});

jQuery(function( $ ) {
    $('#group7249').change(function(){
        var options = group7249.options;
        var id      = options[options.selectedIndex].id;
        var value   = options[options.selectedIndex].value;
        
        $(this).attr('name', id); // this will change the name attribute
    });
});
