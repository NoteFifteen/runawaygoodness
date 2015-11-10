<?php

class InstapageEdit extends instapage
{
	public function init()
	{
		add_action( 'add_meta_boxes', array( &$this, 'addCustomMetaBox' ) );
		add_action( 'save_post', array( &$this, 'saveCustomMeta' ), 10, 2 );
		add_action( 'save_post', array( &$this, 'validateCustomMeta' ), 20, 2 );
	}

	// Add the Meta Box
	public function addCustomMetaBox()
	{
		self::getInstance()->includes[ 'service' ]->silentUpdateCheck();

		add_meta_box
		(
			'instapage_meta_box',
			'Configure your instapage',
			array( &$this, 'showCustomMetaBox' ),
			'instapage_post',
			'normal',
			'high'
		);
	}

	// The Callback
	public function showCustomMetaBox()
	{
		global $post;

		if( !self::getInstance()->includes[ 'main' ]->getUserId() )
		{
			self::getInstance()->includes[ 'admin' ]->error_message = 'You haven\'t connected Instapage account yet. Please go to: <a href="' . INSTAPAGE_PLUGIN_SETTINGS_URI . '">Instapage Settings</a>';
			self::getInstance()->includes[ 'admin' ]->getErrorMessageHTML();
			self::getInstance()->includes[ 'admin' ]->removeEditPage();
			return false;
		}

		// Field Array
		$field = array
		(
			'label' => 'My Page',
			'desc'  => 'Select from your pages.',
			'id'    => 'instapage_my_selected_page',
			'type'  => 'select',
			'options' => array()
		);

		try
		{
			$pages = self::getInstance()->includes[ 'page' ]->loadMyPages();
		}
		catch( Exception $e )
		{
			self::getInstance()->includes[ 'admin' ]->error_message = $e->getMessage();
			self::getInstance()->includes[ 'admin' ]->getErrorMessageHTML();
			self::getInstance()->includes[ 'admin' ]->removeEditPage();
			return false;
		}

		if ( !$pages )
		{
			echo 'N1o pages pushed to your wordpress. Please go to your <a href="http://app.instapage.com/dashboard" target="_blank">Instapage</a> and push some pages.';
			return;
		}

		if ( $pages === false )
		{
			self::getInstance()->includes[ 'admin' ]->error_message = 'You haven\'t published any Instapage page to Wordpress yet';
			self::getInstance()->includes[ 'admin' ]->getErrorMessageHTML();
			self::getInstance()->includes[ 'admin' ]->removeEditPage();
			return false;
		}

		foreach( $pages as $key => $page )
		{
			$field['options'][ $page->id ] = array
			(
				'label' => $page->title,
				'value' => $page->id
			);
		}

		$isFrontPage = self::getInstance()->includes[ 'page' ]->isFrontPage( $post->ID );
		$is_not_found_page = self::getInstance()->includes[ 'page' ]->is404Page( get_the_ID() );
		$meta = get_post_meta( $post->ID, 'instapage_my_selected_page', true );
		$meta_slug = get_post_meta( $post->ID, 'instapage_slug', true );
		$missing_slug = ( self::getInstance()->includes[ 'main' ]->isPageModeActive( 'edit' ) && $meta_slug == '' && !$isFrontPage );

		$delete_link = get_delete_post_link( $post->ID );

		$instapage_post_type = null;
		$redirect_method = 'http';

		if ( $isFrontPage )
		{
			$instapage_post_type = 'home';
		}
		elseif( $is_not_found_page )
		{
			$instapage_post_type = '404';
		}

		$form = self::getInstance()->includes[ 'view' ];
		$form->init( INSTAPAGE_PLUGIN_DIR .'/includes/templates/instapage/edit.php' );
		$form->instapage_post_type = $instapage_post_type;
		$form->user_id = self::getInstance()->includes[ 'main' ]->getUserId();
		$form->field = $field;
		$form->meta = $meta;
		$form->meta_slug = $meta_slug;
		$form->missing_slug = $missing_slug;
		$form->redirect_method = $redirect_method;
		$form->delete_link = $delete_link;
		$form->is_page_active_mode = self::getInstance()->includes[ 'main' ]->isPageModeActive('edit');
		$form->instapage_name = get_post_meta
		(
			$post->ID,
			'instapage_name',
			true
		);

		$form->plugin_file = plugin_basename( __FILE__ );
		echo $form->fetch();
	}

	public function updatePageDetails( $details )
	{
		self::getInstance()->includes[ 'api' ]->instapageApiCall( 'update-page', $details );
	}

	public function saveCustomMeta( $post_id, $post )
	{
		if ( !isset( $_POST[ 'instapage_meta_box_nonce' ] ) || !wp_verify_nonce( $_POST[ 'instapage_meta_box_nonce' ], basename( __FILE__ ) ) )
		{
			// return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		{
			return $post_id;
		}

		if ( $post->post_type != 'instapage_post' )
		{
			return $post_id;
		}

		$old = get_post_meta( $post_id, 'instapage_my_selected_page', true );
		$new = $_POST[ 'instapage_my_selected_page' ];
		$instapage_page_id = $new;
		$instapage_name = $_POST[ 'instapage_name' ];

		$front_page = false;
		$not_found_page = false;

		switch ( $_POST[ 'post-type' ] )
		{
			case '':
			break;

			case 'home':
				$front_page = true;
			break;

			case '404':
				$not_found_page = true;
			break;

			break;
		}

		// HOME PAGE
		$old_front = self::getInstance()->includes[ 'page' ]->getFrontInstapage();

		if ( $front_page )
		{
			$this->setFrontInstapage( $post_id );
		}
		elseif ( $old_front == $post_id )
		{
			$this->setFrontInstapage( false );
		}

		// 404 PAGE
		$old_nf = self::getInstance()->includes[ 'page' ]->get404Instapage();

		if ( $not_found_page )
		{
			$this->set404Instapage( $post_id );
		}
		elseif ( $old_nf == $post_id )
		{
			$this->set404Instapage( false );
		}

		if ( $new && $new != $old )
		{
			update_post_meta( $post_id, 'instapage_my_selected_page', $new );
			update_post_meta( $post_id, 'instapage_name', $instapage_name );
		}

		$this->setPageScreenshot( $instapage_page_id );

		// Custom URL
		$old = get_post_meta( $post_id, 'instapage_slug', true );
		$new = trim( strip_tags( rtrim( $_POST[ 'instapage_slug' ], '/' ) ) );

		if ( $new && $new != $old )
		{
			update_post_meta( $post_id, 'instapage_slug', $new );
		}

		delete_site_transient( 'instapage_page_html_cache_' . $new );

		try
		{
			$this->updatePageDetails
			(
				array
				(
					'user_id' => get_option( 'instapage.user_id' ),
					'plugin_hash' => get_option( 'instapage.plugin_hash' ),
					'page_id' => $_POST[ 'instapage_my_selected_page' ],
					'url' => str_replace( 'http://', '', str_replace( 'https', 'http', get_option( 'siteurl' ) . '/'. rtrim( $_POST[ 'instapage_slug' ], '/' ) ) ),
					'secure' => is_ssl()
				)
			);
		}
		catch( InstapageApiCallException $e )
		{
		}
	}

	public function removeMetaBoxes()
	{
		global $wp_meta_boxes;

		$boxes_for_display = array
		(
			'instapage_meta_box',
			'submitdiv'
		);

		foreach ( $wp_meta_boxes as $k => $v )
		{
			foreach ( $wp_meta_boxes[ $k ] as $j => $u )
			{
				foreach ( $wp_meta_boxes[ $k ][ $j ] as $l => $y )
				{
					foreach ( $wp_meta_boxes[ $k ][ $j ][ $l ] as $m => $y )
					{
						if ( !in_array( $m, $boxes_for_display) )
						{
							unset( $wp_meta_boxes[ $k ][ $j ][ $l ][ $m ] );
						}
					}
				}
			}
		}

		return;
	}

	// Validate the Data
	public function validateCustomMeta( $post_id, $post )
	{

		if ( !isset( $_POST[ 'instapage_meta_box_nonce' ] ) || !wp_verify_nonce( $_POST[ 'instapage_meta_box_nonce' ], basename( __FILE__ ) ) )
		{
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		{
			return $post_id;
		}

		if ( $post->post_type != 'instapage_post' )
		{
			return $post_id;
		}


		$slug = get_post_meta( $post_id, 'instapage_slug' );

		$isFrontPage = self::getInstance()->includes[ 'page' ]->isFrontPage( $post_id );

		$invalid_url = empty( $slug ) && !$isFrontPage;

		// on attempting to publish - check for completion and intervene if necessary
		if ( ( isset( $_POST[ 'publish' ] ) || isset( $_POST[ 'save' ] ) ) && $_POST[ 'post_status' ] == 'publish' )
		{
			// don't allow publishing while any of these are incomplete
			if ( $invalid_url )
			{
				global $wpdb;
				$wpdb->update
				(
					$wpdb->posts,
					array
					(
						'post_status' => 'pending'
					),
					array
					(
						'ID' => $pid
					)
				);
			}
		}
	}

	public function updateMetaValueByInstapagePageId( $instapage_page_id, $meta_key, $meta_value )
	{
		global $wpdb;

		if ( empty( $instapage_page_id ) || empty( $meta_key ) || empty( $meta_value ) )
		{
			return false;
		}

		$post_ids = self::getInstance()->includes[ 'page' ]->getPostIdsByInstapagePageId( $instapage_page_id );

		if ( !$post_ids )
		{
			return false;
		}

		foreach( $post_ids as $post )
		{
			update_post_meta( $post->post_id, $meta_key, $meta_value );
		}
	}

	public function setPageScreenshot( $instapage_page_id )
	{
		$page = self::getInstance()->includes[ 'page' ]->getMyPage( $instapage_page_id );

		if ( !isset( $page->configuration ) )
		{
			return false;
		}

		$page_configuration = unserialize( $page->configuration );

		if ( !isset( $page_configuration->screenshot ) )
		{
			return false;
		}

		$this->updateMetaValueByInstapagePageId( $instapage_page_id, 'instapage_page_screenshot_url', $page_configuration->screenshot );
	}

	public static function setFrontInstapage( $id )
	{
		update_option( 'instapage_front_page_id', $id );
	}

	public static function set404Instapage( $id )
	{
		update_option( 'instapage_404_page_id', $id );
	}

	public static function setRedirectMethod( $val )
	{
		update_option( 'instapage_redirect_method', $val );
	}
}