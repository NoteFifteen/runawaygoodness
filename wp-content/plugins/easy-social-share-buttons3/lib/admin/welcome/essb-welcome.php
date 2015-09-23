<?php

$tabs = array ("essb-welcome" => "What's New", "essb-promote" => "Promote & Earn Money" );
$active_tab = isset ( $_REQUEST ['tab'] ) ? $_REQUEST ['tab'] : "essb-welcome";
$slug = "essb_about";

if (ESSB3_ADDONS_ACTIVE) {
	$addons = ESSBAddonsHelper::get_instance();
	$new_addons = $addons->get_new_addons();

	foreach ($new_addons as $key => $data) {
		$all_addons_button = '<a href="'.admin_url ("admin.php?page=essb_addons").'"  text="' . __ ( 'Add-ons', ESSB3_TEXT_DOMAIN ) . '" class="button button-orange float_right" style="margin-right: 5px;"><i class="fa fa-gear"></i>&nbsp;' . __ ( 'View list of all addons', ESSB3_TEXT_DOMAIN ) . '</a>';

		$dismiss_url = esc_url_raw(add_query_arg(array('dismiss' => 'true', 'addon' => $key), admin_url ("admin.php?page=essb_options")));

		$dismiss_addons_button = '<a href="'.$dismiss_url.'"  text="' . __ ( 'Add-ons', ESSB3_TEXT_DOMAIN ) . '" class="button button-orange float_right" style="margin-right: 5px;"><i class="fa fa-close"></i>&nbsp;' . __ ( 'Dismiss', ESSB3_TEXT_DOMAIN ) . '</a>';
		printf ( '<div class="essb-information-box fade"><div class="icon orange"><i class="fa fa-cube"></i></div><div class="inner">New add-on for Easy Social Share Buttons for WordPress is available: <a href="%2$s" target="_blank"><b>%1$s</b></a> %4$s%3$s</div></div>', $data['title'], $data['url'], $all_addons_button, $dismiss_addons_button );
	}
}

?>

<div class="wrap essb-page-welcome about-wrap">
	<h1><?php echo sprintf( __( 'Welcome to Easy Social Share Buttons for WordPress %s', ESSB3_TEXT_DOMAIN ), preg_replace( '/^(\d+)(\.\d+)?(\.\d)?/', '$1$2', ESSB3_VERSION ) ) ?></h1>

	<div class="about-text">
		<?php _e( 'Easy Social Share Buttons for WordPress is all-in-one social share solution that allows you share, monitor and increase your social popularity by AppsCreo', ESSB3_TEXT_DOMAIN )?>
	</div>
	<div class="wp-badge essb-page-logo">
		<?php echo sprintf( __( 'Version %s', ESSB3_TEXT_DOMAIN ), ESSB3_VERSION )?>
	</div>
	<div class="essb-page-actions">


		<div class="essb-welcome-button-container">
			<a
				href="<?php echo esc_attr( admin_url( 'admin.php?page=essb_options' ) ) ?>"
				class="button button-primary"><?php _e( 'Settings', ESSB3_TEXT_DOMAIN ) ?></a>
			<a href="http://codecanyon.net/downloads" target="_blank" class="button">Rate <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i> Easy Social Share Buttons for WordPress</a>
		</div>
		<div class="essb-welcome-button-container">
			<a href="https://twitter.com/share" class="twitter-share-button"
				data-text="Take full control over social sharing in WordPress with Easy Social Share Buttons by @appscreo"
				data-url="http://bit.ly/socialsharewp" data-size="large"
				data-counturl="http://codecanyon.net/item/easy-social-share-buttons-for-wordpress/6394476">Tweet</a>
			<script>! function ( d, s, id ) {
				var js, fjs = d.getElementsByTagName( s )[ 0 ], p = /^http:/.test( d.location ) ? 'http' : 'https';
				if ( ! d.getElementById( id ) ) {
					js = d.createElement( s );
					js.id = id;
					js.src = p + '://platform.twitter.com/widgets.js';
					fjs.parentNode.insertBefore( js, fjs );
				}
			}( document, 'script', 'twitter-wjs' );</script>
		</div>
		<div
			class="essb-welcome-button-container essb-welcome-button-container-google">

			<!-- Place this tag where you want the +1 button to render. -->
			<div class="g-plusone"
				data-href="http://codecanyon.net/item/easy-social-share-buttons-for-wordpress/6394476"></div>
		</div>
			<div class="essb-welcome-button-container essb-welcome-button-container-facebook">
			<div class="fb-like" style="top: 6px; margin-left:-25px;" data-href="http://codecanyon.net/item/easy-social-share-buttons-for-wordpress/6394476" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true"></div>
			<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/bg_BG/sdk.js#xfbml=1&version=v2.4";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
			</div>
	</div>

	<!-- tabs -->
	<h2 class="nav-tab-wrapper">
	<?php foreach ( $tabs as $tab_slug => $title ): ?>
		<?php $url = 'admin.php?page=' . rawurlencode( $slug ) . '&tab=' . rawurlencode( $tab_slug ); ?>
		<a
			href="<?php echo esc_attr( is_network_admin() ? network_admin_url( $url ) : admin_url( $url ) ) ?>"
			class="nav-tab<?php echo $active_tab === $tab_slug ? esc_attr( ' nav-tab-active' ) : '' ?>">
			<?php echo $title?>
		</a>
	<?php endforeach; ?>
	</h2>

	<?php
	if ($active_tab == "essb-welcome") {
		?>
	<!-- welcome content -->
	<div class="essb_welcome-tab changelog">
		<div class="feature-section col">
			<div>
				<img class="essb-featured-img"
					src="<?php echo ESSB3_PLUGIN_URL ?>/assets/images/welcome/focus-screenshot.png" />

				<h3>Extended Mobile Sharing & Google+ Update</h3>

				<p>Latest version of Easy Social Share Buttons for WordPress 3.2
					will take your mobile share into a new level. We add sharing via SMS and one of most popular mobile applications: Viber.</p>
					<p>Version 3.2 also contains the new Google+ logo introduced on 1st of September</p>
			</div>
		</div>

		<div class="essb_welcome-feature feature-section col three-col">
			<div>
				<img class="essb-img-center" title=""
					src="<?php echo ESSB3_PLUGIN_URL ?>/assets/images/welcome/welcome-features-01.png" />
				<h4>New networks in Fans Counter</h4>

				<p>New version of Easy Social Share Buttons for WordPress includes support for 4 new networks in social fans counter: myMail, MailPoet, Spotify & Twitch</p>
			</div>
			<div>
				<img class="essb-img-center" title=""
					src="<?php echo ESSB3_PLUGIN_URL ?>/assets/images/welcome/welcome-features-02.png" />
				<h4>New templates in Fans Counter</h4>

				<p>We also extend Social Fans Counter with 4 new templates: Round Grey Icons, Tiny (Color), Tiny (Light) & Tiny (Dark)</p>
			</div>
			<div class="last-feature">
				<img class="essb-img-center" title=""
					src="<?php echo ESSB3_PLUGIN_URL ?>/assets/images/welcome/welcome-features-03.png" />
				<h4>Top Social Posts widget</h4>

				<p>We include the brand new top social posts widget with advanced settings for posts display (work of widget requires Social Metrics Lite to be activated).</p>
			</div>
		</div>

		<div class="essb_welcome-feature feature-section col three-col">
			<div>
				<img class="essb-img-center" title=""
					src="<?php echo ESSB3_PLUGIN_URL ?>/assets/images/welcome/welcome-features-04.png" />
				<h4>Compatible with Social Profile Analytics Add-on</h4>

				<p>Social Profile Analytics will help you to monitor the change of social profile fans of all Social Fans Counter networks, activated on your WordPress installation. It is easy way to inspect in details the profile fans count changes of each WordPress active network by different time periods (from day to year). So Social Profile Analytics is must have tool in the process of analyzing the reactions of your audience and base your online marketing strategy on real facts.</p>
			</div>
			<div>
				<img class="essb-img-center" title=""
					src="<?php echo ESSB3_PLUGIN_URL ?>/assets/images/welcome/welcome-features-05.png" />
				<h4>Filter to control shared addressr</h4>

				<p>Version 3.2 contains filter that developers can use if they wish to control share address (for example if you wish to include your own tracking code or affiliate id) - add_filter('essb3_share_url', 'your_custom_hook');</p>
			</div>
			<div class="last-feature">
				<img class="essb-img-center" title=""
					src="<?php echo ESSB3_PLUGIN_URL ?>/assets/images/welcome/welcome-features-06.png" />
				<h4>Various updates & improvements</h4>

				<p>We made additional changes in plugin code to optimize and improve work including delayed counter loading, easy options transfer and more.</p>
			</div>
		</div>
		<p class="essb-thank-you">
			Thank you for choosing <b>Easy Social Share Buttons for WordPress</b>.
			If you like our work please <a href="http://codecanyon.net/downloads"
				target="_blank">rate Easy Social Share Buttons for WordPress <i
				class="fa fa-star"></i><i class="fa fa-star"></i><i
				class="fa fa-star"></i><i class="fa fa-star"></i><i
				class="fa fa-star"></i></a>
		</p>

	</div>
	<?php
	}
	
	if ($active_tab == "essb-promote") {
		?>
	<div class="essb-page-promote changelog">
		<div class="feature-section col">
			<div>
				<h4>
					Promote <b>Easy Social Share Buttons for WordPress</b> and earn
					money from the Envato Affiliate Program.
				</h4>
				Send traffic to any page on Envato Market while adding your account
				username to the end of the URL. When a new user clicks your referral
				link, signs up for an account and purchases an item (or deposits
				money) via any of the Envato Market sites, you will receive 30% of
				that person's first cash deposit or purchase price. If they deposit
				$20 into their account, you get $6. If they buy a $200 item, you get
				$60.
				<p>
				<a href="http://themeforest.net/make_money/affiliate_program" target="_blank">Read more about how Envato affiliate program works on its official site.</a>
				</p>
			</div>
			<p>&nbsp;</p>
			<p>
				Your Envato Username: <input type="text" class="input-element"
					name="envato-user" id="envato-user" /><a href="#"
					class="button button-primary" id="generate-my-code">Get my code</a>
			</p>

			<p id="usercode" style="display: none;">
			Example affilaite links that you can use:<br/>
				<textarea id="user-generated-code" class="input-element"
					style="width: 100%; height: 300px"></textarea>
			</p>
		</div>
	</div>
	<script type="text/javascript">

	jQuery(document).ready(function($){
		$('#generate-my-code').click(function(e) {
			e.preventDefault();

			var envatoUsername = $('#envato-user').val();

			var myCode = "";
			myCode += "<!-- Example code 1 -->\r\n";
			myCode += '<a href="http://codecanyon.net/item/easy-social-share-buttons-for-wordpress/6394476?ref='+envatoUsername+'" target="_blank" title="Easy Social Share Buttons for WordPress - Social sharing plugin that will amplify your social reach">Easy Social Share Buttons for WordPress - Social sharing plugin that will amplify your social reach</a>';
			myCode += "\r\n\r\n";

			myCode += "<!-- Example code 2 -->\r\n";
			myCode += '<a href="http://codecanyon.net/item/easy-social-share-buttons-for-wordpress/6394476?ref='+envatoUsername+'" target="_blank" title="Easy Social Share Buttons for WordPress">Easy Social Share Buttons for WordPress</a>';
			myCode += "\r\n\r\n";

			myCode += "<!-- Example code 3 -->\r\n";
			myCode += '<a href="http://codecanyon.net/item/easy-social-share-buttons-for-wordpress/6394476?ref='+envatoUsername+'" target="_blank" title="Easy Social Share Buttons for WordPress">This site uses Easy Social Share Buttons for WordPress</a>';
			myCode += "\r\n\r\n";
			
			myCode += "<!-- Example code 4 -->\r\n";
			myCode += '<a href="http://codecanyon.net/item/easy-social-share-buttons-for-wordpress/6394476?ref='+envatoUsername+'" target="_blank" title="Social Sharing Plugin for WordPress">Social Sharing Plugin for WordPress that will help increase your social presentation</a>';
			myCode += "\r\n\r\n";
			
			
			$('#user-generated-code').val(myCode);
			
			$('#usercode').show();
		});
	});

	</script>
	<?php
	}
	?>
</div>

<!-- Place this tag in your head or just before your close body tag. -->
<script src="https://apis.google.com/js/platform.js" async defer></script>

