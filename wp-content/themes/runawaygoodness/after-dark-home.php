<?php
/*
Template Name: After Dark Home
*/

//* Add custom body class to the head
add_filter( 'body_class', 'centric_add_body_class' );
function centric_add_body_class( $classes ) {

   $classes[] = 'centric-pro-landing after-dark-home';
   return $classes;
   
}

//* Force full width content layout
add_filter( 'genesis_site_layout', '__genesis_return_full_width_content' );

//* Remove site header elements
remove_action( 'genesis_header', 'genesis_header_markup_open', 5 );
remove_action( 'genesis_header', 'genesis_do_header' );
remove_action( 'genesis_header', 'genesis_header_markup_close', 15 );

//* Remove navigation
remove_action( 'genesis_after_header', 'genesis_do_nav' );
remove_action( 'genesis_after_header', 'genesis_do_subnav' );

//* Remove breadcrumbs
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );

//* Remove site footer widgets
remove_action( 'genesis_before_footer', 'genesis_footer_widget_areas' );

//* Remove site footer elements
remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
remove_action( 'genesis_footer', 'genesis_do_footer' );
remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );

//* Entry stuff
remove_action( 'genesis_entry_header', 'genesis_do_post_title' );

add_action( 'genesis_before', 'do_fullscreen_video' );

function do_fullscreen_video() {

$html = '	<section id="welcome" class="post-132 page type-page status-publish hentry fullscreen">';
$html.= '		<div class="tint" style="background-image:url(http://runawaygoodness.com/wp-content/themes/runawaygoodness/images/texture.png);background-repeat:repeat;background-position:center center;opacity:1;"></div>';
$html.= '		<video poster="http://runawaygoodness.com/wp-content/themes/runawaygoodness/images/cover.jpg" loop autoplay>';
$html.= '			<source src="http://runawaygoodness.com/wp-content/themes/runawaygoodness/images/Sexy-Dancer-Slowly-Touching-2.webmsd.webm" type="video/webm">';
$html.= '			<source src="http://runawaygoodness.com/wp-content/themes/runawaygoodness/images/Sexy-Dancer-Slowly-Touching-2.mp4.mp4" type="video/mp4">';
$html.= '		</video>';
$html.= '		<div class="signup-container">';
$html.= '			<div class="signup-block">';
$html.= '				<div class="signup-text">';
$html.= '					<h1>Runaway Goodness<br />After Dark</h1>';
$html.= '					<h2>Hot and Steamy Books<br />Discounted Prices<br />Directly to your Inbox</h2>';
$html.= '					<p>And the first one is on us.</p>';

$html.= '						<form action="//runawaygoodness.us11.list-manage.com/subscribe/post?u=1c904fe9a0639b7e2464b65c4&amp;id=159f7318a4" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" novalidate>';
$html.= '							<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->';
$html.= '							<div style="position: absolute; left: -5000px;">';
$html.= '								<input type="text" name="b_1c904fe9a0639b7e2464b65c4_159f7318a4" tabindex="-1" value="">';
$html.= '							</div>';


if( isset( $_GET["ref"] ) ) {
	$new_source = $_GET["ref"];
	$remove_chars = array( ',', '@', '.', ' ', '$' );
	foreach( $remove_chars as $k ) {
		$new_source = str_replace( $k, "_", $new_source );
	}

	$source_id = esc_attr( $new_source );
} elseif ( isset( $_COOKIE["rgref"] ) ) {
	$source_id = $_COOKIE["rgref"];
} else {
	$source_id = 'rg-ad-home';
}

$html.= '							<input type="hidden" name="SOURCE" id="SOURCE" value="'. $source_id .'" />';
$html.= '							<div id="" class="om-clearfix om-has-email" data-om-action="selectable" data-om-target="#optin-monster-saas-field-footer_bg">';
$html.= '								<input type="email" value="" name="EMAIL" class="required email" id="bt-mce-EMAIL" aria-required="true" placeholder="Enter your email address here..."><br />';
$html.= '								<input type="checkbox" name="over18" id="over18"> Yep, I\'m over 18<br />';
$html.= '								<input id="om-lightbox-bullseye-optin-submit" type="submit" data-om-action="selectable" placeholder="Enter a valid email address" data-om-target="#optin-monster-saas-field-submit_field" value="Get Your Book!">';
$html.= '							</div>';
$html.= '						</form>';
$html.= '				</div>';
$html.= '			</div>';
$html.= '		</div>';
$html.= '	</section>';
echo $html;

}




//* Run the Genesis loop
genesis();
