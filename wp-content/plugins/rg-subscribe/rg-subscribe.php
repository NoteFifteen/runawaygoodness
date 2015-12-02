<?php
/*
 * Plugin Name: Runaway Goodness - Subscription Tools
 * Plugin URI: https://runawaygoodness.com/media/
 * Description: Creates a new short code called [rg_signup] which allows you to embed a Runaway Goodness subscription box in your posts and a new Runaway Goodness Subscribe Widget.
 * Version: 1.0
 * Author: RunawayGoodness
 * Author URI: https://runawaygoodness.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: runawaygoodness
 */

class RG_Subscribe_Widget extends WP_Widget {
	const WIDGET_URL = 'https://runawaygoodness.com/wp-content/plugins/rg-mailchimp-connect/assets/rg_subscribe.min.js';
	const JQUERY_CDN_URL = 'https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js';

	const ENQ_HANDLE = 'rg_subscribe_js';
	const SHORTCODE_NAME = 'rg_signup';

	public function __construct() {

		// Register the shortcode function
		add_shortcode( self::SHORTCODE_NAME, array( $this, 'rg_signup_shortcode' ) );

		if ( ! is_admin() ) {
			// Deregister the default jQuery and use something newer
			wp_deregister_script( 'jquery' );
			wp_register_script( 'jquery', self::JQUERY_CDN_URL );
			wp_enqueue_script( 'jquery' );

			// Register the Widget script, specify a dependency on jQuery
			wp_register_script( self::ENQ_HANDLE, self::WIDGET_URL, array( 'jquery' ), null, true );
			wp_enqueue_script( self::ENQ_HANDLE );

		}

		parent::WP_Widget( false, $name = __( 'Runaway Goodness Subscribe', 'RG_Subscribe_Widget' ) );
	}

	/**
	 * Returns a widget for use inside regular posts
	 *
	 * @return string
	 */
	public function rg_signup_shortcode() {

		$source_url = get_option( 'home' );

		$html = "<div id=\"rg-signupblock\"></div>" . PHP_EOL;
		$html .= "<script>" . PHP_EOL;
		$html .= 'var rg_mySite = "' . get_option( 'home' ) . '";' . PHP_EOL;
		$html .= 'var rg_showGenres = true; ' . PHP_EOL;
		$html .= "</script>" . PHP_EOL;

		return $html;
	}

	/**
	 * Widget Form Settings
	 *
	 * @param array $instance
	 *
	 * @return string
	 */
	public function form( $instance ) {
		if ( $instance ) {
			$show_genres = esc_attr( $instance['show_genres'] );
		} else {
			$show_genres = 'Yes';
		}

		?>
		<div>
		<p>For the widget, you can enable and disable the display of the available genres that the user can subscribe to.</p>
			<label for="<?php echo $this->get_field_id( 'show_genres' ); ?>"><?php _e( 'Show Genres',
						'RG_Subscribe_Widget' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'show_genres' ); ?>"
			        id="<?php echo $this->get_field_id( 'show_genres' ); ?>" class="widefat">
		<?php
			foreach ( array('Yes', 'No') as $option ) {
				echo '<option value="' . $option . '" id="' . $option . '"', $show_genres == $option ? ' selected="selected"' : '', '>', $option, '</option>';
			}
		?>
			</select>
		</div>
		<?php
	}

		public function update( $new_instance, $old_instance ) {
			$instance                = $old_instance;
			$instance['show_genres'] = strip_tags( $new_instance['show_genres'] );

			return $instance;
		}

		public function widget( $args, $instance ) {
		$source_url  = get_option( 'home' );
		$show_genres = ( isset( $instance['show_genres'] ) && $instance['show_genres'] == 'No' ) ? 'false' : 'true';

		?>
	<div id="rg-signupblock"></div>
	<script>
		var rg_mySite = "<?php echo $source_url ?>";
		var rg_showGenres = <?php echo $show_genres ?>;
	</script>
	<?php
	}
}


add_action('widgets_init',
		create_function('', 'return register_widget("RG_Subscribe_Widget");')
);

