/**
 * Runaway Goodness 3rd Party Subscription Form
 *
 * https://teamtrope.atlassian.net/browse/TTR-151
 *
 * @author Brian Ronald <brian.ronald@booktrope.com>
 */
if(! RunawayGoodness) {
    var RunawayGoodness = {};
}

RunawayGoodness.SubscribeForm = function() {
    this.rg_block = null;
    this.form = null;
    this.genre_select = null;
    this.email_input = null;
    this.submit_button = null;

    this.submitted_form = false;
    this.attempts = 0;
};

/**
 * Builds our initial RG Sign-Up Form
 */
RunawayGoodness.SubscribeForm.buildForm = function() {

    // Some setup
    if(isNaN(this.attempts)) {
        this.attempts = 0;
    }

    this.submitted_form = false;
    this.rg_block = $('#rg-signupblock');

    if (typeof this.rg_block !== 'undefined') {

        // Add attributes to block:
        this.rg_block.addClass('rg-signupblock')
            .css('background', 'black')
            .css('color', 'white')
            .css('font-family', "'Lato', sans-serif")
            .css('font-size', '14pt')
            .css('text-align','center')
            .css('margin', '4px')
            .css('padding', '5px');

        var title_pargraph = document.createElement('p');
        $(title_pargraph).html("Sign up for Runaway Goodness to receive info on free and discounted books! It's 100% free!");
        this.rg_block.append(title_pargraph);

        this.form = document.createElement('form');
        this.form.setAttribute('id',"rg-signupform");
        this.form.setAttribute('name',"rg-signupform");
        this.form.setAttribute('class',"rg-signupform");

        if (typeof rg_showGenres !== 'undefined' && rg_showGenres == true) {
            // Here's where we generate the genre list
            // Ideally generate the JSON in PHP, store it somewhere in /tmp, and inject here
            var genres_json = '[{"name":"Romance","value":"d7adefae40"},{"name":"Women\'s Contemporary Fiction","value":"044e00b73e"},{"name":"Chick Lit","value":"71fff6c086"},{"name":"Young Adult","value":"214df279b6"},{"name":"New Adult","value":"715b34ff18"},{"name":"Romantic Suspense","value":"c0f401a02f"},{"name":"Horror","value":"1526319864"},{"name":"Literary Fiction","value":"25862f090e"},{"name":"Self Help","value":"f870639656"},{"name":"Thriller","value":"6c0e8d58fd"},{"name":"Humor","value":"b5c00b76cf"},{"name":"Christian","value":"7e5b03badc"},{"name":"Science Fiction","value":"a8f230c4c1"},{"name":"Historical Romance","value":"7a463d765b"},{"name":"Paranormal","value":"1b4528eaf1"},{"name":"Mid-grade","value":"08d41db534"},{"name":"Fantasy","value":"5ef2b04121"},{"name":"Memoir","value":"cdc5a72605"},{"name":"LGBT","value":"c3103e3a92"},{"name":"Mystery","value":"2f5b7db69f"},{"name":"Business","value":"48bfe6da8a"},{"name":"Creative Writing","value":"8535224d0a"}]';
            var genres = jQuery.parseJSON(genres_json);

            var genre_select_p = document.createElement('p');
            var genre_select = document.createElement('select');
            this.genre_select = genre_select;
            this.genre_select.setAttribute('id', 'rg-genre');
            this.genre_select.setAttribute('name', 'rg-genre');
            this.genre_select.setAttribute('class', 'rg-genre');

            $.each(genres, function (index, object) {
                var option = document.createElement('option');
                option.setAttribute('value', object.value + ":" + object.name);
                $(option).html(object.name);

                genre_select.appendChild(option);
            });
            genre_select_p.appendChild(this.genre_select);
            this.form.appendChild(genre_select_p);
        }

        var email_input_p = document.createElement('p');
        this.email_input = document.createElement('input');
        this.email_input.setAttribute('id', 'rg-email');
        this.email_input.setAttribute('name', 'rg-email');
        this.email_input.setAttribute('class', 'rg-email');
        this.email_input.setAttribute('placeholder', 'Enter your email address');
        $(this.email_input).css('width', '200px').css('padding', '2px');
        email_input_p.appendChild(this.email_input);

        var submit_button_p = document.createElement('p');
        this.submit_button = document.createElement('button');
        this.submit_button.setAttribute('id', 'rg-signupbutton');
        this.submit_button.setAttribute('name', 'rg-signupbutton');
        this.submit_button.setAttribute('class', 'rg-signupbutton');
        this.submit_button.setAttribute('type', 'button');
        $(this.submit_button).html('GET YOUR FREE BOOK');
        $(this.submit_button).css('background-color', '#aa0000')
            .css('color', 'white')
            .css('border', 'none')
            .css('border-radius', '3px')
            .css('padding', '7px');

        // Callback to handle the XHR post
        $(this.submit_button).click( function() {
            RunawayGoodness.SubscribeForm.subscribeCall();
        });

        submit_button_p.appendChild(this.submit_button);

        this.form.appendChild(email_input_p);
        this.form.appendChild(submit_button_p);

        this.rg_block.append(this.form);
    }
};

/**
 * Makes the call to the RG Rest Service to Subscribe
 */
RunawayGoodness.SubscribeForm.subscribeCall = function() {

    // Don't allow multiple submits
    if(this.submitted_form == true) {
        return;
    }

    // Set up variables here
    var email_address = $('#rg-email').val();

    // Very simple email address check
    if(typeof email_address == 'undefined'|| email_address == '' || (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email_address) == false)) {
        return;
    }

    var genre = '';
    if (typeof rg_showGenres !== 'undefined' && rg_showGenres == true) {
        genre = $('[name=rg-genre]').find('option:selected').val();
    }

    // Disable the inputs
    if(typeof this.genre_select !== 'undefined' && this.genre_select !== null) {
        $(this.genre_select).prop('disabled', true);
    }
    $(this.email_input).prop('disabled', true);

    var source = '';
    if (typeof rg_mySite !== 'undefined' && rg_mySite !== '') {
        source = rg_mySite;
    }

    // Build our input
    var data = JSON.stringify({
        email: email_address,
        genre: genre,
        source: source
    });

    // Show an In-Progress screen
    RunawayGoodness.SubscribeForm.showSubmissionInProgress();

    $.ajax({
        type: "POST",
        url: 'https://runawaygoodness.com/wp-json/rg/v1/subscribe/',
        dataType: 'json',
        contentType: "application/json",
        timeout: 30000, // 30 seconds should be sufficient
        data: data,
        success: function(response) {
            RunawayGoodness.SubscribeForm.handleSubmissionSuccess(response);
        },
        error: function( response ) {
            RunawayGoodness.SubscribeForm.handleSubmissionFailure(response);
        }
    });

};

/**
 * Resets our initial "block" and removes some pointers to form elements
 * which should no longer exist.
 */
RunawayGoodness.SubscribeForm.clearBlock = function() {

    // Clear out the inner-HTML contents
    $(this.rg_block).html('');

    // Reset our objects
    this.form = null;
    this.genre_select = null;
    this.email_input = null;
    this.submit_button = null;
};

/**
 * Busy screen shown after the user submits
 */
RunawayGoodness.SubscribeForm.showSubmissionInProgress = function() {
    RunawayGoodness.SubscribeForm.clearBlock();

    var feedback_paragraph = document.createElement('p');
    $(feedback_paragraph).html("<p>Signing you up!  Get ready for some great deals straight to your Inbox!</p>");

    var busy_image_container = $(document.createElement('div'))
        .css('text-align', 'center')
        .css('margin', '10px');

    var busy_image = document.createElement('img');
    $(busy_image).attr('src','https://runawaygoodness.com/wp-content/plugins/rg-mailchimp-connect/assets/rg-widget-busy.gif');

    busy_image_container.append(busy_image);

    this.rg_block.append(feedback_paragraph);
    this.rg_block.append(busy_image_container);

};

/**
 * Handles a response that appears successful from a HTTP request point of view
 *
 * @param response
 */
RunawayGoodness.SubscribeForm.handleSubmissionSuccess = function(response) {
    this.submitted_form = true;
    // Blank it out
    RunawayGoodness.SubscribeForm.clearBlock();

    // Check for errors
    if((response.code && response.result_code < 100 ) || (response.errors && response.errors == true)) {

        // Generic error handling
        RunawayGoodness.SubscribeForm.handleSubmissionFailure(response);

    } else {

        var feedback_div = $(document.createElement('div'))
            .css('font-size', '10pt')
            .css('text-align', 'left')
            .css('margin', '4px')
            .css('padding', '5px');

        var paragraph_one = document.createElement('p');
        $(paragraph_one).html(
            "Thank you for signing up for the Runaway Goodness newsletter."
        );

        var paragraph_two = $(paragraph_one).clone();
        $(paragraph_two).html(
            "Since you don’t want to miss out on the free and discounted books, now would be an " +
            "awesome time to add books@runawaygoodness.com to your email address book."
        );

        var already_exists_paragraph = $(paragraph_one).clone();
        $(already_exists_paragraph).html(
            "It looks like you're already subscribed with this address!"
        );

        var update_paragraph = $(paragraph_one).clone();
        $(update_paragraph).html(
            'You can <a href="' + response.update_url + '">update your subscription</a> by going ' +
            '<a href="' + response.update_url + '">here.</a>'
        );

        /**
         * Build the response here based on whether the subscription is new or existing
         */

        // Handle existing customers
        if (response.result_code && response.result_code == 110) {
            $(feedback_div).append(already_exists_paragraph);
        } else if (response.result_code && response.result_code == 100) {
            // Successful sign-up
            $(feedback_div).append(paragraph_one);
            $(feedback_div).append(paragraph_two);
        }

        // Everyone gets the subscription update paragraph
        $(feedback_div).append(update_paragraph);

        // Add to the RG widget block
        this.rg_block.append(feedback_div);
    }
};

/**
 * Handles submission errors, in both cases where the server responded properly but with an error
 * or if there's an error contacting the server.
 *
 * @param response
 */
RunawayGoodness.SubscribeForm.handleSubmissionFailure = function(response) {
    this.attempts += 1;

    // Blank out the form...
    RunawayGoodness.SubscribeForm.clearBlock();

    // Log the error
    if(response.message) {
        console.log(response.message);
    }

    var feedback_paragraph = document.createElement('p');

    // Max attempts before we direct them to the site
    if(this.attempts <= 2) {
        $(feedback_paragraph).html("Uh-oh, something went wrong!  Do you want to try again?")
            .css('font-size', '10pt');
        this.rg_block.append(feedback_paragraph);

        // Add a button
        var retry_button = document.createElement('button');
        retry_button.setAttribute('id', 'rg-resubmit');
        retry_button.setAttribute('name', 'rg-resubmit');
        retry_button.setAttribute('class', 'rg-resubmit');
        retry_button.setAttribute('type', 'button');
        $(retry_button).html('Try Again!');
        $(retry_button).css('background-color', '#aa0000')
            .css('color', 'white')
            .css('border', 'none')
            .css('border-radius', '3px')
            .css('padding', '7px');

        // Bind an action to rebuild the form to the button
        $(retry_button).click( function() {
            // Blank out the form...
            RunawayGoodness.SubscribeForm.clearBlock();
            RunawayGoodness.SubscribeForm.buildForm();
        });

        this.rg_block.append(retry_button);
    } else {
        $(feedback_paragraph)
            .html("We're not having much luck.  Try subscribing directly from <a href=\"https://runawaygoodness.com/\">runawaygoodness.com</a>.")
            .css('font-size', '10pt');
        this.rg_block.append(feedback_paragraph);
    }
};

$(document).ready(function() {
    RunawayGoodness.SubscribeForm.buildForm();

    // Disable the automatic "submission" if a user hits enter
    // on the email input box.
    $(window).keydown(function(event){
        if(event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
});
