<?php
/**
 * Create settings page
 */

add_action( 'admin_menu', 'rgmc_add_menu' );
add_action( 'admin_init', 'rgmc_register_settings' );

function rgmc_register_settings() {
	register_setting( 'rgmc_settings', 'almost_done_page' );
	register_setting( 'rgmc_settings', 'thank_you_page' );
	register_setting( 'rgmc_settings', 'already_in_page' );
}

function rgmc_add_menu() {
	//create new top-level menu
	add_submenu_page( 'options-general.php', 'RG Mailchimp Connect Settings', 'Mailchimp Connect', 'administrator', 'rgmc_settings', 'rgmc_settings_page' );

	//call register settings function
	add_action( 'admin_init', 'rgmc_register_settings' );
}

function rgmc_settings_page() {
	?>
	<div class="wrap">
	<h2>Mailchimp Connect Settings</h2>
	<form method="post" action="options.php">
	<?php 
	settings_fields( 'rgmc_settings' );
	do_settings_sections( 'rgmc_settings' );

	$args_almost_done = array(
		'selected'			=> get_option( 'almost_done_page' ),
		'name'				=> 'almost_done_page',
		'show_option_none'	=> 'Please Select'
	);

	$args_thank_you = array(
		'selected'			=> get_option( 'thank_you_page' ),
		'name'				=> 'thank_you_page',
		'show_option_none'	=> 'Please Select'
	);

	$args_already_in = array(
		'selected'			=> get_option( 'already_in_page' ),
		'name'				=> 'already_in_page',
		'show_option_none'	=> 'Please Select'
	);

	?>
	<table class="form-table">
		<tr valign="top">
		<th scope="row">Almost Done Page</th>
		<td><?php wp_dropdown_pages( $args_almost_done ); ?></td>
		</tr>

		<tr valign="top">
		<th scope="row">Thank You Page</th>
		<td><?php wp_dropdown_pages( $args_thank_you ); ?></td>
		</tr>

		<tr valign="top">
		<th scope="row">Already In Page</th>
		<td><?php wp_dropdown_pages( $args_already_in ); ?></td>
		</tr>
	</table>

    <?php submit_button(); ?>
	</form>
	</div>
	<?php
}