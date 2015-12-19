<?php

/**
 * MailChimp Base URL for API
 */
define( 'RG_MC_BASE_URL', 'http://us11.api.mailchimp.com/3.0/lists/' );
// sign up mailchimp shortcode
// 
// To display content on sign up page, add [rgmcsignup]
// 
function rg_signup_form( $hidegenre = false, $button_text ) {
	if( isset( $_GET["ref"] ) ) {
		$_POST["lp-source"] = esc_attr( $_GET["ref"] );
	}

	// Grab the Interest Categories from MC
	$interest_categories = rg_get_interest_categories();

	$genre_options = '';
	$showhiddenc = '';

	foreach ($interest_categories as $genre) {
		if( isset( $_GET["c"] ) && ( $_GET["c"] == $genre->id ) ) {
			$preselect = "selected";
			$showhiddenc = '<input type="hidden" name="lp-genre" value="' . $genre->id . ':' . $genre->name . '">';
		} else {
			$preselect = '';
		}

		$genre_options .= '<option value="' . $genre->id . ':' . $genre->name . '" '. $preselect .'>' . $genre->name . '</option>';
	}

   	echo '<form id="rgsignupform" action="/' . get_page_uri( get_option( 'almost_done_page' ) ) . '/" method="post">';

   	if( !isset( $hidegenre ) || $hidegenre != true ) {
	   	echo '<p>';
		echo '<select id="lp-genre" value="' . ( isset( $_POST["lp-genre"] ) ? esc_attr( $_POST["lp-genre"] ) : '' ) . '" name="lp-genre">';
		echo 	$genre_options;
		echo '	</option>';
		echo '</select>';
	   	echo '</p>';
	} else {
		echo $showhiddenc;
	}

   	echo '<p>';
   	echo '<input type="email" id="lp-email" name="lp-email" value="' . ( isset( $_POST["lp-email"] ) ? esc_attr( $_POST["lp-email"] ) : '' ) . '" placeholder="Enter your email address" />';
   	echo '</p>';
    // pass through source if available
    
    if( isset( $_POST["lp-source"] ) ) {
    	$new_source = $_POST["lp-source"];
		$remove_chars = array( ',', '@', '.', ' ', '$' );
		foreach( $remove_chars as $k ) {
			$new_source = str_replace( $k, "_", $new_source );
		}

    	$source_id = esc_attr( $new_source );
    } elseif ( isset( $_COOKIE["rgref"] ) ) {
    	$source_id = $_COOKIE["rgref"];
    } else {
    	$source_id = 'rg-home';
    }

    if( isset( $_GET["affid"] ) ) {
    	$aff_id = sanitize_text_field( $_GET["affid"] );
    } elseif ( isset( $_COOKIE["affid"] ) ) {
    	$aff_id = $_COOKIE["affid"];
    } else {
    	$aff_id = '';
    }

// https://runawaygoodness.com/?ref=sas&affid=143450
   	echo '<input type="hidden" name="lp-source" value="' . $source_id . '" />';
   	echo '<input type="hidden" name="AFFID" value="' . $aff_id . '" />';
   	echo '<p class="subformwrapper"><input id="rgsignupbutton" type="submit" name="lp-submitted" value="'. $button_text .'"/></p>';
   	echo '</form>';
}

function process_rg_signup() {

    // if the submit button is clicked, send the email
    if ( isset( $_POST['lp-submitted'] ) ) {

        // sanitize form values
        $email   = sanitize_email( $_POST["lp-email"] );
        $aff_id = sanitize_text_field( $_POST["AFFID"] );
        $genre = explode(":", sanitize_text_field( $_POST["lp-genre"] ));
		$genre_code = $genre[0]; // category interest for the genre
		$genre_name = $genre[1]; // text name of tbe genre
        $source = sanitize_text_field( $_POST["lp-source"] );

		// get genres (interest categories)
		$url = RG_MC_BASE_URL . LIST_ID . '/members/';

		if( isset( $_POST["lp-genre"] ) ) {

			// I prefer this approach because it's easier to work with
			// and you don't have to worry about manually building it as JSON
			$request = new stdClass();
			$request->email_address = $email;
			$request->status = 'subscribed';
			$request->merge_fields = [
				'SOURCE' => $source,
				'FIRSTCAT' => $genre_name,
				'AFFID' => $aff_id
			];
			$request->interests = [
				$genre_code => true
			];

			// has genres
			$response = \Httpful\Request::post($url)		// Build a POST request...
			    ->sendsJson()								// tell it we're sending (Content-Type) JSON...
			    ->authenticateWith(API_KEY, API_KEY)		// authenticate with basic auth...
				->body(json_encode($request))
			    ->send();                      				// and finally, fire that thing off!
			    add_post_meta( 1, 'rgmcresponse', $response, false );
		} else {
			// no genres
			switch( $_COOKIE["rgfirstcat"] ) {
				case "8535224d0a";
					$first_cat = "Creative Writing";
				break;

				case "48bfe6da8a";
					$first_cat = "Business";
				break;

				case "1526319864";
					$first_cat = "Horror";
				break;

				case "044e00b73e";
					$first_cat = "WCF";
				break;

				case "c0f401a02f";
					$first_cat = "Romantic Suspense";
				break;

				case "25862f090e";
					$first_cat = "Literary Fiction";
				break;

				case "f870639656";
					$first_cat = "Self Help";
				break;

				case "6c0e8d58fd";
					$first_cat = "Thriller";
				break;

				case "d7adefae40";
					$first_cat = "Romance";
				break;

				case "a8f230c4c1";
					$first_cat = "Science Fiction";
				break;

				case "1b4528eaf1";
					$first_cat = "Paranormal";
				break;

				case "cdc5a72605";
					$first_cat = "Memoir";
				break;

				case "7a463d765b";
					$first_cat = "Historical Romance";
				break;

				case "715b34ff18";
					$first_cat = "New Adult";
				break;

				case "71fff6c086";
					$first_cat = "Chick Lit";
				break;

				case "08d41db534";
					$first_cat = "Mid-grade";
				break;

				case "5ef2b04121";
					$first_cat = "Fantasy";
				break;

				case "2f5b7db69f";
					$first_cat = "Mystery";
				break;

				case "c3103e3a92";
					$first_cat = "LGBT";
				break;

				case "214df279b6";
					$first_cat = "Young Adult";
				break;

				case "7e5b03badc";
					$first_cat = "Christian";
				break;

				case "b5c00b76cf";
					$first_cat = "Humor";
				break;

				default:
					$first_cat = "None";
				break;

			}

			$request = new stdClass();
			$request->email_address = $email;
			$request->status = 'subscribed';
			$request->merge_fields = [
					'SOURCE' => $source,
					'FIRSTCAT' => $first_cat,
					'AFFID' => $aff_id
			];

			$response = \Httpful\Request::post($url)		// Build a POST request...
			    ->sendsJson()								// tell it we're sending (Content-Type) JSON...
			    ->authenticateWith(API_KEY, API_KEY)		// authenticate with basic auth...
			    ->body(json_encode($request))             					// attach a body/payload...
			    ->send();                      				// and finally, fire that thing off!
			    add_post_meta( 1, 'rgmcresponse', $response, false );

		}

		// TODO: Handle Already on list
		/*
		ANDY - Need Your Help Here!
		
		if ($response->body->status == "400") {
			 echo '<script type="text/javascript">';
    		echo 'window.location.href = "' . get_site_url() . "/" . get_page_uri( get_option( 'already_in_page' ) ) . "/?e=" . $email .'&l=216"';;
			echo '</script>';
			//echo $response->body->status;
			//print_r($response);
		}
		*/

    }
}

function rg_mailchimp_sign_up( $atts ) {
	$atts = shortcode_atts( array(
		'hidegenre' => false,
		'buttontext' => 'Get Your Free Book',
	), $atts, 'rgmcsignup' );

    ob_start();
    process_rg_signup();
    rg_signup_form( $atts['hidegenre'], $atts['buttontext'] );
    return ob_get_clean();
}

add_shortcode( 'rgmcsignup' , 'rg_mailchimp_sign_up' );

// thank you mailchimp shortcode
// 
// To display content on Almost Done page, add [rgmcthankyou]
// 
function rg_mailchimp_genres_form() {

	$html = '';

    if ( isset( $_POST['lp-submitted'] ) ) {
    	$emailmd5 = md5( strtolower( sanitize_email( $_POST["lp-email"] )));
    	$email  = strtolower( sanitize_email( $_POST["lp-email"] ));
   	}

   	if( isset( $_GET['e'] ) ) {
   		$eplus = str_replace(' ', '+', $_GET["e"] );
    	$emailmd5 = md5( strtolower( sanitize_email( $eplus )));
    	$email  = strtolower( sanitize_email( $eplus ) );
   	}

   	if( isset( $_GET['affid'] ) ) {
   		$aff_id = sanitize_text_field( $_GET["affid"] );
   	} elseif( isset( $_COOKIE['affid'] ) ) {
   		$aff_id = sanitize_text_field( $_COOKIE["affid"] );
   	}

   	if( isset( $_POST["resetemail"] ) ) {
   		// send email with reset link
   		$reset_link = get_site_url() . '/' . get_page_uri( get_option( 'already_in_page' ) ) . '/?e='. esc_attr( $_POST["resetemail"] .'&l=258' );

   		$to = esc_attr( $_POST["resetemail"] );
   		$subject = 'Update Runaway Goodness subscription';
   		$message = 'To reset your Runaway Goodness subscription settings, use the link below.'."\r\n".$reset_link;
   		$headers = 'From: Runaway Goodness <books@runawaygoodness.com>' . "\r\n";

   		if( wp_mail( $to, $subject, $message, $headers ) ) {
   			$html = '<p>We have sent you an email with a link to update your subscription settings.</p>';
   		} else {
   			$html = '<p>There was a problem sending your email.';
   		}

   	} elseif( !isset( $email ) && !isset( $_POST["resetemail"] ) ) {
   		$html = '<p>Oops! You reached this page in a way we didn\'t expect. Enter your email address below and we will send you a link to confirm you are you.';
   		$html .= '<form method="post">';
   		$html .= '<input type="text" name="resetemail" placeholder="Enter your email address"><br />';
   		$html .= '<input type="submit" value="Submit">';
   		$html .= '</form>';
   	} else {

	    // grab member data
		$url2 = RG_MC_BASE_URL . LIST_ID . '/members/'. $emailmd5 .'?output=json';
		$response2 = \Httpful\Request::get($url2)
        ->authenticateWith(API_KEY, API_KEY)        // authenticate with basic auth...
        ->send();

		$interest_array = array();

	    // Oddly, we've received responses with no interests or body and PHP spits out a warning
	    if($response2->body && $response2->body->interests) {
		    foreach( $response2->body->interests as $k => $v ) {
			    if( $v == 1 ) {
				    $interest_array[] = $k;
			    }
		    }
	    }

		global $post;
		$slug = get_post( $post )->post_name;
	    $output = '';

		if ( isset($_GET['st'] ) && $_GET['st'] == 'up' ) {
			$output = "<p><strong>Your settings have been saved!</strong></p>"; 
		}

		$output .= '<form method="post">
	        <fieldset>';

	    // Grab the Interest Categories from MC
	    $interest_categories = rg_get_interest_categories();

	    foreach ($interest_categories as $genre) {
			if( in_array( $genre->id, $interest_array ) ) {
				$checkinterest = 'checked';
			} else {
				$checkinterest = '';
			}

			$output .= '<div class="genreinput"><input type="checkbox" id="'. $genre->id .'" name="lp-genres[]" value="' . $genre->id . '" '. $checkinterest .' /> <label for="'. $genre->id .'">' . $genre->name . '</label></div>';
		}
		wp_die(' found it !! ');
		$output .= '<input type="hidden" name="frompage" value="' . $slug .'" />';
		$output .= '<input type="hidden" name="lp-email" value="' . $email . '" />';
		$output .= '<input type="hidden" name="aff_id" value="' . $aff_id . '" />';

		$output .= '<div class="genresubmit"><input type="submit" name="lp-genres-submitted" value="Send"/></div>
	        </fieldset></form>';
		
		$html .= $output;
	}
	echo $html;
}

function process_rg_genres() {

	// grab entire list of interests
	$interest_categories = rg_get_interest_categories();
	$full_interest_list = array();

	foreach ($interest_categories as $interests) {
		$full_interest_list[] = $interests->id;
	}

	// if the submit button is clicked, send the email
	if ( isset( $_POST['lp-genres-submitted'] ) ) {
    	$email = $_POST["lp-email"];

		// If they didn't set any genres, we need to create an empty array
        $genres = (isset($_POST["lp-genres"])) ? $_POST["lp-genres"] : [];

	    // Build the update request
	    $request = new stdClass();
	    $request->email_address = $email;
	    $request->interests = [];

	    // Add their selected genres
	    foreach($genres as $genre) {
		    $request->interests[$genre] = true;
	    }

		// Add unchecked genres to the list
		$not_checked = array_diff( $full_interest_list, $genres );
		foreach ($not_checked as $nci ) {
			$request->interests[$nci] = false;
		}

  		$url = sprintf(RG_MC_BASE_URL . LIST_ID . '/members/%s', md5($email));
		$response = \Httpful\Request::patch($url)       // Build a POST request to update existing user
		    ->sendsJson()                             	// tell it we're sending (Content-Type) JSON...
		    ->authenticateWith(API_KEY, API_KEY)        // authenticate with basic auth...
		    ->body(json_encode($request))				// attach a body/payload...
		    ->send();                      				// and finally, fire that thing off!
 
 		if ($response->body->status == "404") {
		    error_log(sprintf('Unable to locate user %s in Mailchimp!', $email));
 			echo  '<div class="genreerror"><strong>** ' . $response->body->status . ' ** Hmmm, something Strange happened. We could not locate this email in the system. Please contact books@runawaygoodness.com and we will be glad to help! **</strong></div>';
	    	rg_mailchimp_genres_form();
 		} else {

 			// redirect based on where the visitor came from
 			if( $_POST["frompage"] == get_post( get_option( 'almost_done_page' ) )->post_name ) {
	 			echo '<script type="text/javascript">';
	    		echo 'window.location.href = "' . get_site_url() . '/' . get_page_uri( get_option( 'thank_you_page' ) ) . '/"';
				echo '</script>';
			} else {
	 			echo '<script type="text/javascript">';
	    		echo 'window.location.href = "' . get_site_url() . '/' . get_page_uri( get_option( 'already_in_page' ) ) . "/?e=" . $email . '&st=up&l=388"';
				echo '</script>';			
			}
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

/**
 * Function to grab the Interest Categories from MC, utilizing cache if possible
 */
function rg_get_interest_categories()
{
	if(! $interests = wp_cache_get('rg_interests', 'rg')) {

		$interest_url = RG_MC_BASE_URL . LIST_ID . '/interest-categories/' . INTEREST_TYPE . '/interests?count=100&output=json';
		$interest_response = \Httpful\Request::get($interest_url)
		                                     ->authenticateWith(API_KEY, API_KEY)        // authenticate with basic auth...
		                                     ->send();

		if(isset($interest_response->body) && isset($interest_response->body->interests) && is_array($interest_response->body->interests)) {
			$interests = $interest_response->body->interests;
			wp_cache_set('rg_interests', $interests, 'rg', 300);
		} else {
			// Something went very wrong here
			error_log('RG: Problem grabbing the Interest Categories from Mailchimp!');
			$interests = [];
		}
	}

	return $interests;
}

// used to ONLY process the signups
function rg_mailchimp_only_signup() {
    ob_start();
    process_rg_signup();
    return ob_get_clean();
}

add_shortcode( 'rgmsignuponly' , 'rg_mailchimp_only_signup' );


function rg_update_sub_link() {
	if( isset($_POST['lp-email'] ) ) {
		$link = get_site_url() . '/' . get_page_uri( get_option( 'update_sub_page' ) ) . "/?e=" . $_POST['lp-email'] .'&l=421';
	} else {
		$link = get_site_url() . '/' . get_page_uri( get_option( 'update_sub_page' ) ) . "/";
	}

	$html = '<a href="'.$link.'" class="button">Update Subscription</a>';

	return $html;
}
add_shortcode( 'rgupdatesublink', 'rg_update_sub_link' );