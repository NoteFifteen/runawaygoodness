
jQuery(document).ready(function ($) {
    'use strict';

    //- Remove the <a> tag from our admin_menu separators
    $('a div[class="separator"]').unwrap();

    $.integralUrlParam = function (name, url) {
        if (!url) {
            url = window.location.href;
        }
        var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(url);
        if (!results) {
            return undefined;
        }
        return results[1] || undefined;
    };

    //- If we're on the widget page from our link
    if ($.integralUrlParam('highlight_imc')) {
        $('div[id*=imc_subscribe_widget]').addClass('imc-subscribe-widget-wrapper').click(function () {
            $('div[id*="imc_subscribe_widget"]').removeClass('imc-subscribe-widget-wrapper');
        });
    }


});

function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min)) + min;
}
