<?php
// sign up mailchimp shortcode
// 
// To display content on sign up page, add [rgmcsignup]
// 
function rg_signup_form() {
	// get genres (interest categories)
	$url = 'http://us11.api.mailchimp.com/3.0/lists/' . LIST_ID . '/interest-categories/' . INTEREST_TYPE . '/interests?apikey=' . API_KEY . '&count=100&output=json';
	$response = \Httpful\Request::get($url)->send();
	$genre_options = '';

	foreach ($response->body->interests as $genre) {
		$genre_options .= '<option value="' . $genre->id . ':' . $genre->name . '">' . $genre->name . '</option>';
	}

    echo '<form id="rgsignupform" action="' . ALMOST_DONE_LOC . '" method="post">';
    echo '<p>';
	echo '<select id="lp-genre" value="' . ( isset( $_POST["lp-genre"] ) ? esc_attr( $_POST["lp-genre"] ) : '' ) . '" name="lp-genre">';
	echo '	<option value="">Pick Your Genre</option>';
	echo 	$genre_options;
	echo '	</option>';
	echo '</select>';
    echo '</p>';
    echo '<p>';
    echo '<input type="email" id="lp-email" name="lp-email" value="' . ( isset( $_POST["lp-email"] ) ? esc_attr( $_POST["lp-email"] ) : '' ) . '" placeholder="Enter your email address" />';
    echo '</p>';
    // pass through source if available
    echo '<input type="hidden" name="lp-source" value="' . ( isset( $_POST["lp-source"] ) ? esc_attr( $_POST["lp-source"] ) : 'rg-home' )  . '" />';
    echo '<p><input id="rgsignupbutton" type="submit" name="lp-submitted" value="Get Your Book"/></p>';
    echo '</form>';
}

function process_rg_signup() {

    // if the submit button is clicked, send the email
    if ( isset( $_POST['lp-submitted'] ) ) {

    	$emailmd5   = md5(sanitize_email( $_POST["lp-email"] ));
        // sanitize form values
        $email   = sanitize_email( $_POST["lp-email"] );
        $genre = sanitize_text_field( $_POST["lp-genre"] );
        $genre = explode(":", sanitize_text_field( $_POST["lp-genre"] ));
		$genre_code = $genre[0]; // category interest for the genre
		$genre_name = $genre[1]; // text name of tbe genre
        $source = sanitize_text_field( $_POST["lp-source"] );

		// get genres (interest categories)
		$url = 'http://us11.api.mailchimp.com/3.0/lists/' . LIST_ID . '/members/';

		$response = \Httpful\Request::post($url)        // Build a POST request...
		    ->sendsJson()                             	// tell it we're sending (Content-Type) JSON...
		    ->authenticateWith(API_KEY, API_KEY)  		// authenticate with basic auth...
		    ->body('{	"email_address": "' . $email . '", 
    					"status": "pending", 
    					"merge_fields": {
    						"SOURCE": "' . $source . '",
    						"FIRSTCAT": "' . $genre_name . '"
    					},
    					"interests": {
  						    "' . $genre_code . '": true
    					}
					}')             					// attach a body/payload...
		    ->send();                      				// and finally, fire that thing off!
		// TODO: Handle Already on list
		if ($response->body->status == "400") {
			 echo '<script type="text/javascript">';
    		echo 'window.location.href = "' . ALREADY_IN_LOC . '"';;
			echo '</script>';
			//echo $response->body->status;
			//print_r($response);
		}

    }
}

function rg_mailchimp_sign_up() {
    ob_start();
    process_rg_signup();
    rg_signup_form();
    return ob_get_clean();
}

add_shortcode( 'rgmcsignup' , 'rg_mailchimp_sign_up' );

// thank you mailchimp shortcode
// 
// To display content on Almost Done page, add [rgmcthankyou]
// 
function rg_mailchimp_genres_form() {

    if ( isset( $_POST['lp-submitted'] ) ) {
    	$emailmd5 = md5( strtolower( sanitize_email( $_POST["lp-email"] )));
    	$email  = strtolower( sanitize_email( $_POST["lp-email"] ));
   	}
	$url = 'http://us11.api.mailchimp.com/3.0/lists/' . LIST_ID . '/interest-categories/' . INTEREST_TYPE . '/interests?apikey=' . API_KEY . '&count=100&output=json';
	$response = \Httpful\Request::get($url)->send();

	$output = '<form method="post">
        <fieldset>';

	foreach ($response->body->interests as $genre) {
		$output .= '<div class="genreinput"><input type="checkbox" id="'. $genre->id .'" name="lp-genres[]" value="' . $genre->id . '" checked /> <label for="'. $genre->id .'">' . $genre->name . '</label></div>';
	}
	$output .= '<input type="hidden" name="lp-email" value="' . $email . '" />';
	$output .= '<div class="genresubmit"><input type="submit" name="lp-genres-submitted" value="Send"/></div>
        </fieldset></form>';
	
	$html .= $output;

    echo $html;
}

function process_rg_genres() {

   // if the submit button is clicked, send the email
    if ( isset( $_POST['lp-genres-submitted'] ) ) {
    	$email = $_POST["lp-email"];
    	$mailmd5 = md5($email);
        $genres = $_POST["lp-genres"];
        $first = true;
		foreach ($genres as $genre) {
			if ( $first ) {
				// no comma
				$first = false;
			} else {
				$genre_list .= ',';
			}

			$genre_list .= '"' . $genre . '":true';
		}
		
  		$url = 'http://us11.api.mailchimp.com/3.0/lists/' . LIST_ID . '/members/' . $mailmd5;
		$response = \Httpful\Request::patch($url)        // Build a POST request to update existing user
		    ->sendsJson()                             	// tell it we're sending (Content-Type) JSON...
		    ->authenticateWith(API_KEY, API_KEY)  // authenticate with basic auth...
		    ->body('{	
		    			"email_address": "'. $email . '",
    					"interests":{' . $genre_list . '}
    				}')             					// attach a body/payload...
		    ->send();                      				// and finally, fire that thing off!
 
 		if ($response->body->status == "404") {
 			echo  '<div class="genreerror"><strong>** ' . $response->body->status . ' ** Hmmm, something Strange happened. We could not locate this email in the system. Please contact books@runawaygoodness.com and we will be glad to help! **</strong></div>';
	    	rg_mailchimp_genres_form();
 		} else {
 			echo '<script type="text/javascript">';
    		echo 'window.location.href = "' . THANK_YOU_LOC . '"';
			echo '</script>';
 		}

    } else {
    	rg_mailchimp_genres_form();
    }
}

function rg_mailchimp_genres() {
    ob_start();
    process_rg_signup();
    process_rg_genres();
    return ob_get_clean();
}

add_shortcode( 'rgmcthankyou' , 'rg_mailchimp_genres' );