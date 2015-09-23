<?php

global $essb_available_social_networks, $essb_avaliable_counter_positions, $essb_avaiable_total_counter_position, $essb_avaiable_button_style;
global $essb_available_button_positions, $essb_avaliable_content_positions, $essb_available_tempaltes;

global $essb_available_button_positions_mobile, $essb_avaliable_content_positions_mobile, $essb_avaiable_total_counter_position_mobile, 
	$essb_avaliable_counter_positions_mobile, $essb_available_social_profiles;

global $essb_default_native_buttons;

$essb_default_native_buttons = array();
$essb_default_native_buttons[] = 'google';
$essb_default_native_buttons[] = 'twitter';
$essb_default_native_buttons[] = 'facebook';
$essb_default_native_buttons[] = 'linkedin';
$essb_default_native_buttons[] = 'pinterest';
$essb_default_native_buttons[] = 'youtube';
$essb_default_native_buttons[] = 'managewp';
$essb_default_native_buttons[] = 'vk';


$essb_available_tempaltes = array ();
$essb_available_tempaltes [''] = "Default template from settings";
$essb_available_tempaltes ['default'] = "Default";
$essb_available_tempaltes ['metro'] = "Metro";
$essb_available_tempaltes ['modern'] = "Modern";
$essb_available_tempaltes ['roud'] = "Round";
$essb_available_tempaltes ['big'] = "Big";
$essb_available_tempaltes ['metro-retina'] = "Metro (Retina)";
$essb_available_tempaltes ['big-retina'] = "Big (Retina)";
$essb_available_tempaltes ['light-retina'] = "Light (Retina)";
$essb_available_tempaltes ['flat-retina'] = "Flat (Retina)";
$essb_available_tempaltes ['tiny-retina'] = "Tiny (Retina)";
$essb_available_tempaltes ['round-retina'] = "Round (Retina)";
$essb_available_tempaltes ['modern-retina'] = "Modern (Retina)";
$essb_available_tempaltes ['circles-retina'] = "Circles (Retina)";
$essb_available_tempaltes ['blocks-retina'] = "Blocks (Retina)";
$essb_available_tempaltes ['dark-retina'] = "Dark (Retina)";
$essb_available_tempaltes ['grey-circles-retina'] = "Grey Circles (Retina)";
$essb_available_tempaltes ['grey-blocks-retina'] = "Grey Blocks (Retina)";
$essb_available_tempaltes ['clear-retina'] = "Clear (Retina)";
$essb_available_tempaltes ['dimmed-retina'] = "Dimmed (Retina)";
$essb_available_tempaltes ['grey-retina'] = "Grey (Retina)";
$essb_available_tempaltes ['default-retina'] = "Default 3.0 (Retina)";

$essb_available_social_networks = array (
		'facebook' => array ('name' => 'Facebook', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'twitter' => array ('name' => 'Twitter', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'google' => array ('name' => 'Google+', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'pinterest' => array ('name' => 'Pinterest', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'linkedin' => array ('name' => 'LinkedIn', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'digg' => array ('name' => 'Digg', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'del' => array ('name' => 'Del', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'stumbleupon' => array ('name' => 'StumbleUpon', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'tumblr' => array ('name' => 'Tumblr', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'vk' => array ('name' => 'VKontakte', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'print' => array ('name' => 'Print', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'mail' => array ('name' => 'Email', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'flattr' => array ('name' => 'Flattr', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'reddit' => array ('name' => 'Reddit', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'buffer' => array ('name' => 'Buffer', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'love' => array ('name' => 'Love This', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'weibo' => array ('name' => 'Weibo', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'pocket' => array ('name' => 'Pocket', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'xing' => array ('name' => 'Xing', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'ok' => array ('name' => 'Odnoklassniki', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'mwp' => array ('name' => 'ManageWP.org', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'more' => array ('name' => 'More Button', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'whatsapp' => array ('name' => 'WhatsApp', 'type' => 'buildin', 'supports' => 'mobile' ), 
		'meneame' => array ('name' => 'Meneame', 'type' => 'buildin', 'supports' => 'desktop,mobile' ), 
		'blogger' => array ('name' => 'Blogger', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only' ), 
		'amazon' => array ('name' => 'Amazon', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only' ), 
		'yahoomail' => array ('name' => 'Yahoo Mail', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only' ), 
		'gmail' => array ('name' => 'Gmail', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only' ), 
		'aol' => array ('name' => 'AOL', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only' ), 
		'newsvine' => array ('name' => 'Newsvine', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only' ), 
		'hackernews' => array ('name' => 'HackerNews', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only' ), 
		'evernote' => array ('name' => 'Evernote', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only' ), 
		'myspace' => array ('name' => 'MySpace', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only' ), 
		'mailru' => array ('name' => 'Mail.ru', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only' ), 
		'viadeo' => array ('name' => 'Viadeo', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only' ), 
		'line' => array ('name' => 'Line', 'type' => 'buildin', 'supports' => 'mobile,retina templates only' ),
		/*'embedly' => array(
				'name' => 'embed.ly',
				'type' => 'buildin'
		),*/
		'flipboard' => array ('name' => 'Flipboard', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only'),
		'comments' => array( 'name' => 'Comments', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only'),
		'yummly' => array( 'name' => 'Yummly', 'type' => 'buildin', 'supports' => 'desktop,mobile,retina templates only'),
		'sms' => array( 'name' => 'SMS', 'type' => 'buildin', 'supports' => 'mobile,retina templates only'),
		'viber' => array( 'name' => 'Viber', 'type' => 'buildin', 'supports' => 'mobile,retina templates only')
		   );

// Line => http://media.line.me/howto/en/

$essb_avaliable_counter_positions = array ();
$essb_avaliable_counter_positions ['left'] = "Left";
$essb_avaliable_counter_positions ['right'] = "Right";
$essb_avaliable_counter_positions ['inside'] = "Inside button instead of network name";
$essb_avaliable_counter_positions ['insidename'] = "Inside button after network name";
$essb_avaliable_counter_positions ['insidebeforename'] = "Inside button before network name";
$essb_avaliable_counter_positions ['insidehover'] = "Inside button and appear when you hover button over the network name";
$essb_avaliable_counter_positions ['hidden'] = "Hidden (use this position if you wish to have only total counter)";
$essb_avaliable_counter_positions ['leftm'] = "Left Modern";
$essb_avaliable_counter_positions ['rightm'] = "Right Modern";
$essb_avaliable_counter_positions ['top'] = "Top Modern";
$essb_avaliable_counter_positions ['topm'] = "Top Mini";
$essb_avaliable_counter_positions ['bottom'] = "Bottom";

$essb_avaliable_counter_positions_mobile = array ();
$essb_avaliable_counter_positions_mobile ['inside'] = "Inside button instead of network name";
$essb_avaliable_counter_positions_mobile ['insidename'] = "Inside button after network name";
$essb_avaliable_counter_positions_mobile ['insidebeforename'] = "Inside button before network name";
$essb_avaliable_counter_positions_mobile ['insidehover'] = "Inside button and appear when you hover button over the network name";
$essb_avaliable_counter_positions_mobile ['hidden'] = "Hidden (use this position if you wish to have only total counter)";

$essb_avaiable_total_counter_position = array ();
$essb_avaiable_total_counter_position ['right'] = "Right";
$essb_avaiable_total_counter_position ['left'] = "Left";
$essb_avaiable_total_counter_position ['rightbig'] = "Right Big Number (with option for custom text)";
$essb_avaiable_total_counter_position ['leftbig'] = "Left Big Nubmer (with option for custom text)";
$essb_avaiable_total_counter_position ['before'] = "Before social share buttons";
$essb_avaiable_total_counter_position ['after'] = "After social share buttons";
$essb_avaiable_total_counter_position ['hidden'] = "This will hide the total counter and make only button counters be visible";

$essb_avaiable_total_counter_position_mobile = array ();
$essb_avaiable_total_counter_position_mobile ['before'] = "Before social share buttons";
$essb_avaiable_total_counter_position_mobile ['after'] = "After social share buttons";
$essb_avaiable_total_counter_position_mobile ['hidden'] = "This will hide the total counter and make only button counters be visible";

$essb_avaiable_button_style = array ();
$essb_avaiable_button_style ['button'] = 'Display as share button with icon and network name';
$essb_avaiable_button_style ['button_name'] = 'Display as share button with network name and without icon';
$essb_avaiable_button_style ['icon'] = 'Display share buttons only as icon without network names';
$essb_avaiable_button_style ['icon_hover'] = 'Display share buttons as icon with network name appear when button is pointed';

$essb_avaliable_content_positions = array ();
$essb_avaliable_content_positions ['content_top'] = array ("image" => "assets/images/display-positions-02.png", "label" => "Content top" );
$essb_avaliable_content_positions ['content_bottom'] = array ("image" => "assets/images/display-positions-03.png", "label" => "Content bottom" );
$essb_avaliable_content_positions ['content_both'] = array ("image" => "assets/images/display-positions-04.png", "label" => "Content top and bottom" );
$essb_avaliable_content_positions ['content_float'] = array ("image" => "assets/images/display-positions-05.png", "label" => "Float from content top" );
$essb_avaliable_content_positions ['content_floatboth'] = array ("image" => "assets/images/display-positions-06.png", "label" => "Float from content top and bottom" );
$essb_avaliable_content_positions ['content_nativeshare'] = array ("image" => "assets/images/display-positions-07.png", "label" => "Native social buttons top, share buttons bottom" );
$essb_avaliable_content_positions ['content_sharenative'] = array ("image" => "assets/images/display-positions-08.png", "label" => "Share buttons top, native buttons bottom" );
$essb_avaliable_content_positions ['content_manual'] = array ("image" => "assets/images/display-positions-09.png", "label" => "Manual display with shortcode only" );

$essb_avaliable_content_positions_mobile = array ();
$essb_avaliable_content_positions_mobile ['content_top'] = array ("image" => "assets/images/display-positions-02.png", "label" => "Content top" );
$essb_avaliable_content_positions_mobile ['content_bottom'] = array ("image" => "assets/images/display-positions-03.png", "label" => "Content bottom" );
$essb_avaliable_content_positions_mobile ['content_both'] = array ("image" => "assets/images/display-positions-04.png", "label" => "Content top and bottom" );
$essb_avaliable_content_positions_mobile ['content_float'] = array ("image" => "assets/images/display-positions-05.png", "label" => "Float from content top" );
$essb_avaliable_content_positions_mobile ['content_manual'] = array ("image" => "assets/images/display-positions-09.png", "label" => "Manual display with shortcode only" );

$essb_available_button_positions = array ();
$essb_available_button_positions ['sidebar'] = array ("image" => "assets/images/display-positions-10.png", "label" => "Sidebar" );
$essb_available_button_positions ['popup'] = array ("image" => "assets/images/display-positions-11.png", "label" => "Pop up" );
$essb_available_button_positions ['flyin'] = array ("image" => "assets/images/display-positions-12.png", "label" => "Fly in" );
$essb_available_button_positions ['postfloat'] = array ("image" => "assets/images/display-positions-13.png", "label" => "Post vertical float" );
$essb_available_button_positions ['topbar'] = array ("image" => "assets/images/display-positions-14.png", "label" => "Top bar" );
$essb_available_button_positions ['bottombar'] = array ("image" => "assets/images/display-positions-15.png", "label" => "Bottom bar" );
$essb_available_button_positions ['onmedia'] = array ("image" => "assets/images/display-positions-16.png", "label" => "On media" );

$essb_available_button_positions_mobile = array ();
$essb_available_button_positions_mobile ['sidebar'] = array ("image" => "assets/images/display-positions-10.png", "label" => "Sidebar" );
$essb_available_button_positions_mobile ['topbar'] = array ("image" => "assets/images/display-positions-14.png", "label" => "Top bar" );
$essb_available_button_positions_mobile ['bottombar'] = array ("image" => "assets/images/display-positions-15.png", "label" => "Bottom bar" );
$essb_available_button_positions_mobile ['sharebottom'] = array ("image" => "assets/images/display-positions-17.png", "label" => "Share buttons bar (Mobile Only Display Method)" );
$essb_available_button_positions_mobile ['sharebar'] = array ("image" => "assets/images/display-positions-18.png", "label" => "Share bar (Mobile Only Display Method)" );
$essb_available_button_positions_mobile ['sharepoint'] = array ("image" => "assets/images/display-positions-19.png", "label" => "Share point (Mobile Only Display Method)" );

$essb_available_social_profiles = array ("twitter" => "Twitter", "facebook" => "Facebook", "google" => "Google+", "pinterest" => "Pinterest", "foursquare" => "foursquare", "yahoo" => "Yahoo!", "skype" => "skype", "yelp" => "yelp", "feedburner" => "FeedBurner", "linkedin" => "Linkedin", "viadeo" => "Viadeo", "xing" => "Xing", "myspace" => "Myspace", "soundcloud" => "soundcloud", "spotify" => "Spotify", "grooveshark" => "grooveshark", "lastfm" => "last.fm", "youtube" => "YouTube", "vimeo" => "vimeo", "dailymotion" => "Dailymotion", "vine" => "Vine", "flickr" => "flickr", "500px" => "500px", "instagram" => "Instagram", "wordpress" => "WordPress", "tumblr" => "tumblr", "blogger" => "Blogger", "technorati" => "Technorati", "reddit" => "reddit", "dribbble" => "dribbble", "stumbleupon" => "StumbleUpon", "digg" => "Digg", "envato" => "Envato", "behance" => "Behance", "delicious" => "Delicious", "deviantart" => "deviantART", "forrst" => "Forrst", "play" => "Play Store", "zerply" => "Zerply", "wikipedia" => "Wikipedia", "apple" => "Apple", "flattr" => "Flattr", "github" => "GitHub", "chimein" => "Chime.in", "friendfeed" => "FriendFeed", "newsvine" => "NewsVine", "identica" => "Identica", "bebo" => "bebo", "zynga" => "zynga", "steam" => "steam", "xbox" => "XBOX", "windows" => "Windows", "outlook" => "Outlook", "coderwall" => "coderwall", "tripadvisor" => "tripadvisor", "appnet" => "appnet", "goodreads" => "goodreads", "tripit" => "Tripit", "lanyrd" => "Lanyrd", "slideshare" => "SlideShare", "buffer" => "Buffer", "rss" => "RSS", "vkontakte" => "VKontakte", "disqus" => "DISQUS", "houzz" => "houzz", "mail" => "Mail", "patreon" => "Patreon", "paypal" => "Paypal", "playstation" => "PlayStation", "smugmug" => "SmugMug", "swarm" => "Swarm", "triplej" => "triplej", "yammer" => "Yammer", "stackoverflow" => "stackoverflow", "drupal" => "Drupal", "odnoklassniki" => "Odnoklassniki", "android" => "Android", "meetup" => "Meeptup", "persona" => "Mozilla Persona" );