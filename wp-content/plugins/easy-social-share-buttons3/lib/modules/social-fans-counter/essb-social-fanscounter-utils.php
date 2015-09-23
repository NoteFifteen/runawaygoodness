<?php

class ESSBSocialFansCounterUtils {
	
	private static $options = array ();
	
	public static function register_options($options) {
		
		self::$options = $options;
	}
	
	public static function show_title() {
		
		if (false == self::get_option ( 'hide_title' )) {
			
			return true;
		}
	}
	
	public static function widget_columns() {
		
		return intval ( self::get_option ( 'columns' ) );
	}
	
	public static function column_class() {
		
		$columns = self::widget_columns ();
		
		if ($columns == 1) {
			
			return 'essbfc-col-md-12 essbfc-col-one';
		}
		
		if ($columns > 0) {
			
			$css_cols = (12 / $columns);
			
			if (strpos($css_cols, '.') !== false) {
				$css_cols = str_replace('.', '-', $css_cols);
			}
			
			if ($columns > 6) {
				$css_cols = $columns;
			}
			
			return 'essbfc-col-lg-' . $css_cols . ' essbfc-col-md-' . $css_cols . ' essbfc-col-sm-' . $css_cols . ' essbfc-col-xs-' . $css_cols;
		}
	}
	
	public static function effect_class($social) {
		
		$effects = self::get_option ( 'effects' );
		
		if (in_array ( $social, array ('wp_posts', 'wp_users', 'wp_comments', 'love' ) )) {
			$effects = 'essbfc-no-effect';
		}
		
		return $effects;
	}
	
	public static function show_numbers() {
		
		if (false == self::get_option ( 'hide_numbers' )) {
			
			return true;
		}
	}
	
	public static function show_diff() {
		
		if (false != self::get_option ( 'show_diff' )) {
			
			return true;
		}
	}
	
	public static function show_diff_lt_zero() {
		
		if (false != self::get_option ( 'show_diff_lt_zero' )) {
			
			return true;
		}
	}
	
	public static function new_window() {
		
		if (true == self::get_option ( 'new_window' )) {
			
			return '_blank';
		}
	}
	
	public static function nofollow() {
		
		if (true == self::get_option ( 'nofollow' )) {
			
			return 'nofollow';
		}
	}
	
	public static function diff_count_text_color() {
		
		$color = self::get_option ( 'diff_count_text_color' );
		if (! empty ( $color )) {
			return 'color: ' . $color . ' !important;';
		}
	}
	
	public static function diff_count_bg_color() {
		
		$color = self::get_option ( 'diff_count_bg_color' );
		
		if (! empty ( $color )) {
			return 'background-color: ' . $color . ' !important;';
		}
	}
	
	public static function css_bg_class($social) {
		
		$bg_color = self::get_option ( 'bg_color' );
		
		$template = self::get_option('template');
		if (!empty($template)) {
			if ($template == "color" ) {
				$bg_color = "light";
			}
			if ($template == "grey") {
				$bg_color = "light";
			}
			if ($template == "metro") {
				$bg_color = "colord";
			}
			if ($template == "tinycolor") {
				$bg_color = "colord tiny";
			}
			if ($template == "dark") {
				$bg_color = "dark";
			}
			if ($template == "flat") {
				$bg_color = "flat";
			}
			if ($template == "grey-transparent" || $template == "color-transparent" || $template == "lite" || $template == "roundcolor" || $template == "roundgrey" || $template == "tinylight" || $template == "tinygrey") {
				$bg_color = "transparent";
				
				if ($template == "roundcolor" || $template == "roundgrey") {
					$bg_color = "transparent-round";
				}
				
				if ($template == "tinygrey" || $template == "tinylight") {
					$bg_color = "tiny";
				}
			}
		}
		
		if ($bg_color == 'light') {
			
			return 'essbfc-dark-bg';
		}
		
		if ($bg_color == 'dark') {
			
			return 'essbfc-light-bg';
		}
		
		if ($bg_color == 'colord') {
			
			return 'essbfc-bg-' . $social;
		}
		
		if ($bg_color == 'flat') {
				
			return 'essbfc-flat essbfc-bg-' . $social;
		}
		
		if ($bg_color == 'transparent') {
			
			return 'essbfc-transparent';
		}
		
		if ($bg_color == 'transparent-round') {
				
			return 'essbfc-transparent-round';
		}

		if ($bg_color == 'tiny') {
				
			return 'essbfc-transparent essbfc-tiny';
		}
		
		if ($bg_color == 'colord tiny') {
				
			return 'essbfc-bg-' . $social.' essbfc-tiny';
		}
	}
	
	public static function css_text_color_class() {
		
		$template = self::get_option('template');
		if (!empty($template)) {
			if ($template == "grey" || $template == "color" || $template == "roundcolor" || $template == "roundgrey") {
				return 'essbfc-light-color';
			}
			if ($template == "lite") {
				return 'essbfc-light-color';
			}
			if ($template == "metro") {
				return 'essbfc-dark-color';
			}
			if ($template == "dark") {
				return 'essbfc-dark-color';
			}
			if ($template == "flat") {
				return 'essbfc-dark-color';
			}
			if ($template == "grey-transparent") {
				return 'essbfc-light-color';
			}
			
			if ($template == "tinylight" || $template == "tinygrey") {
				return 'essbfc-light-color essbfc-tiny';
			}
			if ($template == "tinycolor") {
				return 'essbfc-dark-color essbfc-tiny';
			}
		}
		
		if (self::get_option ( 'icon_color' ) == 'light') {
			
			return 'essbfc-dark-color';
		}
		
		if (self::get_option ( 'icon_color' ) == 'dark') {
			
			return 'essbfc-light-color';
		}
	}
	
	public static function css_icon_image_class($social) {
		
		$icon = '';
		
		switch ($social) {
			case 'wp_posts' :
				$icon = '-essbfc-icon-wp-posts';
				break;
			
			case 'wp_comments' :
				$icon = '-essbfc-icon-wp-comments';
				break;
			
			case 'wp_users' :
				$icon = '-essbfc-icon-wp-users';
				break;			
			case 'weheartit' :
				$icon = '-essbfc-icon-weheartit-1';
				break;
			case 'love' :
				$icon = '-essbfc-icon-weheartit-1';
				break;
			default :
				$icon = '-essbfc-icon-' . $social;
				break;
		}
		return $icon;
	}
	
	public static function css_icon_color_class($social) {
		
		$template = self::get_option('template');
		if (!empty($template)) {
			if ($template == "color" || $template == "color-transparent") {
				return 'essbfc-c-' . $social;
			}
			else if ($template == "roundcolor") {
				return 'essbfc-rc-' . $social;
			}
			else if ($template == "roundgrey") {
				return 'essbfc-rcg-' . $social;
			}
			else if ($template == "tinylight") {
				return 'essbfc-c-' . $social;
			}
		}
		
		if (self::get_option ( 'icon_color' ) == 'colord') {
			
			return 'essbfc-c-' . $social;
		}
	}
	
	public static function css_sp_class($social) {
		
		$template = self::get_option('template');
		if (!empty($template)) {
			if ($template == "color") {
				return 'essbfc-bg-' . $social;
			}
			if ($template == "grey") {
				return 'essbfc-light-bg';
			}
			
			if ($template == "lite") {
				return 'essbfc-light-bg';
			}
			
			if ($template == "metro") {
				return 'essbfc-dark-bg';
			}
			if ($template == "dark") {
				return 'essbfc-dark-bg';
			}
			if ($template == "flat") {
				return 'essbfc-dark-bg';
			}
			if ($template == "grey-transparent") {
				return 'essbfc-light-bg';
			}
			if ($template == "color-transparent") {
				return 'essbfc-bg-' . $social;
			}
		}
		
		if (self::get_option ( 'icon_color' ) == 'light') {
			
			return 'essbfc-dark-bg';
		}
		
		if (self::get_option ( 'icon_color' ) == 'dark') {
			
			return 'essbfc-light-bg';
		}
		
		if (self::get_option ( 'icon_color' ) == 'colord') {
			
			return 'essbfc-bg-' . $social;
		}
	}
	
	public static function css_hover_text_bg_color_class($social) {
		
		$hover_text_color = self::get_option ( 'hover_text_bg_color' );
		
		if ($hover_text_color == 'light') {
			
			return 'essbfc-dark-bg';
		}
		
		if ($hover_text_color == 'dark') {
			
			return 'essbfc-light-bg';
		}
		
		if ($hover_text_color == 'colord') {
			
			return 'essbfc-bg-' . $social;
		}
	}
	
	public static function css_hover_text_color_class($social) {
		
		$hover_text_color = self::get_option ( 'hover_text_color' );
		
		if ($hover_text_color == 'light') {
			
			return 'essbfc-dark-color';
		}
		
		if ($hover_text_color == 'dark') {
			
			return 'essbfc-light-color';
		}
		
		if ($hover_text_color == 'colord') {
			
			return 'essbfc-c-' . $social;
		}
	}
	
	public static function social_url($social) {
		
		switch ($social) {
			case 'facebook' :
				return 'http://www.facebook.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'twitter' :
				return 'http://www.twitter.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'google' :
				return 'http://plus.google.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'pinterest' :
				return 'http://www.pinterest.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'linkedin' :
				if (ESSBSocialFansCounterHelper::get_option ( $social . '.account_type', 'company' ) == 'company') {
					//return 'http://www.linkedin.com/company/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
					return ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				} elseif (ESSBSocialFansCounterHelper::get_option ( $social . '.account_type', 'company' ) == 'group') {
					return 'http://www.linkedin.com/groups/?gid=' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				} else {
					return 'http://www.linkedin.com/profile/view?id=' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				}
				break;
			case 'github' :
				return 'http://github.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'vimeo' :
				if (ESSBSocialFansCounterHelper::get_option ( $social . '.account_type', 'channel' ) == 'user') {
					{
						$vimeo_id = trim ( ESSBSocialFansCounterHelper::get_option ( $social . '.id' ) );
						
						if (preg_match ( '/^[0-9]+$/', $vimeo_id )) {
							return 'http://vimeo.com/user' . $vimeo_id;
						} else {
							return 'http://vimeo.com/' . $vimeo_id;
						}
					}
				} else {
					return 'http://vimeo.com/channels/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				}
				break;
			case 'dribbble' :
				return 'http://dribbble.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'soundcloud' :
				return 'https://soundcloud.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'behance' :
				return 'http://www.behance.net/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'foursquare' :
				if (intval ( ESSBSocialFansCounterHelper::get_option ( $social . '.id' ) ) && intval ( ESSBSocialFansCounterHelper::get_option ( $social . '.id' ) ) == ESSBSocialFansCounterHelper::get_option ( $social . '.id' )) {
					return 'https://foursquare.com/user/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				} else {
					return 'https://foursquare.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				}
				break;
			case 'forrst' :
				return 'http://forrst.com/people/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'mailchimp' :
				return ESSBSocialFansCounterHelper::get_option ( $social . '.list_url' );
				break;
			case 'delicious' :
				return 'https://delicious.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'instgram' :
				return 'http://instagram.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.username' );
				break;
			case 'youtube' :
				return 'http://www.youtube.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.account_type' ) . '/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'envato' :
				$ref = '';
				if (ESSBSocialFansCounterHelper::get_option ( $social . '.ref' )) {
					$ref = '?ref=' . ESSBSocialFansCounterHelper::get_option ( $social . '.ref' );
				}
				return 'http://www.' . ESSBSocialFansCounterHelper::get_option ( $social . '.site' ) . '.net/user/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' ) . $ref;
				break;
			case 'vk' :
				$account_type = ESSBSocialFansCounterHelper::get_option ( $social . '.account_type' );
				if ($account_type == "community") {
					return 'http://www.vk.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				}
				else {
					return 'http://www.vk.com/id' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				}
				break;
			case 'rss' :
				return ESSBSocialFansCounterHelper::get_option ( $social . '.link' );
				break;
			case 'vine' :
				return 'https://vine.co/' . ESSBSocialFansCounterHelper::get_option ( $social . '.username' );
				break;
			case 'tumblr' :
				$basename2arr = explode ( '.', ESSBSocialFansCounterHelper::get_option ( $social . '.basename' ) );
				if ($basename2arr == 'www')
					return 'http://' . ESSBSocialFansCounterHelper::get_option ( $social . '.basename' );
				else
					return 'http://www.tumblr.com/follow/' . @$basename2arr [0];
				break;
			case 'slideshare' :
				return 'http://www.slideshare.net/' . ESSBSocialFansCounterHelper::get_option ( $social . '.username' );
				break;
			case '500px' :
				return 'http://500px.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.username' );
				break;
			case 'flickr' :
				return 'https://www.flickr.com/photos/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'wp_posts' :
			case 'wp_users' :
			case 'wp_comments' :
				$url = ESSBSocialFansCounterHelper::get_option ( $social . '.url' );
				if ($url == "") {
					$url = 'javascript:void(0);';
				}
				return $url;
				break;
			case 'audioboo' :
				return 'https://audioboo.fm/users/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'steamcommunity' :
				return 'http://steamcommunity.com/groups/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'weheartit' :
				return 'http://weheartit.com/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
			case 'love' :
				return ESSBSocialFansCounterHelper::get_option ( $social . '.url' );
				break;
			case 'total' :
				return ESSBSocialFansCounterHelper::get_option ( $social . '.url' );
				break;
			case 'feedly' :
				return 'http://feedly.com/i/subscription/feed' . urlencode ( '/' . ESSBSocialFansCounterHelper::get_option ( $social . '.url' ) );
				break;
			case 'mymail':
				return ESSBSocialFansCounterHelper::get_option ( $social . '.url' );
				break;
			case 'mailpoet':
				return ESSBSocialFansCounterHelper::get_option ( $social . '.url' );
				break;
			case 'twitch' :
				return 'http://www.twitch.tv/' . ESSBSocialFansCounterHelper::get_option ( $social . '.id' ).'/profile';
				break;
			case 'spotify' :
				return ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
		}
	}
	
	public static function fans_count($social, $format = true) {
		
		if (self::is_cached ( $social )) {
			if ($format) {
				return self::format_count ( self::get_cached_count ( $social ) );
			} else {
				return self::get_cached_count ( $social );
			}
		}
		
		switch ($social) {
			case 'twitter' :
				$count = ESSBSocialFansCounterUpdater::twitter ();
				break;
			case 'facebook' :
				$count = ESSBSocialFansCounterUpdater::facebook ();
				break;
			case 'google' :
				$count = ESSBSocialFansCounterUpdater::googleplus ();
				break;
			case 'pinterest' :
				$count = ESSBSocialFansCounterUpdater::pinterest ();
				break;
			case 'linkedin' :
				$count = ESSBSocialFansCounterUpdater::linkedin ();
				break;
			case 'vimeo' :
				$count = ESSBSocialFansCounterUpdater::vimeo ();
				break;
			case 'github' :
				$count = ESSBSocialFansCounterUpdater::github ();
				break;
			case 'dribbble' :
				$count = ESSBSocialFansCounterUpdater::dribbble ();
				break;
			case 'envato' :
				$count = ESSBSocialFansCounterUpdater::envato ();
				break;
			case 'soundcloud' :
				$count = ESSBSocialFansCounterUpdater::soundcloud ();
				break;
			case 'behance' :
				$count = ESSBSocialFansCounterUpdater::behance ();
				break;
			case 'foursquare' :
				$count = ESSBSocialFansCounterUpdater::foursquare ();
				break;
			case 'forrst' :
				$count = ESSBSocialFansCounterUpdater::forrst ();
				break;
			case 'mailchimp' :
				$count = ESSBSocialFansCounterUpdater::mailchimp ();
				break;
			case 'delicious' :
				$count = ESSBSocialFansCounterUpdater::delicious ();
				break;
			case 'instgram' :
				$count = ESSBSocialFansCounterUpdater::instgram ();
				break;
			case 'youtube' :
				$count = ESSBSocialFansCounterUpdater::youtube ();
				break;
			case 'vk' :
				$count = ESSBSocialFansCounterUpdater::vk ();
				break;
			case 'rss' :
				$count = ESSBSocialFansCounterUpdater::rss ();
				break;
			case 'vine' :
				$count = ESSBSocialFansCounterUpdater::vine ();
				break;
			case 'tumblr' :
				$count = ESSBSocialFansCounterUpdater::tumblr ();
				break;
			case 'slideshare' :
				$count = ESSBSocialFansCounterUpdater::slideshare ();
				break;
			case '500px' :
				$count = ESSBSocialFansCounterUpdater::c500Px ();
				break;
			case 'flickr' :
				$count = ESSBSocialFansCounterUpdater::flickr ();
				break;
			case 'wp_posts' :
				$count = ESSBSocialFansCounterUpdater::wpposts ();
				break;
			case 'wp_comments' :
				$count = ESSBSocialFansCounterUpdater::wpcomments ();
				break;
			case 'wp_users' :
				$count = ESSBSocialFansCounterUpdater::wpusers ();
				break;
			case 'audioboo' :
				$count = ESSBSocialFansCounterUpdater::audioboo ();
				break;
			case 'steamcommunity' :
				$count = ESSBSocialFansCounterUpdater::steamcommunity ();
				break;
			case 'weheartit' :
				$count = ESSBSocialFansCounterUpdater::weheartit ();
				break;
			case 'feedly' :
				$count = ESSBSocialFansCounterUpdater::feedly ();
				break;
			case 'love' :
				$count = ESSBSocialFansCounterUpdater::love ();
				break;
			case 'spotify':
				$count = ESSBSocialFansCounterUpdater::spotify();
				break;
			case 'twitch':
				$count = ESSBSocialFansCounterUpdater::twitch();
				break;
			case 'mymail':
				$count = ESSBSocialFansCounterUpdater::mymail();
				break;
			case 'mailpoet':
				$count = ESSBSocialFansCounterUpdater::mailpoet();
				break;
			default :
				$count = 0;
				break;
		}
		
		$is_active_selfcounts = ESSBSocialFansCounterHelper::get_option('uservalues');
		if ($is_active_selfcounts) {
			$user_value = ESSBSocialFansCounterHelper::get_option($social.'.uservalue');
			
			if (intval($user_value) > intval($count)) {
				$count = $user_value;
			}
		}
		
		if (empty ( $count )) {
			$count = self::get_cached_count ( $social );
		}
		
		self::cache_count ( $social, $count );
		
		// @since 3.1.2 bridge with addon Social Profile Analytics
		if (defined('ESSB3_SPA_VERSION')) {
			ESSBSPAFansCounterBridge::log_single_network($social, $count);
		}
		
		if ($format) {
			return self::format_count ( $count );
		} else {
			return $count;
		}
	}
	
	public static function total_fans() {
		
		$total = 0;
		
		foreach ( self::enabled_socials () as $social ) {
			
			$count = self::get_cached_count ( $social );
			if (intval ( $count ) > 0) {
				$total += $count;
			}
		}
		
		return self::format_count ( $total );
	}
	
	public static function fans_text($social) {
		
		return ESSBSocialFansCounterHelper::get_option ( $social . '.text' );
	}
	
	public static function fans_hover_text($social) {
		
		return ESSBSocialFansCounterHelper::get_option ( $social . '.hover_text' );
	}
	
	public static function enabled_socials() {
		
		$networks_order = ESSBSocialFansCounterHelper::get_active_networks_order();
		$networks = ESSBSocialFansCounterHelper::get_active_networks();
		
		$result = array ();
		
		foreach ( $networks_order as $social ) {			
			if (in_array($social, $networks)) {
				if (self::is_valid_account ( $social )) {
					
					$result [] = $social;
				}
			}
		}
		
		return $result;
	}
	
	public static function is_active_total() {
		$networks_order = ESSBSocialFansCounterHelper::get_active_networks_order();
		$networks = ESSBSocialFansCounterHelper::get_active_networks();
		
		if (in_array('total', $networks)) {
			return true;
		}
		else {
			return false;
		}		
	}
	
	public static function is_valid_account($social) {
		
		switch ($social) {
			
			case 'mailchimp' :
				return ESSBSocialFansCounterHelper::get_option ( $social . '.list_id' );
				break;
			case 'rss' :
				return ESSBSocialFansCounterHelper::get_option ( $social . '.link' );
				break;
			case 'feedly' :
				return ESSBSocialFansCounterHelper::get_option ( $social . '.url' );
				break;
			case 'vine' :
			case 'slideshare' :
			case '500px' :
				return ESSBSocialFansCounterHelper::get_option ( $social . '.username' );
				break;
			case 'tumblr' :
				return ESSBSocialFansCounterHelper::get_option ( $social . '.basename' );
				break;
			case 'wp_posts' :
			case 'wp_comments' :
			case 'wp_users' :
			case 'love':
				return true;
				break;
			default :
				return ESSBSocialFansCounterHelper::get_option ( $social . '.id' );
				break;
		}
	}
	
	public static function show_total() {
		
		if (false != self::get_option ( 'show_total' )) {
			
			return true;
		}
	}
	
	public static function get_total_type() {
		return ESSBSocialFansCounterHelper::get_option  ( 'total.viewtype' );
	}
	
	public static function get_total_width() {
		return ESSBSocialFansCounterHelper::get_option  ( 'total.width' );
	}

	public static function get_total_text_position() {
		return ESSBSocialFansCounterHelper::get_option  ( 'total.textposition' );
	}
	
	
	public static function css_total_class($total_type = 'button', $total_width = 'full') {
		
		$socials = count ( self::enabled_socials () );
		$columns = self::widget_columns ();
		
		$rows = floor ( ($socials / $columns) );
		$decimal = ($rows + 1) - ($socials / $columns);
		
		if ($total_type == 'text') {
			$decimal = 0;
		}
		
		
		
		if ($decimal == 0)
			$css_cols = 12;
		else {
			$css_cols = ($decimal * 12);
			
			if ($total_width == 'button') {
				$css_cols = (12 / $columns);
			}
		}
		
		if (strpos($css_cols, '.') !== false) {
			$css_cols = str_replace('.', '-', $css_cols);
		}
		
		
		return 'essbfc-block essbfc-view essbfc-col-lg-' . $css_cols . ' essbfc-col-md-' . $css_cols . ' essbfc-col-sm-' . $css_cols . ' essbfc-col-xs-' . $css_cols;
	}
	
	public static function format_count($count) {
		
		$format = ESSBSocialFansCounterHelper::get_option ( 'format' );
		
		switch ($format) {
			case 'nf' :
				$result = number_format ( self::prevent_format_count ( $count ), 0, '', '' );
				break;
			case 'd' :
				$result = number_format ( self::prevent_format_count ( $count ), 0, '', '.' );
				break;
			case 'c' :
				$result = number_format ( self::prevent_format_count ( $count ), 0, '', ',' );
				break;
			case 's' :
				$result = number_format ( self::prevent_format_count ( $count ), 0, '', ' ' );
				break;
			case 'l' :
				$result = self::format_count_to_letter ( self::prevent_format_count ( $count ) );
				break;
			default :
				$result = $count;
				break;
		}
		
		return $result;
	}
	
	private static function prevent_format_count($count) {
		
		if (strpos ( strtolower ( $count ), 'k' )) {
			
			$count = (intval ( $count ) * 1000);
		}
		
		if (strpos ( strtolower ( $count ), 'm' )) {
			
			$count = (intval ( $count ) * 1000);
		}
		
		return $count;
	}
	
	private static function format_count_to_letter($count) {
		
		$count = intval ( $count );
		
		if ($count < 1000) {
			return $count;
		}
		
		if ($count < 1000000) {
			return number_format ( ($count / 1000), 1 ) . 'k';
		}
		
		return number_format ( ($count / 1000000), 1 ) . 'm';
	}
	
	private static function get_option($option) {
		
		if (isset ( self::$options [$option] )) {
			
			return self::$options [$option];
		}
	}
	
	public static function is_cached($social) {
		
		$expire_time = get_option ( 'essbfcounter_' . $social . '_expire' );
		$now = time ();
		
		$is_alive = ($expire_time > $now);
		
		//print "checking for cached value: ".$social." = is_alive= ".$is_alive;
		
		if (true == $is_alive) {
			return true;
		}
		
		return false;
	}
	
	public static function get_cached_count($social) {
		
		return get_option ( 'essbfcounter_' . $social . '_count' );
	}
	
	private static function cache_count($social, $count) {
		
		$social_expire = ESSBSocialFansCounterHelper::get_option ( $social . '.expire' );
		
		$expire_time = $social_expire;
		
		if (empty ( $social_expire ))
			$expire_time = ESSBSocialFansCounterHelper::get_option ( 'setting.expire' );
		
		update_option ( 'essbfcounter_' . $social . '_count', $count );
		update_option ( 'essbfcounter_' . $social . '_expire', (time () + ($expire_time * 60)) );
	}
	
	public static function box_width() {
		
		if (self::get_option ( 'box_width' ) > 0) {
			return "width: " . self::get_option ( 'box_width' ) . 'px !important;';
		}
	}
	
	public static function shake_class($social) {
		
		$shake = self::get_option ( 'shake' );
		
		if (in_array ( $social, array ('wp_posts', 'wp_users', 'wp_comments', 'love' ) ) && self::get_option ( 'effects' ) != 'essbfc-no-effect') {
			$shake = '';
		}
		
		return $shake;
	}
	
	public static function calc_lastweek_count($social) {
		
		$last_week_count = get_option ( 'essbfcounter_' . $social . '_last_week_count' );
		$current_count = self::get_cached_count ( $social );
		
		return ($current_count - $last_week_count);
	}
	
	public static function get_social_diff($social) {
		
		// last week date in db
		$last_week_day = get_option ( 'essbfcounter_last_week_date' );
		
		// last week real
		$last_week_real = (time () - (7 * 24 * 60 * 60));
		
		$social_last_week_count = get_option ( 'essbfcounter_' . $social . '_last_week_count' );
		
		// check if week done set new counters
		if (($last_week_real > $last_week_day) || empty ( $social_last_week_count )) {
			update_option ( 'essbfcounter_last_week_date', time () );
			update_option ( 'essbfcounter_' . $social . '_last_week_count', self::get_cached_count ( $social ) );
		}
		
		return self::calc_lastweek_count ( $social );
	}
	
	public static function lazy_load() {
		return (self::get_option ( 'is_lazy' ) == 1);
	}
	
	public static function animate_numbers() {
		return (self::get_option ( 'animate_numbers' ) == 1);
	}
	
	public static function max_duration() {
		return self::get_option ( 'max_duration' );
	}

}
