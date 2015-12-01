<?php

class InstapageEdit extends instapage
{
	const UPDATE_OK = 1;
	const UPDATE_FAILED = 2;

	public function init()
	{
		add_action( 'add_meta_boxes', array( &$this, 'addCustomMetaBox' ) );
		add_action( 'save_post', array( &$this, 'saveCustomMeta' ), 1, 2 );
		add_action( 'save_post', array( &$this, 'validateCustomMeta' ), 20, 2 );
		add_action( 'wp_trash_post', array( &$this, 'trashInstapagePost' ) );
		add_filter( 'post_updated_messages', array( &$this, 'instapagePostUpdatedMessage' ), 1, 1 );
		add_action( 'load-edit.php', array( &$this, 'instapageCleanup') );
		add_action( 'init', array( &$this, 'registerInvalidPosttype' ) );
		add_filter( 'bulk_actions-edit-instapage_post', array(&$this, 'removeBulkActions' ) );
	}

	public function removeBulkActions( $actions )
	{
		unset( $actions[ 'untrash'] );
		unset( $actions[ 'edit'] );
		unset( $actions[ 'trash'] );

		return $actions;
	}

	public  function registerInvalidPosttype()
	{
		$args = array(
			'label' => __( 'Instapage invalid' ),
			'public' => false,
			'exclude_from_search' => true,
			'show_in_admin_all_list' => false,
			'show_in_admin_status_list' => false
		);

		register_post_status( 'instapage_invalid', $args );
	}

	public function instapageCleanup()
	{
		$post_type = InstapageIO::getVar( 'post_type', 'post', 'request' );

		if( $post_type != 'instapage_post' )
		{
			return;
		}

		global $wpdb;

		$sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'instapage_post' AND post_status = 'instapage_invalid'";
		$results = $wpdb->get_results( $sql, OBJECT );

		foreach( $results as $result )
		{
			wp_delete_post( $result->ID, true );
		}
	}

	public function instapagePostUpdatedMessage( $messages )
	{
		global $post;

		$post_url = self::getInstance()->includes[ 'page' ]->getPageUrl( $post->ID );

		if( $this->getUpdateStatus() != self::UPDATE_OK )
		{
			$messages[ 'instapage_post' ] = array(
				0 => '',
				1 => '',
				2 => '',
				3 => '',
				4 => '',
				5 => '',
				6 => '',
				7 => '',
				8 => '',
				9 => '',
				10 => '',
			);
		}
		else
		{
			$messages[ 'instapage_post' ] = array(
				0 => '',
				1 => sprintf( __( 'Page updated. <a target="_blank"href="%s">View page</a>' ), esc_url( $post_url ) ),
				2 => '',
				3 => '',
				4 => __( 'Page updated.' ),
				5 => '',
				6 => sprintf( __( 'Page published. <a target="_blank" href="%s">View page</a>' ), esc_url( $post_url ) ),
				7 => __( 'Page saved.' ),
				8 => sprintf( __( 'Page submitted. <a target="_blank" href="%s">Preview page</a>' ), esc_url( add_query_arg( 'preview', 'true', $post_url ) ) ),
				9 => '',
				10 => '',
			);
		}

		return $messages;
	}

	public function trashInstapagePost( $post_id )
	{
		global $post;

		if ( $post->post_type != 'instapage_post' )
		{
			return $post_id;
		}

		$page_id = get_post_meta( $post->ID, 'instapage_my_selected_page', true );

		$data = array
		(
			'user_id' => get_option( 'instapage.user_id' ),
			'plugin_hash' => get_option( 'instapage.plugin_hash' ),
			'page_id' => $page_id,
			'url' => '',
			'secure' => is_ssl()
		);

		try
		{
			$this->updatePageDetails( $data );
		}
		catch( InstapageApiCallException $e )
		{
			error_log( $e->getMessage() );
		}
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
			self::getInstance()->includes[ 'admin' ]->error_message = __( sprintf( 'You haven\'t connected Instapage account yet. Please go to: <a href="%s">Instapage Settings</a>', INSTAPAGE_PLUGIN_SETTINGS_URI ) );
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
			echo __( 'No pages pushed to your wordpress. Please go to your <a href="http://app.instapage.com/dashboard" target="_blank">Instapage</a> and push some pages.' );
			return;
		}

		if ( $pages === false )
		{
			self::getInstance()->includes[ 'admin' ]->error_message = __( 'You haven\'t published any Instapage page to Wordpress yet' );
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
		$new = InstapageIO::getVar( 'instapage_my_selected_page', 0, 'post' );
		$instapage_page_id = $new;
		$instapage_name = InstapageIO::getVar( 'instapage_name', '', 'post' );
		$instapage_post_type = InstapageIO::getVar( 'post-type', '', 'post' );
		$instapage_slug = InstapageIO::getVar( 'instapage_slug', '', 'post' );

		$front_page = false;
		$not_found_page = false;

		switch ( $instapage_post_type )
		{
			case '':
			break;

			case 'home':
				$front_page = true;
				$instapage_slug = $_POST[ 'instapage_slug' ] = '';
			break;

			case '404':
				$not_found_page = true;
				$instapage_slug = $_POST[ 'instapage_slug' ] = self::getInstance()->includes[ 'page' ]->getRandomSlug();
			break;

			default:
			break;
		}

		if( !$this->checkPageData() )
		{
			$this->setUpdateStatus( self::UPDATE_FAILED );

			return $post_id;
		}

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
			$new = trim( strip_tags( rtrim( $instapage_slug, '/' ) ) );

			if ( $new != $old )
			{
				update_post_meta( $post_id, 'instapage_slug', $new );
			}

			delete_site_transient( 'instapage_page_html_cache_' . $new );
			$this->setUpdateStatus( self::UPDATE_OK );
		}
		catch( InstapageApiCallException $e )
		{
			$this->setUpdateStatus( self::UPDATE_FAILED );
			InstapageIO::addNotice( __( 'Page could not be updated. ' ), 'error' );
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
		$post_status = InstapageIO::getVar( 'post_status', null, 'post' );

		// on attempting to publish - check for completion and intervene if necessary
		if ( ( isset( $_POST[ 'publish' ] ) || isset( $_POST[ 'save' ] ) ) && ( $post_status == 'publish' || $post_status == 'instapage_invalid' ) )
		{
			// don't allow publishing while any of these are incomplete
			$status = null;
			if ( $invalid_url || $this->getUpdateStatus() != self::UPDATE_OK )
			{
				$status = 'instapage_invalid';
			}
			else
			{
				$status = 'publish';
			}

			global $wpdb;

			$wpdb->update
			(
				$wpdb->posts,
				array
				(
					'post_status' => $status
				),
				array
				(
					'ID' => $post_id
				)
			);
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

	public function checkPageData( $on_save_only = true, $add_notices = true )
	{

		if ( !$this->isSavePerformed() && $on_save_only )
		{
			return true;
		}

		global $post;

		$instapage_page_id = InstapageIO::getVar( 'instapage_my_selected_page', 0, 'post' );
		$instapage_name = InstapageIO::getVar( 'instapage_name', '', 'post' );
		$instapage_post_type = InstapageIO::getVar( 'post-type', '', 'post' );
		$instapage_slug = InstapageIO::getVar( 'instapage_slug', '', 'post' );
		$success = true;

		switch( $instapage_post_type )
		{
			//Normal Page
			case '':
				//check if url is correct
				$page_url = self::getInstance()->includes[ 'page' ]->getPageUrl( false, $instapage_slug );

				if( filter_var( $page_url, FILTER_VALIDATE_URL ) === false )
				{
					if( $add_notices )
					{
						InstapageIO::addNotice( '<strong>' . __( 'Custom URL' ) . '</strong>' . __( ' is incorrect, please use valid URL for that field.' ), 'error' );
					}

					$success = false;
				}

				//check if no ditectory exists
				$test_path = get_home_path() . $instapage_slug;

				if( is_dir( $test_path ) )
				{
					if( $add_notices )
					{
						InstapageIO::addNotice( sprintf( '<strong>' . __( 'Custom URL' ) . '</strong>' . __( ' is incorrect, it leads to an existing directory (%s).' ), $test_path ), 'error' );
					}

					$success = false;
				}

				//check if url is avalible
				$wp_post_id = $this->getPostIdByUrl( $page_url );

				if( $wp_post_id && $wp_post_id != $post->ID )
				{
					if( $add_notices )
					{
						$wp_post_edit_url = get_edit_post_link( $wp_post_id );
						InstapageIO::addNotice( sprintf( __( 'Selected <strong>Custom URL</strong> (%s) is already in use. You can <a href="%s">edit the post</a> and change permalink or change custom Instapage URL.' ), $page_url, $wp_post_edit_url ), 'error' );
					}

					$success = false;
				}

				if( $success )
				{
					$result = wp_remote_head( $page_url );

					if( !is_wp_error( $result ) )
					{
						$response_code = wp_remote_retrieve_response_code( $result );

						if( $response_code >= 300 && $response_code < 400 && isset( $result[ 'headers' ][ 'location' ] ) )
						{
							$result = wp_remote_head( $result[ 'headers' ][ 'location' ] );

							if( !is_wp_error( $result ) )
							{
								$response_code = wp_remote_retrieve_response_code( $result );
							}
						}

						if( $response_code != 404 )
						{
							InstapageIO::addNotice( '<strong>' . __( 'Custom URL' ) . '</strong>' . __( ' is incorrect, it leads to an existing page.' ), 'error' );
							$success = false;
						}
					}
				}

			break;

			case '404':
				//check if url is avalible
				$page_url = self::getInstance()->includes[ 'page' ]->getPageUrl( false, $instapage_slug );
				$wp_post_id = $this->getPostIdByUrl( $page_url );

				if( $wp_post_id && $wp_post_id != $post->ID )
				{
					if( $add_notices )
					{
						InstapageIO::addNotice( __( 'Instapage plugin has generated random page slug during save process, but it appears to be taken. Please try publishing the page once again to generate another page slug.' ), 'error' );
					}

					$success = false;
				}

			break;

			case 'home':

				if( $instapage_slug != '' )
				{
					if( $add_notices )
					{
						InstapageIO::addNotice( __( 'There was a problem during update. Please make sure that you have JavaScript enabled and try again.' ), 'error' );
					}

					$success = false;
				}

			break;
		}

		return $success;
	}

	public function setUpdateStatus( $status = self::UPDATE_OK )
	{
		update_option( 'instapage_last_save_status', $status );
	}

	public function getUpdateStatus()
	{
		return get_option( 'instapage_last_save_status' , 'undefined' );
	}

	private function isSavePerformed()
	{
		if ( ( isset( $_POST[ 'publish' ] ) || isset( $_POST[ 'save' ] ) ) && InstapageIO::getVar( 'post_status', '', 'post' ) == 'publish' )
		{
			return true;
		}
	}

	public function getPostIdByUrl( $url, $post_id = null, &$is_post = null)
	{
		//check if page or post with the same URL exist in WP
		$wp_post_id = url_to_postid( $url );

		if( $wp_post_id )
		{
			if( isset( $is_post ) )
			{
				$is_post = true;
			}

			return $wp_post_id;
		}

		//check if page with the same URL exist in Instapage plugin
		global $wpdb;

		$instapage_slug = trim( str_replace( site_url(), '', $url ), '/' );
		$sql = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'instapage_slug' AND meta_value = '%s'", $instapage_slug );
		$results = $wpdb->get_results( $sql, ARRAY_A );

		if( !empty( $results ) )
		{
			if( isset( $is_post ) )
			{
				$is_post = false;
			}

			return isset( $results[ 0 ][ 'post_id' ] ) ? $results[ 0 ][ 'post_id' ] : 0;
		}

		return 0;
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