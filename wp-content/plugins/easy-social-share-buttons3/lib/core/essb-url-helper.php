<?php
class ESSBUrlHelper {
	
	public static function get_current_url($mode = 'base') {
	
		$url = 'http' . (is_ssl () ? 's' : '') . '://' . $_SERVER ['HTTP_HOST'] . $_SERVER ['REQUEST_URI'];
	
		switch ($mode) {
			case 'raw' :
				return $url;
				break;
			case 'base' :
				return reset ( explode ( '?', $url ) );
				break;
			case 'uri' :
				$exp = explode ( '?', $url );
				return trim ( str_replace ( home_url (), '', reset ( $exp ) ), '/' );
				break;
			default :
				return false;
		}
	}
	
	public static function get_current_page_url() {
		$pageURL = 'http';
		if(isset($_SERVER["HTTPS"]))
			if ($_SERVER["HTTPS"] == "on") {
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	
	public static function short_googl($url, $post_id = '', $deactivate_cache = false, $api_key = '') {
		if (!empty($post_id) && !$deactivate_cache) {
			$exist_shorturl = get_post_meta($post_id, 'essb_shorturl_googl', true);
			
			if (!empty($exist_shorturl)) {
				return $exist_shorturl;
			}
		}
		
		//$encoded_url = urlencode($url);
		$encoded_url = $url;
		if (!empty($api_key)) {
			$result = wp_remote_post ( 'https://www.googleapis.com/urlshortener/v1/url?key='.($api_key), array ('body' => json_encode ( array ('longUrl' => esc_url_raw ( $encoded_url ) ) ), 'headers' => array ('Content-Type' => 'application/json' ) ) );
		}
		else {
			$result = wp_remote_post ( 'https://www.googleapis.com/urlshortener/v1/url', array ('body' => json_encode ( array ('longUrl' => esc_url_raw ( $encoded_url ) ) ), 'headers' => array ('Content-Type' => 'application/json' ) ) );
		}
				
		// Return the URL if the request got an error.
		if (is_wp_error ( $result ))
			return $url;
	
		$result = json_decode ( $result ['body'] );
		$shortlink = $result->id;
		if ($shortlink) {
			if ($post_id != '') {
				update_post_meta ( $post_id, 'essb_shorturl_googl', $shortlink );
	
			}
	
			return $shortlink;
		}
	
		return $url;
	}
	
	public static function short_bitly($url, $user = '', $api = '', $post_id = '', $deactivate_cache = false) {
		if (!empty($post_id) && !$deactivate_cache) {
			$exist_shorturl = get_post_meta($post_id, 'essb_shorturl_bitly', true);
				
			if (!empty($exist_shorturl)) {
				return $exist_shorturl;
			}
		}
		
		//$encoded_url = urlencode($url);
		$encoded_url = ($url);
		
		$params = http_build_query(
				array(
						'login' => $user,
						'apiKey' => $api,
						'longUrl' => $encoded_url,
						'format' => 'json',
				)
		);
	
		/*if ($jmp == 'true') {
			$params['domain'] = "j.mp";
		}*/
			
		$result = $url;
	
		$rest_url = 'https://api-ssl.bitly.com/v3/shorten?' . $params;
	
		$response = wp_remote_get( $rest_url );
		//print_r($response);
		// if we get a valid response, save the url as meta data for this post
		if( !is_wp_error( $response ) ) {
	
			$json = json_decode( wp_remote_retrieve_body( $response ) );
	
			if( isset( $json->data->url ) ) {
	
				$result = $json->data->url;
				update_post_meta ( $post_id, 'essb_shorturl_bitly', $result );
			}
		}
	
		return $result;
	}
	
	public static function short_ssu($url, $post_id, $deactivate_cache = false) {
		$result = $url;
		
		if (!empty($post_id) && !$deactivate_cache) {
			$exist_shorturl = get_post_meta($post_id, 'essb_shorturl_ssu', true);
		
			if (!empty($exist_shorturl)) {
				return $exist_shorturl;
			}
		}
		
		if (defined('ESSB3_SSU_VERSION')) {
			if (class_exists('ESSBSelfShortUrlHelper')) {
				$short_url = ESSBSelfShortUrlHelper::get_external_short_url ( $url );
				
				if (!empty($short_url)) {
					$result = ESSBSelfShortUrlHelper::get_base_path () . $short_url;
					update_post_meta ( $post_id, 'essb_shorturl_ssu', $result );
				}
			}
		}
		
		return $result;
	}
	
	public static function short_url($url, $provider, $post_id = '', $bitly_user = '', $bitly_api = '') {
		global $essb_options;
				
		$deactivate_cache = ESSBOptionValuesHelper::options_bool_value($essb_options, 'deactivate_shorturl_cache');
		$shorturl_googlapi = ESSBOptionValuesHelper::options_value($essb_options, 'shorturl_googlapi');
		
		$short_url = "";
		
		if ($provider == "ssu") {
			if (!defined('ESSB3_SSU_VERSION')) {
				$provider = "wp";
			}
		}
		
		switch ($provider) {
			case "wp" :
				$short_url = wp_get_shortlink();
				break;			
			case "goo.gl" :
				$short_url = self::short_googl($url, $post_id, $deactivate_cache, $shorturl_googlapi);
				break;
			case "bit.ly" :
				$short_url = self::short_bitly($url, $bitly_user, $bitly_api, $post_id, $deactivate_cache);
				break;
			case "ssu":
				$short_url = self::short_ssu($url, $post_id, $deactivate_cache);
				break;
		}
		
		return $short_url;
	}
 	
	public static function attach_tracking_code($url, $code = '') {
		$posParamSymbol = strpos($url, '?');
		
		$code = str_replace('&', '%26', $code);
	
		if ($posParamSymbol === false) {
			$url .= '?';
		}
		else {
			$url .= "%26";
		}
	
		$url .= $code;
			
		return $url;
	}

	public static function esc_tracking_url($url) {
		$url = str_replace('&', '%26', $url);
		//$url = str_replace('?', '%3F', $url);
		
		return $url;
	}
	
	public static function generate_affiliatewp_referral_link ($permalink) {
		if ( ! ( is_user_logged_in() && affwp_is_affiliate() ) ) {
			return $permalink;
		}
		// append referral variable and affiliate ID to sharing links in Jetpack
		$permalink = add_query_arg( affiliate_wp()->tracking->get_referral_var(), affwp_get_affiliate_id(), $permalink );
		return $permalink;
	}
} 

?>