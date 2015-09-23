<?php

if (! class_exists ( 'OAuthServer' )) {
	require_once ESSB3_PLUGIN_ROOT . 'lib/modules/social-fans-counter/OAuth/OAuth.php';
}

if (! class_exists ( 'LinkedIn' )) {
	require_once ESSB3_PLUGIN_ROOT . 'lib/modules/social-fans-counter/linkedin/linkedin.php';
}

if (! class_exists ( 'TwitterOAuthn' )) {
	require_once ESSB3_PLUGIN_ROOT . 'lib/modules/social-fans-counter/twitter/twitteroauth.php';
}

if (! class_exists ( 'MCAPI' )) {
	require_once ESSB3_PLUGIN_ROOT . 'lib/modules/social-fans-counter/mailchimp/MCAPI.class.php';
}

if (! class_exists ( 'VineApp' )) {
	require_once ESSB3_PLUGIN_ROOT . 'lib/modules/social-fans-counter/vine/Vine.php';
}

if (! class_exists ( 'Tumblr' )) {
	require_once ESSB3_PLUGIN_ROOT . 'lib/modules/social-fans-counter/Tumblr/Tumblr.php';
}

class ESSBSocialFansCounterUpdater {
	public static function do_curl($url) {
		if (! extension_loaded ( 'curl' ))
			return;
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 2 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt ( $ch, CURLOPT_VERBOSE, true );
		$data = curl_exec ( $ch );
		curl_close ( $ch );
		return $data;
	}
	
	public static function love() {
		$result = 0;
		
		try {
			$args = array ('posts_per_page' => - 1, 'post_type' => 'any' );
			$posts = get_posts ( $args );
			if ($posts) {
				foreach ( $posts as $post ) {
					$love_count = get_post_meta ( $post->ID, '_essb_love', true );
					
					$love_count = intval ( $love_count );
					$result = $result + $love_count;
				}
			}
		
		} catch ( Exception $e ) {
			$result = 0;		
		}
		
		return $result;
	}
	
	public static function twitter() {
		
		$consumer_key = ESSBSocialFansCounterHelper::get_option ( 'twitter.consumer_key' );
		$consumer_secret = ESSBSocialFansCounterHelper::get_option ( 'twitter.consumer_secret' );
		$access_token = ESSBSocialFansCounterHelper::get_option ( 'twitter.access_token' );
		$access_token_secret = ESSBSocialFansCounterHelper::get_option ( 'twitter.access_token_secret' );
		$id = ESSBSocialFansCounterHelper::get_option ( 'twitter.id' );
		
		if (empty ( $consumer_key ) || empty ( $consumer_secret ) || empty ( $access_token ) || empty ( $access_token_secret ) || empty ( $id )) {
			return 0;
		}
		
		$api = new TwitterOAuth ( $consumer_key, $consumer_secret, $access_token, $access_token_secret );
		$response = $api->get ( 'users/lookup', array ('screen_name' => trim ( $id ) ) );
		
		if (isset ( $response->errors )) {
			return null;
		}
		
		if (isset ( $response [0] ) && is_array ( $response [0] )) {
			return $response [0] ['followers_count'];
		}
		
		if (isset ( $response [0]->followers_count )) {
			return $response [0]->followers_count;
		}
	}
	
	public static function facebook() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'facebook.id' );
		$access_token = ESSBSocialFansCounterHelper::get_option ( 'facebook.access_token' );
		$account_type = ESSBSocialFansCounterHelper::get_option ( 'facebook.account_type', 'page' );
		
		if (!empty($id) && empty($access_token)) {
			return self::facebook_no_token($id);
			//return 0;
		}
		else {
		if (($account_type == 'page' && empty ( $id )) || ($account_type == 'followers' && (empty ( $id ) || empty ( $access_token )))) {
			return 0;
		}
		
		if ($account_type == 'followers') {
			return self::facebook_followers ();
		} else {
			return self::facebook_page ();
		}
		}
	}
	
	private static function facebook_no_token($id) {
		
		$data = self::do_curl( 'http://graph.facebook.com/'.$id );
		$result = 0;
		if (! empty ( $data )) {
			$response = json_decode ( $data, true );
			if (isset ( $response ['likes'] )) {
				return $response ['likes'];
			}
		}
		
		return $result;
		
	}
	
	private static function facebook_page() {
		
		$request = wp_remote_get ( 'https://graph.facebook.com/v2.2/' . ESSBSocialFansCounterHelper::get_option ( 'facebook.id' ) . '?fields=likes&&access_token=' . ESSBSocialFansCounterHelper::get_option ( 'facebook.access_token' ) );
		
		if (false == $request) {
			return null;
		}
		
		$response = json_decode ( wp_remote_retrieve_body ( $request ), true );
		
		if (isset ( $response ['likes'] )) {
			return $response ['likes'];
		}
	}
	
	private static function facebook_followers() {
		
		$request = wp_remote_get ( 'https://graph.facebook.com/v2.0/me/subscribers?access_token=' . ESSBSocialFansCounterHelper::get_option ( 'facebook.access_token' ) );
		
		if (false == $request) {
			return null;
		}
		
		$response = json_decode ( wp_remote_retrieve_body ( $request ), true );
		
		if (isset ( $response ['summary'] )) {
			return $response ['summary'] ['total_count'];
		}
	}
	
	public static function googleplus() {
		$api_key = ESSBSocialFansCounterHelper::get_option ( 'google.api_key' );
		//print "google API key = ".$api_key;
		if (trim($api_key) == '') {
			return self::googleplus_noapi();
		}
		else {
			return self::googleplus_api();
		}
	} 

	public static function googleplus_api() {
		$id = ESSBSocialFansCounterHelper::get_option ( 'google.id' );
		if (empty ( $id )) {
			return 0;
		}
		$api_key = ESSBSocialFansCounterHelper::get_option ( 'google.api_key' );
		$value_type = ESSBSocialFansCounterHelper::get_option('google.value_type');
		
		$url = "https://www.googleapis.com/plus/v1/people/" . $id . "?key=" . $api_key;
		//print $url;
		$data = self::do_curl( $url );
		$circleCount = 0;
		$plusOneCount = 0;
		if (! empty ( $data )) {
			$jsonData = json_decode ( $data, true );
			if (! empty ( $jsonData ['plusOneCount'] )) {
				$count ['plusOneCount'] = $jsonData ['plusOneCount'];
				$plusOneCount = intval ( $jsonData ['plusOneCount'] );
			}
			if (! empty ( $jsonData ['circledByCount'] )) {
				$count ['circledByCount'] = $jsonData ['circledByCount'];
				$circleCount = intval ( $jsonData ['circledByCount'] );
			}
		
		}
		
		if ($value_type == "plusOneCount") {
			return $plusOneCount;
		} else if ($value_type == "circledByCount") {
			return $circleCount;
		} else {
			return ($circleCount + $plusOneCount);
		}
	}
	
	public static function googleplus_noapi() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'googe.id' );
		if (empty ( $id )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'https://plus.google.com/' . urlencode ( $id ) . '/posts?hl=en' );
		
		if (false == $request) {
			return null;
		}
		
		$response = @wp_remote_retrieve_body ( $request );
		
		preg_match ( '/<span class="BOfSxb">([0-9., ]+)<\/span>/s', $response, $matches );
		
		if (! is_array ( $matches )) {
			return 0;
		}
		return str_replace ( array ('.', ' ', ',' ), '', $matches [1] );
	}
	
	public static function pinterest() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'pinterest.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'https://www.pinterest.com/' . $id );
		
		if (false == $request) {
			return null;
		}
		
		@preg_match ( ' <meta property="pinterestapp:followers" name="pinterestapp:followers" content="(\d+)" data-app>', @wp_remote_retrieve_body ( $request ), $matches );
		
		if (count ( $matches > 0 ) && isset ( $matches [1] )) {
			return $matches [1];
		}
	}
	
	public static function linkedin () {
	
		$id = ESSBSocialFansCounterHelper::get_option( 'linkedin.id' );
		$account_type = ESSBSocialFansCounterHelper::get_option( 'linkedin.account_type' , 'company' );
		$token = ESSBSocialFansCounterHelper::get_option( 'linkedin.token' );
	
		if ( empty( $id ) || empty( $account_type ) || empty( $token ) ) {
			return 0;
		}
	
		$args = array(
				'headers' => array('Authorization' => sprintf( 'Bearer %s', $token ) )
		);
	
		if ( $account_type == 'company' ) {
	
			$result = 0;
			try {
				$html = self::essb_remote_get( $id , false);
				$doc = new DOMDocument();
				@$doc->loadHTML($html);
				$xpath = new DOMXPath($doc);
				$data = $xpath->evaluate('string(//p[@class="followers-count"])');
				$result = (int) preg_replace('/[^0-9.]+/', '', $data);
			
			} catch (Exception $e) {
				$result = 0;
			}
			
			return $result;
			//$response = wp_remote_get( sprintf( 'https://api.linkedin.com/v1/companies/%s/num-followers?format=json', $id ) , $args );
			//print_r($response);
			//if ( is_wp_error( $response ) || !$response ) return 0;
	
			//return intval( wp_remote_retrieve_body( $response ) );
	
		} elseif ( $account_type == 'profile' ) {
	
			$response = wp_remote_get( 'https://api.linkedin.com/v1/people/~:(num-connections)?format=json' , $args );
			
			if ( is_wp_error( $response ) ) return 0;
	
			$result = json_decode( wp_remote_retrieve_body( $response ) , true );
	
			if ( !$result || !isset( $result['numConnections'] ) ) return 0;
	
			return $result['numConnections'];
		} else {
			return 0;
		}
	
	}
	
	public static function linkedin_old() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'linkedin.id' );
		$account_type = ESSBSocialFansCounterHelper::get_option ( 'linkedin.account_type', 'company' );
		$app_key = ESSBSocialFansCounterHelper::get_option ( 'linkedin.app_key' );
		$app_secret = ESSBSocialFansCounterHelper::get_option ( 'linkedin.app_secret' );
		$oauth_token = ESSBSocialFansCounterHelper::get_option ( 'linkedin.oauth_token' );
		$oauth_token_secret = ESSBSocialFansCounterHelper::get_option ( 'linkedin.oauth_token_secret' );
		
		if (empty ( $id ) || empty ( $app_secret ) || empty ( $app_key ) || ($account_type == 'profile' && (empty ( $oauth_token ) || empty ( $oauth_token_secret )))) {
			return 0;
		}
		
		$opt = array ('appKey' => $app_key, 'appSecret' => $app_secret, 'callbackUrl' => '' );
		
		$api = new LinkedIn ( $opt );
		
		if ($account_type == 'company') {
			$response = $api->company ( trim ( 'universal-name=' . $id . ':(num-followers)' ) );
		} elseif ($account_type == 'group') {
			$response = $api->group ( $id, ':(num-members)' );
		} else {
			$api->setTokenAccess ( array ('oauth_token' => $oauth_token, 'oauth_token_secret' => $oauth_token_secret ) );
			$response = $api->statistics ( $id );
		}
		
		if (false == $response ['success']) {
			return false;
		}
		
		$xml = new SimpleXMLElement ( $response ['linkedin'] );
		$count = 0;
		
		if ($account_type == 'company') {
			
			if (isset ( $xml->{'num-followers'} )) {
				$count = current ( $xml->{'num-followers'} );
			}
		}
		
		if ($account_type == 'group') {
			
			if (isset ( $xml->{'num-members'} )) {
				
				$count = current ( $xml->{'num-members'} );
			}
		}
		
		if ($account_type == 'profile') {
			
			if (isset ( $xml->property )) {
				
				$count = ( string ) $xml->property [0];
			}
		}
		
		return $count;
	}
	
	public static function github() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'github.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'https://api.github.com/users/' . $id );
		
		if (false == $request) {
			return null;
		}
		
		$response = json_decode ( @wp_remote_retrieve_body ( $request ) );
		
		if (isset ( $response->followers )) {
			return $response->followers;
		}
	}
	
	public static function vimeo() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'vimeo.id' );
		$account_type = ESSBSocialFansCounterHelper::get_option ( 'vimeo.account_type', 'channel' );
		$access_token = ESSBSocialFansCounterHelper::get_option ( 'vimeo.access_token' );
		
		if (($account_type == 'channel' && empty ( $id )) || ($account_type == 'user' && (empty ( $id ) || empty ( $access_token )))) {
			return 0;
		}
		
		if ($account_type == 'user') {
			return self::vimeo_user ();
		} else {
			return self::vimeo_channel ();
		}
	}
	
	private static function vimeo_channel() {
		
		$request = wp_remote_get ( 'http://vimeo.com/api/v2/channel/' . ESSBSocialFansCounterHelper::get_option ( 'vimeo.id' ) . '/info.json' );
		
		if (false == $request) {
			return null;
		}
		
		$response = json_decode ( wp_remote_retrieve_body ( $request ), true );
		
		if (isset ( $response ['total_subscribers'] )) {
			return $response ['total_subscribers'];
		}
	}
	
	private static function vimeo_user() {
		
		$request = wp_remote_get ( 'https://api.vimeo.com/users/' . ESSBSocialFansCounterHelper::get_option ( 'vimeo.id' ) . '/followers?access_token=' . ESSBSocialFansCounterHelper::get_option ( 'vimeo.access_token' ) );
		
		if (false == $request) {
			return null;
		}
		
		$response = json_decode ( wp_remote_retrieve_body ( $request ), true );
		
		if (isset ( $response ['total'] )) {
			return $response ['total'];
		}
	}
	
	public static function dribbble() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'dribbble.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'http://api.dribbble.com/' . $id );
		
		if (false == $request) {
			return null;
		}
		
		$response = @json_decode ( @wp_remote_retrieve_body ( $request ) );
		
		if (isset ( $response->followers_count )) {
			return $response->followers_count;
		}
	}
	
	public static function envato() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'envato.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'http://marketplace.envato.com/api/edge/user:' . $id . '.json' );
		
		if (false == $request) {
			return null;
		}
		
		$response = @json_decode ( @wp_remote_retrieve_body ( $request ) );
		
		if (isset ( $response->user ) && isset ( $response->user->followers )) {
			return $response->user->followers;
		}
	}
	
	public static function soundcloud() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'soundcloud.id' );
		$api_key = ESSBSocialFansCounterHelper::get_option ( 'soundcloud.api_key' );
		
		if (empty ( $id ) || empty ( $api_key )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'http://api.soundcloud.com/users/' . $id . '.json?client_id=' . $api_key );
		
		if (false == $request) {
			return null;
		}
		
		$response = @json_decode ( @wp_remote_retrieve_body ( $request ) );
		
		if (isset ( $response->followers_count )) {
			return $response->followers_count;
		}
	}
	
	public static function behance() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'behance.id' );
		$api_key = ESSBSocialFansCounterHelper::get_option ( 'behance.api_key' );
		
		if (empty ( $id ) || empty ( $api_key )) {
			return 0;
		}
		
		$opts = array ('http' => array ('method' => "GET", 'header' => "Accept-language: en\r\n" . "Cookie: foo=bar\r\n" . "User-Agent:Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)\r\n" ) );
		
		$context = stream_context_create ( $opts );
		
		$request = @wp_remote_get ( 'http://www.behance.net/v2/users/' . $id . '/?api_key=' . $api_key );
		
		if (false == $request) {
			return null;
		}
		
		$response = json_decode ( @wp_remote_retrieve_body ( $request ) );
		
		if (isset ( $response->user ) && isset ( $response->user->stats ) && isset ( $response->user->stats->followers )) {
			return $response->user->stats->followers;
		}
	}
	
	public static function delicious() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'delicious.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'http://feeds.delicious.com/v2/json/userinfo/' . $id );
		
		if (false == $request) {
			return null;
		}
		
		$response = json_decode ( @wp_remote_retrieve_body ( $request ) );
		
		if (isset ( $response ['2'] ) && isset ( $response ['2']->n )) {
			return $response ['2']->n;
		}
	}
	
	public static function instgram() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'instgram.id' );
		$username = ESSBSocialFansCounterHelper::get_option ( 'instgram.username' );
		$api_key = ESSBSocialFansCounterHelper::get_option ( 'instgram.api_key' );
		
		if (empty ( $id ) || empty ( $username ) || empty ( $api_key )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'https://api.instagram.com/v1/users/' . $id . '?access_token=' . $api_key );
		
		if (false == $request) {
			return null;
		}
		
		$response = json_decode ( @wp_remote_retrieve_body ( $request ) );
		
		if (isset ( $response->data ) && isset ( $response->data->counts ) && isset ( $response->data->counts->followed_by )) {
			return $response->data->counts->followed_by;
		}
	}
	
	public static function youtube() {
		
		$api_key = ESSBSocialFansCounterHelper::get_option ( 'youtube.api_key' );
		
		if (empty($api_key)) {
			if (ESSBSocialFansCounterHelper::get_option ( 'youtube.account_type' ) == 'channel')
				return self::youtube_channel ();
		
			if (ESSBSocialFansCounterHelper::get_option ( 'youtube.account_type' ) == 'user')
				return self::youtube_user ();
		}
		else {
			if (ESSBSocialFansCounterHelper::get_option ( 'youtube.account_type' ) == 'channel')
				return self::youtube_api3_channel($api_key);
			
			if (ESSBSocialFansCounterHelper::get_option ( 'youtube.account_type' ) == 'user')
				return self::youtube_api3_user ($api_key);
				
		}
	}
	
	public static function youtube_api3_user($api_key = '') {
	
		$id = ESSBSocialFansCounterHelper::get_option ( 'youtube.id' );
	
		if (empty ( $id )) {
			return 0;
		}
			
		$request = self::do_curl ( 'https://www.googleapis.com/youtube/v3/channels?part=statistics&forUsername='.$id.'&key=' . $api_key );
	
		if (false == $request) {
			return null;
		}
	
		$response = @json_decode ( $request );
		if (isset ( $response->items ) && isset ( $response->items[0]->statistics )) {
			return intval ( $response->items[0]->statistics->subscriberCount );
		}
	}
	
	public static function youtube_api3_channel($api_key = '') {
	
	$id = ESSBSocialFansCounterHelper::get_option ( 'youtube.id' );
	
		if (empty ( $id )) {
			return 0;
		}
			
		$request = self::do_curl ( 'https://www.googleapis.com/youtube/v3/channels?part=statistics&id='.$id.'&key=' . $api_key );
	
		if (false == $request) {
			return null;
		}
	
		$response = @json_decode ( $request );
		if (isset ( $response->items ) && isset ( $response->items[0]->statistics )) {
			return intval ( $response->items[0]->statistics->subscriberCount );
		}
	}
	
	
	public static function youtube_user() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'youtube.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'http://gdata.youtube.com/feeds/api/users/' . $id );
		
		if (false == $request) {
			return null;
		}
		
		try {
			$response = str_replace ( 'yt:', 'yt', @wp_remote_retrieve_body ( $request ) );
			$response = new SimpleXMLElement ( $response );
			
			if (false == is_object ( $response )) {
				return null;
			}
			
			if (isset ( $response->ytstatistics ) && isset ( $response->ytstatistics ['subscriberCount'] )) {
				return intval ( $response->ytstatistics ['subscriberCount'] );
			}
		}
		catch (Exception $e) {
			return null;
		}
	}
	
	public static function youtube_channel() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'youtube.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'http://gdata.youtube.com/feeds/api/channels/' . $id . '?v=2' );
		
		if (false == $request) {
			return null;
		}
		
		try {
			$response = str_replace ( 'yt:', 'yt', @wp_remote_retrieve_body ( $request ) );
			$response = new SimpleXMLElement ( $response );
			
			if (false == is_object ( $response )) {
				return null;
			}
			
			if (isset ( $response->ytchannelStatistics ) && isset ( $response->ytchannelStatistics ['subscriberCount'] )) {
				return intval ( $response->ytchannelStatistics ['subscriberCount'] );
			}
		}
		catch (Exception $e) {
			return null;
		}
	}
	
	public static function foursquare() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'foursquare.id' );
		$api_key = ESSBSocialFansCounterHelper::get_option ( 'foursquare.api_key' );
		
		if (empty ( $id ) || empty ( $api_key )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'https://api.foursquare.com/v2/users/self?oauth_token=' . $api_key . '&v=' . date ( 'Ymd' ) );
		
		if (false == $request) {
			return null;
		}
		
		$response = @json_decode ( @wp_remote_retrieve_body ( $request ) );
		
		if (isset ( $response->response ) && isset ( $response->response->user ) && isset ( $response->response->user->friends->count )) {
			return $response->response->user->friends->count;
		}
	}
	
	public static function forrst() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'forrst.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'http://forrst.com/api/v2/users/info?username=' . $id );
		
		if (false == $request) {
			return null;
		}
		
		$response = @json_decode ( @wp_remote_retrieve_body ( $request ) );
		
		if (isset ( $response->resp ) && isset ( $response->resp->followers )) {
			return $response->resp->followers;
		}
	}
	
	public static function mailchimp() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'mailchimp.list_id' );
		$api_key = ESSBSocialFansCounterHelper::get_option ( 'mailchimp.api_key' );
		
		if (empty ( $id ) || empty ( $api_key )) {
			return 0;
		}
		
		$result = 0;
		try {
			$api = new MCAPI ( $api_key );
			$retval = $api->lists ();
			$result = 0;
			
			foreach ( $retval ['data'] as $list ) {
				if ($list ['id'] == $id) {
					$result = $list ['stats'] ['member_count'];
					break;
				}
			}
		} catch ( Exception $e ) {
			$result = 0;
		}
		
		return $result;
	}
	
	public static function vk () {
		$type = ESSBSocialFansCounterHelper::get_option ( 'vk.account_type' );
		
		if ($type == "community") {
			return self::vk_community();
		}
		else {
			return self::vk_profile();
		}
	}
	
	public static function vk_community() {
		$id = ESSBSocialFansCounterHelper::get_option ( 'vk.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$result = 0;
		try {
			$request = wp_remote_get ( "http://api.vk.com/method/groups.getById?gid=$id&fields=members_count" );
			
			if (! $request) {
				return 0;
			}
			
			$response = wp_remote_retrieve_body ( $request );
			
			$data = json_decode ( $response, true );
			$result = (int) $data['response'][0]['members_count'];
		} catch (Exception $e) {
			$result = 0;
		}
		
		return $result;
	}
	
	public static function vk_profile() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'vk.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$request = @wp_remote_post ( 'https://api.vk.com/method/users.getFollowers', array ('body' => array ('count' => '0', 'user_id' => $id ) ) );
		
		if (false == $request)
			return 0;
		
		$response = json_decode ( @wp_remote_retrieve_body ( $request ), true );
		
		if (isset ( $response ['response'] ) && isset ( $response ['response'] ['count'] )) {
			return $response ['response'] ['count'];
		}
	}
	
	
	public static function rss() {
		
		$account_type = ESSBSocialFansCounterHelper::get_option ( 'rss.account_type', 'manual' );
		$json_file = ESSBSocialFansCounterHelper::get_option ( 'rss.json_file' );
		$url = ESSBSocialFansCounterHelper::get_option ( 'rss.link' );
		$feedblitz = ESSBSocialFansCounterHelper::get_option ( 'rss.feedblitz' );
		
		if (($account_type == 'feedpress' && (empty ( $json_file ) || empty ( $url )))) {
			return 0;
		}
		
		if ($account_type == 'feedpress') {
			return self::rss_feedpress ();
		}
		
		if ($account_type == 'manual') {
			if (!empty($feedblitz)) {
				return self::rss_feedblitz();	
			}
			else {
				return self::rss_manual ();
			}
		}
	}
	
	private static function rss_feedpress() {
		
		$json_file = ESSBSocialFansCounterHelper::get_option ( 'rss.json_file' );
		
		$request = wp_remote_get ( $json_file );
		
		if (! $request) {
			return 0;
		}
		
		$response = wp_remote_retrieve_body ( $request );
		
		$response = json_decode ( $response, true );
		
		if (is_array ( $response ) && isset ( $response ['subscribers'] )) {
			return $response ['subscribers'];
		}
	}
	
	private static function rss_feedblitz() {
		$feedblitz = ESSBSocialFansCounterHelper::get_option ( 'rss.feedblitz' );
		
		$result = 0;
		try {
			$feedpress_url = esc_url ( $feedblitz );
			// print $feedpress_url;
			$request = wp_remote_retrieve_body ( wp_remote_get ( $feedpress_url, array ('timeout' => 18, 'sslverify' => false ) ) );
			// print "data = ".$data;
			$result = ( int ) $request;
		} catch ( Exception $e ) {
			$result = 0;
		}
		
		return $result;
	}
	
	private static function rss_manual() {
		return ESSBSocialFansCounterHelper::get_option ( 'rss.count' );
	}
	
	public static function vine() {
		
		$email = trim ( ESSBSocialFansCounterHelper::get_option ( 'vine.email' ) );
		$password = trim ( ESSBSocialFansCounterHelper::get_option ( 'vine.password' ) );
		
		if (empty ( $email ) || empty ( $password )) {
			return 0;
		}
		
		$v = new VineApp ( $email, $password );
		$user = $v->userinfo ();
		
		if (! $user) {
			return 0;
		}
		
		return $user ['data'] ['followerCount'];
	}
	
	public static function tumblr() {
		
		$api_key = trim ( ESSBSocialFansCounterHelper::get_option ( 'tumblr.api_key' ) );
		$api_secret = trim ( ESSBSocialFansCounterHelper::get_option ( 'tumblr.api_secret' ) );
		$access_token = trim ( ESSBSocialFansCounterHelper::get_option ( 'tumblr.access_token' ) );
		$access_token_secret = trim ( ESSBSocialFansCounterHelper::get_option ( 'tumblr.access_token_secret' ) );
		
		$basename = trim ( ESSBSocialFansCounterHelper::get_option ( 'tumblr.basename' ) );
		
		if (empty ( $api_key ) || empty ( $api_secret ) || empty ( $access_token ) || empty ( $access_token_secret ) || empty ( $basename )) {
			return 0;
		}
		
		$tumblr = new Tumblr ( $api_key, $api_secret, $access_token, $access_token_secret );
		$response = $tumblr->followers ( $basename );
		
		if (! $response || ! is_object ( $response )) {
			return 0;
		}
		
		if (isset ( $response->response ) && isset ( $response->response->total_users )) {
			return $response->response->total_users;
		}
	}
	
	public static function slideshare() {
		
		$username = trim ( ESSBSocialFansCounterHelper::get_option ( 'slideshare.username' ) );
		
		if (empty ( $username )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'http://www.slideshare.net/' . $username . '/followers' );
		
		if (! $request) {
			return 0;
		}
		
		$response = @wp_remote_retrieve_body ( $request );
		
		if (! $response) {
			return 0;
		}
		
		@preg_match ( '/([0-9]+)( Followers| Follower)/s', $response, $matches );
		
		if (is_array ( $matches ) && isset ( $matches [1] )) {
			return $matches [1];
		}
	}
	
	public static function c500Px() {
		
		$api_key = trim ( ESSBSocialFansCounterHelper::get_option ( '500px.api_key' ) );
		$api_secret = trim ( ESSBSocialFansCounterHelper::get_option ( '500px.api_secret' ) );
		$username = trim ( ESSBSocialFansCounterHelper::get_option ( '500px.username' ) );
		
		if (empty ( $api_key ) || empty ( $api_secret ) || empty ( $username )) {
			return 0;
		}
		
		$request = @wp_remote_get ( 'https://api.500px.com/v1/users/search?term=' . $username . '&consumer_key=' . $api_key );
		
		if (false == $request) {
			return 0;
		}
		
		$response = json_decode ( @wp_remote_retrieve_body ( $request ), true );
		
		if (! is_array ( $response ) || ! isset ( $response ['total_items'] ) || $response ['total_items'] == 0) {
			return 0;
		}
		
		foreach ( $response ['users'] as $user ) {
			if ($user ['username'] == $username) {
				return $user ['followers_count'];
			}
		}
	}
	
	public static function flickr() {
		return ESSBSocialFansCounterHelper::get_option ( 'flickr.count' );
	}
	
	public static function wpposts() {
		
		return wp_count_posts ()->publish;
	}
	
	public static function wpcomments() {
		
		return wp_count_comments ()->approved;
	}
	
	public static function wpusers() {
		$result = count_users ();
		return $result ['total_users'];
	}
	
	public static function audioboo() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'audioboo.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$request = wp_remote_get ( 'http://api.audioboo.fm/users/' . $id . '/followers' );
		
		if (false == $request) {
			return 0;
		}
		
		$response = json_decode ( wp_remote_retrieve_body ( $request ), true );
		
		if (isset ( $response ['body'] ) && isset ( $response ['body'] ['totals'] )) {
			return $response ['body'] ['totals'] ['count'];
		}
	}
	
	public static function steamcommunity() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'steamcommunity.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$request = wp_remote_get ( 'http://steamcommunity.com/groups/' . $id );
		
		if (! $request) {
			return 0;
		}
		
		preg_match ( '/<span class="count ">([0-9]+)<\/span>/s', wp_remote_retrieve_body ( $request ), $matches );
		
		if (is_array ( $matches ) && count ( $matches ) > 0) {
			return $matches [1];
		}
	}
	
	public static function weheartit() {
		
		$id = ESSBSocialFansCounterHelper::get_option ( 'weheartit.id' );
		
		if (empty ( $id )) {
			return 0;
		}
		
		$request = wp_remote_request ( 'http://weheartit.com/' . $id . '/fans' );
		
		if (! $request) {
			return 0;
		}
		
		preg_match ( '/<h3>([0-9]+) (Follower|Followers)<\/h3>/s', wp_remote_retrieve_body ( $request ), $matches );
		
		if (is_array ( $matches ) && count ( $matches ) > 0) {
			return $matches [1];
		}
	}
	
	public static function feedly() {
		
		$url = ESSBSocialFansCounterHelper::get_option ( 'feedly.url' );
		
		if (empty ( $url )) {
			return 0;
		}
		
		$request = wp_remote_request ( 'http://cloud.feedly.com/v3/feeds/feed' . urlencode ( '/' . $url ) );
		
		if (! $request) {
			return 0;
		}
		
		$response = json_decode ( wp_remote_retrieve_body ( $request ), true );
		
		if (is_array ( $response ) && isset ( $response ['subscribers'] )) {
			return $response ['subscribers'];
		}
	}

	public static function essb_remote_get( $url , $json = true) {
		$get_request = wp_remote_get( $url , array( 'timeout' => 18 , 'sslverify' => false ) );
		$request = wp_remote_retrieve_body( $get_request );
		if( $json ) $request = @json_decode( $request , true );
		return $request;
	}
	
	public static function twitch() {
		$id = ESSBSocialFansCounterHelper::get_option ( 'twitch.id' );
		$result = 0;
		try {
			$data 	= self::essb_remote_get("https://api.twitch.tv/kraken/channels/$id");
			$result = isset($data['followers']) ? (int) $data['followers'] : 0;
		} catch (Exception $e) {
			$result = 0;
		}
		
		return $result;
	}
	
	public static function spotify() {
		$id = $url = ESSBSocialFansCounterHelper::get_option ( 'spotify.id' );
		$id = rtrim( $id , "/");
		$id = urlencode( str_replace( array(  'https://play.spotify.com/', 'https://player.spotify.com/', 'artist/', 'user/' ) , '', $id) );
		
		$result = 0;
		try {
			if( !empty( $url ) && strpos( $url, 'artist') !== false ){
				$data = self::essb_remote_get("https://api.spotify.com/v1/artists/$id");
			}else{
				$data = self::essb_remote_get("https://api.spotify.com/v1/users/$id");
			}
			$result = (int) $data['followers']['total'];
		
		} catch (Exception $e) {
			$result = 0;
		}
		
		return $result;
	}
	
	public static function mymail() {
		$result = 0;
		
		$list = ESSBSocialFansCounterHelper::get_option ( 'mymail.id' );
		if( class_exists( 'mymail' ) ) {
		if( !empty( $list )){
			if( $list == 'all' ){
				$counts = mymail('subscribers')->get_count_by_status();
				$result	= $counts[1];
			}else{
				$result	= mymail('lists')->get_member_count( $list, 1) ;
			}
		}
		}
		return $result;
	}
	
	public static function mailpoet() {
		
		$result = 0;
		
		$list = ESSBSocialFansCounterHelper::get_option ( 'mailpoet.id' );
		
		if( !empty( $list )){
			if( $list == 'all' ){
				$result	= ESSBSocialFansCounterHelper::mailpoet_total_subscribers();
			}else{
				$result	= ESSBSocialFansCounterHelper::mailpoet_get_list_users( $list );
			}
		}
		
		return $result;
	}
}
