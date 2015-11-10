
jQuery(document).ready(function ($) {
    'use strict';

    // make table rows sortable using JQueryUI Sortable: http://api.jqueryui.com/sortable/
    $('.imc-tag-group-active, .imc-tag-group-inactive').sortable({
        connectWith: ".imc-tag-group",
        containment: ".tab-pane.active",
        cursor: 'move',
        cancel: '.disable-sort',
        receive: function (event, ui) {

            //$('#message').html('').css('opacity', 0);
            //$('.imc-tag-group').sortable('disable');
            $('.imc-tag-group').addClass('disable-sort');

            //- get the tag that was just moved
            var merge_tag_box = ui.item;
            var tag = $(ui.item).data('mergeTag');
            var list_id = $(ui.item).data('listId');

            $('#' + list_id + ' .imc_sync_count').html('').css('opacity', 0);

            $(merge_tag_box).addClass('mergetag-syncing');

            $.post(
                    ajaxurl,
                    {'merge_tag': tag,
                        'list_id': list_id,
                        'action': 'list_sync_merge_tag'},
            //- On Success
            function (resp) {
                var message_class = 'updated';
                if (resp instanceof Array || resp instanceof Object) {
                    for (var key in resp) {
                        var this_message = '';
                        if (key == 'msg') {
                            this_message = resp[key];
                            // stop activity spinner
                            $(merge_tag_box).removeClass('mergetag-syncing').toggleClass('active').toggleClass('inactive').addClass('exists_in_mailchimp');
                        } else {
                            this_message = resp[key] ? resp[key] : resp ? resp : 'An Error Occurred';
                            message_class = 'error';
                            $('.imc-tag-group').sortable('cancel');
                        }

                        //$('#message').append('<li>' + this_message + '</li>');
                        if (this_message) {
                            $('#' + list_id + ' .imc_sync_count').append(this_message);
                        }
                    }
                }

                //$('#message').css('opacity', 0)
                $('#'+ list_id +' .imc_sync_count').css('opacity', 0)
                        .removeClass('error')
                        .removeClass('updated')
                        //.addClass(message_class)
                        .css('opacity', 1)
                        .show('slow');

                //$('.imc-tag-group').sortable('enable');
                $('.imc-tag-group').removeClass('disable-sort');

            });

        }
    }).disableSelection();

});