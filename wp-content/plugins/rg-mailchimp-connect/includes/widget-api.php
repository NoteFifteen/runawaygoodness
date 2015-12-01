<?php

/**
 * RG Subscribe REST API for RG Widget for use on 3rd Party Sites
 *
 * https://teamtrope.atlassian.net/browse/TTR-151
 *
 * @author Brian Ronald <brian.ronald@booktrope.com>
 */
class RG_Subscribe_API
{
	/**
	 * Result codes to return to the caller
	 */
	const RESULT_FAILURE             =   0;
	const RESULT_FAILURE_BAD_ADDRESS =  10;
	const RESULT_NEW_SUBSCRIBER      = 100;
	const RESULT_EXISTING_SUBSCRIBER = 110;

	public function __construct() {
		// Determine if WP Rest API is loaded
		if(function_exists('register_rest_route')) {

			add_action( 'send_headers', array($this, 'rg_add_cors_header') );

			/**
			 * Add an endpoint for the RG Widget to POST subscriptions
			 */
			add_action( 'rest_api_init', function() {
				register_rest_route('rg/v1', '/subscribe/', array(
						'methods'  => 'POST',
						'callback' => array($this, 'rg_subscribe'),
				));
			} );

		}
	}

	/**
	 * 	Override the header value to include Content-Type to allow JSON Post and from any source
	 * but only if it's for our RG subscribe URL
	 */
	public function rg_add_cors_header() {
		if( isset($_SERVER['REQUEST_URL']) && strcasecmp($_SERVER['REQUEST_URL'], '/wp-json/rg/v1/subscribe/') == 0) {
			header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
			header( 'Access-Control-Allow-Origin: *' );
		}
	}

	/**
	 * Handles subscriptions via a REST API call from 3rd Party Sources
	 *
	 * Bound to /rg_subscribe/v1/subscribe
	 *
	 * @param WP_REST_Request $request
	 * @return Array
	 */
	public function rg_subscribe( WP_REST_Request $request) {

		$params = $request->get_json_params();

		// Request Object to send to MailChimp
		$request = new stdClass();
		$request->status = 'subscribed';
		$request->merge_fields = [];

		// Must have an email to proceed.
		if((! isset($params['email']) || empty($params['email'])) || ! is_email( sanitize_email( $params['email'] ))) {
			return [
				'errors' => true,
				'result_code' => self::RESULT_FAILURE_BAD_ADDRESS,
				'message' => 'Invalid email address!'
			];
		} else {
			$request->email_address = sanitize_email( $params['email'] );
		}

		// We're allowing subscriptions without specifying a Genre
		if(isset($params['genre']) && ! empty($params['genre'])) {
			$genre = explode(":", sanitize_text_field( $params['genre'] ));
			$genre_code = $genre[0]; // category interest for the genre
			$genre_name = $genre[1]; // text name of tbe genre

			$request->merge_fields['FIRSTCAT'] = $genre_name;
			$request->interests = [
					$genre_code => true
			];
		}

		if(isset($params['source']) && ! empty($params['source'])) {
			// Generate a widget-{domain} source for MC
			$request->merge_fields['SOURCE'] = $this->createSourceField( sanitize_text_field( $params['source'] ) );
		}

		// URL to provide that will return them to the update_subscription page
		// e.g. http://runawaygoodness.com/update-subscription/?e=bob@yahoo.com
		$update_url = sprintf('%s/update-subscription/?e=%s', get_site_url(), $request->email_address);

		// Send the request
		$url = RG_MC_BASE_URL . LIST_ID . '/members/';
		$response = \Httpful\Request::post($url)		// Build a POST request...
		                            ->sendsJson()								// tell it we're sending (Content-Type) JSON...
		                            ->authenticateWith(API_KEY, API_KEY)		// authenticate with basic auth...
		                            ->body(json_encode($request))
		                            ->send();                      				// and finally, fire that thing off!

		// Log the raw response
		add_post_meta( 1, 'rgmcresponse', $response, false );

		if ($response->body->status == 'subscribed') {
			return [
					'errors'      => false,
					'result_code' => self::RESULT_NEW_SUBSCRIBER,
					'message'     => sprintf( 'Subscribed %s.', $params['email'] ),
					'update_url'  => $update_url
			];
		} else if($response->body->status == 400 && $response->body->title == 'Member Exists') {
			return [
					'errors'      => false,
					'result_code' => self::RESULT_EXISTING_SUBSCRIBER,
					'message'     => sprintf('Already subscribed with %s.', $params['email']),
					'update_url'  => $update_url
			];
		} else {
			return [
					'errors'      => true,
					'result_code' => self::RESULT_FAILURE,
					'message'     => 'An unknown error occurred!',
					'update_url'  => null
			];
		}
	}

	/**
	 * Make the source something like: widget-domain
	 *
	 * Example: widget-booktrope.com
	 *
	 * @param string $source_url
	 * @return string
	 */
	private function createSourceField($source_url)
	{
		// First, strip off the http/https URI scheme
		$strip_uri_scheme_regex = '/https?:\/\//';
		$source_url = preg_replace($strip_uri_scheme_regex, '', $source_url);

		// Strip off trailing slash, space, tab, etc.
		$source_url = rtrim($source_url, '/ \t\n\r\0\x0B');

		return sprintf('widget-%s', strtolower($source_url));
	}

}


function load_rg_widget() {
	// The constructor will add hooks
	new RG_Subscribe_API();
}

// Action hook to initialize our plugin
add_action( 'plugins_loaded', 'load_rg_widget' );