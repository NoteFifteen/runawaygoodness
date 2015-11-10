<?php

class InstapageMain extends instapage
{
	public function init()
	{
		add_action( 'init', array( &$this, 'instapagePostRegister' ) );
		add_filter( 'display_post_states', array( &$this, 'customPostState' ) );
	}

	public function instapagePostRegister()
	{
		instapage::getInstance()->includes[ 'service' ]->silentUpdateCheck();

		$labels = array
		(
			'name'					=> _x( 'Instapage', 'Post type general name', 'instapage' ),
			'singular_name'			=> _x( 'Instapage', 'Post type singular name', 'instapage' ),
			'add_new'				=> _x( 'Add New', 'instapage', 'instapage' ),
			'add_new_item'			=> __( 'Add New Instapage', 'instapage' ),
			'edit_item'				=> __( 'Edit Instapage', 'instapage' ),
			'new_item'				=> __( 'New Instapage', 'instapage' ),
			'view_item'				=> __( 'View Instapage', 'instapage' ),
			'search_items'			=> __( 'Search Instapage', 'instapage' ),
			'not_found'				=> __( 'Nothing found', 'instapage' ),
			'not_found_in_trash'	=> __( 'Nothing found in Trash', 'instapage' ),
			'parent_item_colon'		=> ''
		);

		$args = array
		(
			'labels'				=> $labels,
			'description'			=> __( 'Allows you to have Instapage on your WordPress site.', 'instapage' ),
			'public'				=> false,
			'publicly_queryable'	=> true,
			'show_ui'				=> true,
			'query_var'				=> true,
			'menu_icon'				=> INSTAPAGE_PLUGIN_URI . 'assets/img/instapage-logo-16x16.png',
			'capability_type'		=> 'page',
			'menu_position'			=> null,
			'rewrite'				=> false,
			'can_export'			=> false,
			'hierarchical'			=> false,
			'has_archive'			=> false,
			'supports'				=> array( 'instapage_my_selected_page', 'instapage_slug', 'instapage_name', 'instapage_url' ),
			'register_meta_box_cb'	=> array( &instapage::getInstance()->includes[ 'edit' ], 'removeMetaBoxes' )
		);

		register_post_type( 'instapage_post', $args );
	}

	public static function getUserId()
	{
		return get_option( 'instapage.user_id' );
	}

	public function customPostState( $states )
	{
		global $post;

		$show_custom_state = null !== get_post_meta( $post->ID, 'instapage_my_selected_page' );

		if ( $show_custom_stat )
		{
			$states = array();
		}

		return $states;
	}

	public static function isPageModeActive( $new_edit = null )
	{
		global $pagenow;

		// make sure we are on the backend
		if ( !is_admin() )
		{
			return false;
		}

		if ( $new_edit == "edit" )
		{
			return in_array( $pagenow, array( 'post.php' ) );
		}
		elseif ( $new_edit == "new" )
		{
			// check for new post page
			return in_array( $pagenow, array( 'post-new.php' ) );
		}
		else
		{
			// check for either new or edit
			return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
		}
	}
}