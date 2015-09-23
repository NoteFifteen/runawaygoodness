(function ($) {

    $.fn.stickySidebar = function (options) {

        var config = $.extend({
            headerSelector: '.essb_sidebar_start_scroll',
            navSelector: 'nav',
            contentSelector: '#content',
            footerSelector: '.essb_sidebar_break_scroll',
            sidebarTopMargin: 100,
            footerThreshold: 40,
            defaultTop: '25%'
        }, options);

        var documentHeight = $(document).height();
        var footerHeight = $(config.footerSelector).outerHeight();
        var viewportHeight = $(window).height();
        var breakingPointScroll = documentHeight - (footerHeight) - (viewportHeight / 2);
        var fixSidebr = function () {

            var sidebarSelector = $(this);
            var viewportHeight = $(window).height();
            var viewportWidth = $(window).width();
            var documentHeight = $(document).height();
            var headerHeight = $(config.headerSelector).outerHeight();
            var navHeight = $(config.navSelector).outerHeight();
            var sidebarHeight = sidebarSelector.outerHeight();
            var contentHeight = $(config.contentSelector).outerHeight();
            var footerHeight = $(config.footerSelector).outerHeight();
            var scroll_top = $(window).scrollTop();
            var fixPosition = contentHeight - sidebarHeight;
            var breakingPoint1 = headerHeight + navHeight;
            
            // calculate
            if ((contentHeight > sidebarHeight) && (viewportHeight > sidebarHeight)) {
            	//alert(breakingPointScroll + ' ' + scroll_top);
                if (scroll_top < breakingPoint1) {
                    sidebarSelector.addClass('sticky').css('top', config.defaultTop);

                } else if ((scroll_top >= breakingPoint1) && (scroll_top < breakingPointScroll)) {
                	//alert("break1");
                    sidebarSelector.addClass('sticky').css('top', config.defaultTop);

                } else {
                	//alert("b");
                    var negative = breakingPointScroll - scroll_top;
                    sidebarSelector.addClass('sticky').css('top', negative);

                }

            }
        };

        return this.each(function () {
            $(window).on('scroll', $.proxy(fixSidebr, this));
            $(window).on('resize', $.proxy(fixSidebr, this))
            $.proxy(fixSidebr, this)();
        });

    };

}(jQuery));