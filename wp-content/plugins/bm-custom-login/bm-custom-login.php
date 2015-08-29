<?php
/*
Plugin Name: BM Custom Login
Plugin URI: http://www.binarymoon.co.uk/projects/bm-custom-login/
Description: Display custom images on the WordPress login screen. Useful for branding.
Author: Ben Gillbanks
Version: 1.8.2
Author URI: http://www.binarymoon.co.uk/
*/

define( 'CL_GROUP', 'custom_login' );
define( 'CL_PAGE', 'custom_login_admin' );
define( 'CL_SECTION', 'custom_login_section' );
define( 'CL_OPTIONS', 'custom_login_options' );


if ( ! class_exists( 'BMCustomLogin' ) ) {

class BMCustomLogin {

	private $options = array ();

	/**
	 * Setup the object
	 */
	function __construct() {

		add_action( 'admin_init', array( $this, 'custom_login_init' ) );
		add_action( 'admin_menu', array( $this, 'custom_login_admin_add_page' ) );
		add_action( 'login_head', array( $this, 'custom_login' ) );
		add_filter( 'login_headerurl', array( $this, 'custom_login_url' ) );
		add_filter( 'login_headertitle', array( $this, 'custom_login_title' ) );
		add_filter( 'admin_footer_text', array( $this, 'custom_login_admin_footer_text' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_color_picker' ) );
		add_action( 'admin_footer', array( $this, 'admin_foot_script' ) );

	}

	/**
	 *
	 * @global type $options
	 * @return type
	 */
	function custom_login_get_options() {

		if ( empty( $this->options ) ) {
			$this->options = get_option( CL_OPTIONS );
		}

		return $this->options;

	}


	/**
	 * Display the custom login info
	 */
	function custom_login() {

		$options = $this->custom_login_get_options();

		$pluginUrl = plugin_dir_url( __FILE__ ) . 'bm-custom-login.css';

		// output styles
		echo '<link rel="stylesheet" type="text/css" href="' . esc_url( $pluginUrl ) . '" />';
		echo '<style>';

		$background = $options['cl_background'];

		if ( ! empty( $background ) ) {
?>
		#login {
			background:url(<?php echo esc_url( $background ); ?>) top center no-repeat;
		}
<?php
		}

		// text colour
		if ( ! empty( $options['cl_color'] ) ) {
?>
		#login,
		#login label {
			color:<?php echo $this->sanitize_hex_color( $options['cl_color'] ); ?>;
		}
<?php
		}

		$body_styles = array();

		// background colour
		if ( ! empty( $options['cl_backgroundColor'] ) ) {
			$body_styles[] = $this->sanitize_hex_color( $options['cl_backgroundColor'] );
		}

		if ( ! empty( $options['cl_backgroundImage'] ) ) {
			$body_styles[] = 'url(' . esc_url( $options['cl_backgroundImage'] ) . ')';

			if ( ! empty( $options['cl_backgroundPX'] ) ) {
				$body_styles[] = esc_attr( $options['cl_backgroundPX'] );
			}

			if ( ! empty( $options['cl_backgroundPY'] ) ) {
				$body_styles[] = esc_attr( $options['cl_backgroundPY'] );
			}

			if ( ! empty( $options['cl_backgroundRepeat'] ) ) {
				$body_styles[] = esc_attr( $options['cl_backgroundRepeat'] );
			}
		}

		if ( count( $body_styles ) ) {
?>
		html {
			background:<?php echo implode( $body_styles, ' ' ); ?> !important;
		}
		body.login {
			background:transparent !important;
		}
<?php
		}

		// text colour
		if ( ! empty( $options['cl_linkColor'] ) ) {
?>
		.login #login a {
			color:<?php echo $this->sanitize_hex_color( $options['cl_linkColor'] ); ?> !important;
		}
		.login #login a:hover {
			color:<?php echo $this->adjust_brightness( $options['cl_linkColor'], -30 ); ?> !important;
		}
		.submit #wp-submit {
			background:<?php echo $this->sanitize_hex_color( $options['cl_linkColor'] ); ?>;
			border-color:<?php echo $this->adjust_brightness( $options['cl_linkColor'], -60 ); ?>;
			color:<?php echo $this->readable_colour( $options['cl_linkColor'] ); ?>
		}
		.submit #wp-submit:hover {
			background:<?php echo $this->adjust_brightness( $options['cl_linkColor'], -30 ); ?>;
		}
<?php
		}

		if ( ! empty( $options['cl_colorShadow'] ) ) {
?>
		#login #nav,
		#login #backtoblog {
			text-shadow:0 1px 6px <?php echo $this->sanitize_hex_color( $options['cl_colorShadow'] ); ?>;
		}
<?php
		}

		// custom css
		if ( ! empty( $options['cl_customCSS'] ) ) {
			$css = $this->sanitize_css( $options['cl_customCSS'] );
			echo str_replace( array( "\n","\r","\t" ), '', $css );
		}

		echo '</style>';

	}


	/**
	 * sanitize user entered css
	 * as seen here: http://wordpress.stackexchange.com/questions/53970/sanitize-user-entered-css
	 *
	 * @param type $css
	 */
	function sanitize_css( $css ) {

		if ( ! class_exists( 'csstidy' ) ) {
			include_once( 'csstidy/class.csstidy.php' );
		}

		$csstidy = new csstidy();
		$csstidy->set_cfg( 'remove_bslash', false );
		$csstidy->set_cfg( 'compress_colors', false );
		$csstidy->set_cfg( 'compress_font-weight', false );
		$csstidy->set_cfg( 'discard_invalid_properties', true );
		$csstidy->set_cfg( 'merge_selectors', false );
		$csstidy->set_cfg( 'remove_last_;', false );
		$csstidy->set_cfg( 'css_level', 'CSS3.0' );

		$css = preg_replace( '/\\\\([0-9a-fA-F]{4})/', '\\\\\\\\$1', $css );
		$css = wp_kses_split( $css, array(), array() );

		$csstidy->parse( $css );

		return $csstidy->print->plain();

	}


	/**
	 *
	 */
	function custom_login_url( $url ) {

		return get_home_url();

	}


	/**
	 *
	 */
	function custom_login_title( $title ) {

		$options = $this->custom_login_get_options();

		if ( ! empty( $options['cl_powerby'] ) ) {
			$title = $options['cl_powerby'];
		}

		return $title;

	}


	/**
	 *
	 * @param <type> $oldText
	 * @return <type>
	 */
	function custom_login_admin_footer_text( $old_text ) {

		$options = $this->custom_login_get_options();

		if ( ! empty( $options['cl_footertext'] ) )  {
			return wp_kses_post( $options['cl_footertext'] );
		}

		return wp_kses_post( $old_text );

	}


	/**
	 *
	 */
	function custom_login_admin_add_page () {

		add_options_page( 'BM Custom Login', 'Custom Login', 'manage_options', CL_PAGE, array( $this, 'custom_login_options' ) );

	}


	/**
	 *
	 */
	function custom_login_options () {
?>
	<style>
		.wrap {
			position:relative;
		}

		.cl_notice {
			padding:10px 20px;
			-moz-border-radius:3px;
			-webkit-border-radius:3px;
			border-radius:3px;
			background:lightyellow;
			border:1px solid #e6db55;
			margin:10px 5px 10px 0;
		}

		.cl_notice h3 {
			margin-top:5px;
			padding-top:0;
		}

		.cl_notice li {
			list-style-type:disc;
			margin-left:20px;
		}
	</style>

	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br /></div>
		<h2><?php _e( 'Custom Login Options', 'custom_login' ); ?></h2>

		<form action="options.php" method="post">
<?php
		settings_fields( CL_GROUP );
		do_settings_sections( CL_PAGE );
?>
			<p class="submit">
				<input name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes', 'custom_login' ); ?>" class="button-primary" />
			</p>
		</form>

		<div class="cl_notice">
			<h3>More WordPress Goodies &rsaquo;</h3>
			<p>If you like this plugin then you may also like my themes on <a href="http://prothemedesign.com" target="_blank">Pro Theme Design</a></p>
			<ul>
				<li><a href="http://twitter.com/prothemedesign">Pro Theme Design on Twitter</a></li>
				<li><a href="http://facebook.com/ProThemeDesign">Pro Theme Design on Facebook</a></li>
			</ul>
		</div>

	</div>

	<?php
	}


	/**
	 *
	 */
	function custom_login_init () {

		$vars = $this->custom_login_get_options();

		register_setting(
			CL_GROUP,
			CL_OPTIONS,
			array( $this, 'custom_login_validate' )
		);

		add_settings_section(
			CL_SECTION,
			__( 'Login Screen Settings', 'custom_login' ),
			array( $this, 'custom_login_section_validate' ),
			CL_PAGE
		);

		add_settings_field (
			'cl_background',
			__( 'Background Image Url:', 'custom_login' ),
			array( $this, 'form_image' ),
			CL_PAGE,
			CL_SECTION,
			array (
				'id' => 'cl_background',
				'value' => $vars,
				'default' => '',
				'description' => __( 'Ideal size is 312 by 600', 'custom_login' ),
			)
		);

		add_settings_field (
			'cl_powerby',
			__( 'Custom Login Powered by:', 'custom_login' ),
			array( $this, 'form_text' ),
			CL_PAGE,
			CL_SECTION,
			array (
				'id' => 'cl_powerby',
				'value' => $vars,
				'default' => '',
				'description' => '',
			)
		);

		add_settings_field (
			'cl_footertext',
			__( 'WordPress footer text:', 'custom_login' ),
			array( $this, 'form_text' ),
			CL_PAGE,
			CL_SECTION,
			array (
				'id' => 'cl_footertext',
				'value' => $vars,
				'default' => '',
				'description' => __( 'Appears at the bottom of the admin pages when logged in.', 'custom_login' ),
			)
		);

		add_settings_field (
			'cl_backgroundColor',
			__( 'Page Background Color:', 'custom_login' ),
			array( $this, 'form_color' ),
			CL_PAGE,
			CL_SECTION,
			array (
				'id' => 'cl_backgroundColor',
				'value' => $vars,
				'default' => '#eeeeee',
			)
		);

		add_settings_field (
			'cl_backgroundImage',
			__( 'Page Background Image:', 'custom_login' ),
			array( $this, 'form_image' ),
			CL_PAGE,
			CL_SECTION,
			array (
				'id' => 'cl_backgroundImage',
				'value' => $vars,
				'default' => '',
				'description' => '',
			)
		);

		add_settings_field (
			'cl_backgroundPY',
			__( 'Page Background Vertical Position:', 'custom_login' ),
			array( $this, 'form_select' ),
			CL_PAGE,
			CL_SECTION,
			array (
				'id' => 'cl_backgroundPY',
				'value' => $vars,
				'default' => 'top',
				'options' => array ('top', 'center', 'bottom'),
				'description' => __( 'Vertical  position of background element', 'custom_login' ),
			)
		);

		add_settings_field (
			'cl_backgroundPX',
			__( 'Page Background Horizontal Position:', 'custom_login' ),
			array( $this, 'form_select' ),
			CL_PAGE,
			CL_SECTION,
			array (
				'id' => 'cl_backgroundPX',
				'value' => $vars,
				'default' => 'center',
				'options' => array ('left', 'center', 'right'),
				'description' => __( 'Horizontal position of background element', 'custom_login' ),
			)
		);

		add_settings_field (
			'cl_backgroundPRepeat',
			__( 'Page Background Repeat:', 'custom_login' ),
			array( $this, 'form_select' ),
			CL_PAGE,
			CL_SECTION,
			array (
				'id' => 'cl_backgroundRepeat',
				'value' => $vars,
				'default' => 'no-repeat',
				'options' => array ('no-repeat', 'repeat-x', 'repeat-y', 'repeat'),
				'description' => __( 'Background image repeat', 'custom_login' ),
			)
		);

		add_settings_field (
			'cl_color',
			__( 'Text Color:', 'custom_login' ),
			array( $this, 'form_color' ),
			CL_PAGE,
			CL_SECTION,
			array (
				'id' => 'cl_color',
				'value' => $vars,
				'default' => '#333333',
			)
		);

		add_settings_field (
			'cl_colorShadow',
			__( 'Text Shadow Color:', 'custom_login' ),
			array( $this, 'form_color' ),
			CL_PAGE,
			CL_SECTION,
			array (
				'id' => 'cl_colorShadow',
				'value' => $vars,
				'default' => '#000000',
			)
		);

		add_settings_field (
			'cl_linkColor',
			__( 'Text Link Color:', 'custom_login' ),
			array( $this, 'form_color' ),
			CL_PAGE,
			CL_SECTION,
			array (
				'id' => 'cl_linkColor',
				'value' => $vars,
				'default' => '#21759B',
			)
		);

		add_settings_field (
			'cl_customCSS',
			__( '<strong>Advanced<strong> - Custom CSS:', 'custom_login' ),
			array( $this, 'form_textarea' ),
			CL_PAGE,
			CL_SECTION,
			array (
				'id' => 'cl_customCSS',
				'value' => $vars,
				'default' => '',
				'description' => '',
			)
		);


	}


	/**
	 *
	 * @param type $fields
	 * @return type
	 */
	function custom_login_validate ($fields) {

		// colour validation
		$fields['cl_color'] = $this->sanitize_hex_color( $fields['cl_color'] );
		$fields['cl_colorShadow'] = $this->sanitize_hex_color( $fields['cl_colorShadow'] );
		$fields['cl_backgroundColor'] = $this->sanitize_hex_color( $fields['cl_backgroundColor'] );
		$fields['cl_linkColor'] = $this->sanitize_hex_color( $fields['cl_linkColor'] );
		$fields['cl_background'] = esc_url_raw( $fields['cl_background'] );
		$fields['cl_backgroundImage'] = esc_url_raw( $fields['cl_backgroundImage'] );
		$fields['cl_powerby'] = strip_tags( esc_html( $fields['cl_powerby'] ) );
		$fields['cl_customCSS'] = $this->sanitize_css( $fields['cl_customCSS'] );

		return $fields;

	}


	/**
	 *
	 * @param type $fields
	 * @return type
	 */
	function custom_login_section_validate ($fields) {

		return $fields;

	}


	/**
	 *
	 * @param type $args
	 */
	function form_text( $args ) {

		$id = '';
		$value = '';

		// set values
		if ( ! empty ($args['value'][$args['id']] ) ) {
			$value = $args['value'][$args['id']];
		} else {
			if (!empty ($args['default'])) {
				$value = $args['default'];
			}
		}

		$id = $args['id'];
?>
	<input type="text" id="<?php echo $id; ?>" name="<?php echo CL_OPTIONS; ?>[<?php echo $id; ?>]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
<?php
		if ( ! empty ($args['description'])) {
			echo '<br /><span class="description">' . $args['description'] . '</span>';
		}

	}


	/**
	 *
	 * @param type $args
	 */
	function form_textarea( $args ) {

		$id = '';
		$value = '';

		// set values
		if ( ! empty ($args['value'][$args['id']] ) ) {
			$value = $args['value'][$args['id']];
		} else {
			if (!empty ($args['default'])) {
				$value = $args['default'];
			}
		}

		$id = $args['id'];

		if ( ! empty ( $args['description'] ) ) {
			echo '<p class="description">' . $args['description'] . '</p>';
		}
?>
	<textarea type="text" rows="10" cols="50" id="<?php echo $id; ?>" name="<?php echo CL_OPTIONS; ?>[<?php echo $id; ?>]" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
<?php

	}


	/**
	 *
	 * @param type $args
	 */
	function form_select( $args ) {

		$id = '';
		$value = '';
		$options = array();

		if (!empty($args['options'])) {
			$options = $args['options'];
		}

		if (!empty($args['value'][$args['id']])) {
			$value = $args['value'][$args['id']];
		} else {
			if (!empty ($args['default'])) {
				$value = $args['default'];
			}
		}

		$id = $args['id'];

		// display select box options list
		if ( $options ) {
			echo '<select id="' . $id . '" name="' . CL_OPTIONS . '[' . $id . ']">';
			foreach ( $options as $o ) {
				$selected = '';
				if ( $o == $value ) {
					$selected = ' selected="selected" ';
				}
				echo '<option value="' . $o . '" ' . $selected . '>' . $o . '</option>';
			}
			echo '</select>';
		}

		if ( ! empty ( $args['description'] ) ) {
			echo '<br /><span class="description">' . $args['description'] . '</span>';
		}

	}


	/**
	 * display a color picker in place of a text input
	 *
	 * @param type $args
	 */
	function form_color( $args ) {

		$id = '';
		$value = '';
		$description = '';

		// set values
		if ( ! empty( $args['value'][$args['id']] ) ) {
			$value = $args['value'][$args['id']];
		} else {
			if ( ! empty( $args['default'] ) ) {
				$value = $args['default'];
			}
		}

		if ( ! empty( $args['description'] ) ) {
			$description = $args['description'];
		}

		$id = $args['id'];

?>
	<input type="text" id="<?php echo $id; ?>" name="<?php echo CL_OPTIONS; ?>[<?php echo $id; ?>]" value="<?php echo $this->sanitize_hex_color( $value ); ?>" data-default-color="#<?php echo $this->sanitize_hex_color( $args['default'] ); ?>" class="color-picker"/>
<?php
		if ( ! empty( $description ) ) {
			echo '<br /><span class="description">' . $description . '</span>';
		}

	}


	/**
	 *
	 * @param type $args
	 */
	function form_image( $args ) {

		$id = '';
		$value = '';
		$description = '';

		// set values
		if ( ! empty( $args['value'][$args['id']] ) ) {
			$value = $args['value'][$args['id']];
		} else {
			if ( ! empty( $args['default'] ) ) {
				$value = $args['default'];
			}
		}

		if ( ! empty( $args['description'] ) ) {
			$description = $args['description'];
		}

		$id = $args['id'];
?>
		<input class="image-picker" type="text" size="36" name="<?php echo CL_OPTIONS; ?>[<?php echo $id; ?>]" value="<?php echo $value; ?>" />
		<button class="image-picker-button button-secondary"><?php esc_attr_e( 'Upload Image', 'custom_login' ); ?></button>
<?php

		if ( ! empty( $description ) ) {
			echo '<br /><span class="description">' . $description . '</span>';
		}
	}


	/**
	 * @param type $hook
	 */
	function enqueue_color_picker() {

		$screen = get_current_screen();

		if ( 'settings_page_custom_login_admin' !== $screen->id ) {
			return;
		}

		// Add the color picker css file
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// add media library
		wp_enqueue_media();

	}


	/**
	 *
	 */
	function admin_foot_script() {

		$screen = get_current_screen();

		if ( 'settings_page_custom_login_admin' !== $screen->id ) {
			return;
		}

?>
	<script>
		(function( $ ) {
			$(function() {

				$( 'input.color-picker' ).wpColorPicker();

				var media_init = function(selector, button_selector)  {
					var clicked_button = false;

					$(selector).each(function (i, input) {
						var button = jQuery(input).next(button_selector);
						button.click(function (event) {
							event.preventDefault();
							var selected_img;
							clicked_button = jQuery(this);

							// check for media manager instance
							if(wp.media.frames.frame) {
								wp.media.frames.frame.open();
								return;
							}
							// configuration of the media manager new instance
							wp.media.frames.frame = wp.media({
								title: 'Select image',
								multiple: false,
								library: {
									type: 'image'
								},
								button: {
									text: 'Use selected image'
								}
							});

							// Function used for the image selection and media manager closing
							var media_set_image = function() {
								var selection = wp.media.frames.frame.state().get('selection');

								// no selection
								if (!selection) {
									return;
								}

								// iterate through selected elements
								selection.each(function(attachment) {
									var url = attachment.attributes.url;
									clicked_button.prev(selector).val(url);
								});
							};

							wp.media.frames.frame.on('close', media_set_image);
							wp.media.frames.frame.on('select', media_set_image);
							wp.media.frames.frame.open();
						});
				   });
				};

				media_init( '.image-picker', '.image-picker-button' );

			});
		})( jQuery );
	</script>
<?php
	}


	/**
	 * sanitize hexedecimal numbers used for colors
	 *
	 * @param type $color
	 * @return string
	 */
	function sanitize_hex_color( $color ) {

		if ( '' === $color ) {
			return '';
		}

		// make sure the color starts with a hash
		$color = '#' . ltrim( $color, '#' );

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
			return $color;
		}

		return null;

	}

	/**
	 * adjust brightness of a colour
	 * not the best way to do it but works well enough here
	 *
	 * @param type $hex
	 * @param type $steps
	 * @return type
	 */
	function adjust_brightness( $hex, $steps ) {

		$steps = max( -255, min( 255, $steps ) );

		$hex = str_replace( '#', '', $hex );
		if ( strlen( $hex ) == 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1), 2 );
		}

		$color_parts = str_split( $hex, 2 );
		$return = '#';

		foreach ( $color_parts as $color ) {
			$color = hexdec( $color );
			$color = max( 0, min( 255, $color + $steps ) );
			$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT );
		}

		return $this->sanitize_hex_color( $return );

	}


	/**
	 * Calculate whether black or white is best for readability based upon the brightness of specified colour
	 *
	 * @param type $hex
	 */
	function readable_colour( $hex ) {

		$hex = str_replace( '#', '', $hex );
		if ( strlen( $hex ) == 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1), 2 );
		}

		$color_parts = str_split( $hex, 2 );

		$brightness = ( hexdec( $color_parts[0] ) * 0.299 ) + ( hexdec( $color_parts[1] ) * 0.587 ) + ( hexdec( $color_parts[2] ) * 0.114 );

		if ( $brightness > 128 ) {
			return '#000';
		} else {
			return '#fff';
		}

	}
}

new BMCustomLogin();

} // class_exists