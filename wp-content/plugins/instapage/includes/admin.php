<?php

class InstapageAdmin extends instapage
{
	var $error_message;

	public function init()
	{
		add_action( 'admin_enqueue_scripts', array( &$this, 'customizeAdministration' ), 11 );
		add_action( 'admin_enqueue_scripts', array( &$this, 'instapagePostUpgradeTasks' ) );
		add_action( 'admin_menu', array( &$this, 'pluginOptionsMenu' ), 11 );
		add_filter( 'plugin_action_links', array( &$this, 'addPluginActionLink' ), 10, 2 );
		add_action( 'init', array( &$this, 'setCrossOriginProxyServicesIfNotExists' ), 10 );
		add_action( 'admin_notices', array( &$this,'instapageAdminNotices' ) );
	}

	public function instapagePostUpgradeTasks()
	{

		$instapage_db_version = floatval( get_option( 'instapage_db_version', 0 ) );
		$instapage_plugin_version = floatval( self::getInstance()->includes[ 'service' ]->pluginGet( 'Version' ) );

		if( $instapage_db_version < $instapage_plugin_version )
		{
			$instapage_posts = self::getInstance()->includes[ 'page' ]->getMyPosts();
			$front_id = self::getInstance()->includes[ 'page' ]->getFrontInstapage();
			$page_404_id = self::getInstance()->includes[ 'page' ]->get404Instapage();

			$success = true;
			$do_update = false;

			foreach( $instapage_posts as $post_id => $instapage_post )
			{
				$do_update = false;
				$instapage_id = InstapageIO::getVar( 'instapage_my_selected_page', 0, $instapage_post );

				if( $instapage_id && self::getInstance()->includes[ 'page' ]->is404Page( $post_id ) && !$this->checkRandomPattern( InstapageIO::getVar( 'instapage_slug', '', $instapage_post ) ) )
				{
					$slug = self::getInstance()->includes[ 'page' ]->getRandomSlug();
					$do_update = true;
				}
				else if( self::getInstance()->includes[ 'page' ]->isFrontPage( $post_id ) && InstapageIO::getVar( 'instapage_slug', '', $instapage_post ) != '' )
				{
					$slug = '';
					$do_update = true;
				}

				if( $do_update )
				{
					try
					{
						self::getInstance()->includes[ 'edit' ]->updatePageDetails
						(
							array
							(
								'user_id' => get_option( 'instapage.user_id' ),
								'plugin_hash' => get_option( 'instapage.plugin_hash' ),
								'page_id' => $instapage_id,
								'url' => str_replace( 'http://', '', str_replace( 'https', 'http', get_option( 'siteurl' ) . '/'. rtrim( $slug, '/' ) ) ),
								'secure' => is_ssl()
							)
						);

						update_post_meta( $post_id, 'instapage_slug', $slug );
					}
					catch( InstapageApiCallException $e )
					{
						InstapageIO::addNotice( $e->getMessage(), 'error' );
						$success = false;
					}
				}
			}

			if( $success )
			{
				update_option( 'instapage_db_version', $instapage_plugin_version );
			}
			else
			{
				InstapageIO::addNotice( sprintf( __( 'There was an error during automatic page update. Try refreshing this page, and contact our <a target="_blank" href="%s">Customer Support</a> team if the problem persists.' ), esc_url( self::instapage_support_link ) ), 'error' );
			}
		}
	}

	private function checkRandomPattern( $slug  = '' )
	{

		$prefix = InstapagePage::RANDOM_PREFIX;
		$sufix_length = InstapagePage::RANDOM_SUFIX_LENGTH;
		$sufix_set = InstapagePage::RANDOM_SUFIX_SET;
		$pattern = '/^' . $prefix . '[' . $sufix_set . ']{' . $sufix_length . '}$/';

		return preg_match( $pattern, $slug );
	}

	public function instapageAdminNotices()
	{
		$notices = get_option( 'instapage_notices' );

		if ( !empty( $notices ) )
		{
			foreach ( $notices as $notice )
			{
				echo $notice;
			}

			delete_option( 'instapage_notices' );
		}
	}

	public function setCrossOriginProxyServicesIfNotExists()
	{
		$cross_origin_proxy_services = get_option( 'instapage.cross_origin_proxy_services' );

		if ( $cross_origin_proxy_services === false )
		{
			update_option( 'instapage.cross_origin_proxy_services', 1 );
		}
	}

	public function redirection( $location )
	{
		echo '<script>instapage_redirection( "' . $location . '" );</script>';
	}

	public function removeEditPage()
	{
		echo '<script>instapage_remove_edit();</script>';
	}

	public function showMessage( $not_error, $message )
	{
		$this->error_message = $message;

		if ( $not_error )
		{
			add_action( 'admin_notices', array( &$this, 'getMessageHTML' ) );
		}
		else
		{
			add_action( 'admin_notices', array( &$this, 'getErrorMessageHTML' ) );
		}
	}

	public function getErrorMessageHTML()
	{
		$form = self::getInstance()->includes[ 'view' ];
		$form->init( INSTAPAGE_PLUGIN_DIR .'/includes/templates/instapage/error.php' );
		$form->error_class = 'error';
		$form->msg = $this->error_message;
		echo $form->fetch();
	}

	public function getMessageHTML()
	{
		$form = self::getInstance()->includes[ 'view' ];
		$form->init( INSTAPAGE_PLUGIN_DIR .'/includes/templates/instapage/error.php' );
		$form->error_class = 'updated';
		$form->msg = $this->error_message;
		echo $form->fetch();
	}

	public function customizeAdministration()
	{
		global $post_type;
	}

	public function getUrlVersion()
	{
		return '?url-version=' . instapage::getInstance()->includes[ 'service' ]->pluginGet( 'Version' );
	}

	public function showSettingsPage()
	{
		$user_id = get_option( 'instapage.user_id' );
		$form = instapage::getInstance()->includes[ 'view' ];
		$form->init( INSTAPAGE_PLUGIN_DIR .'/includes/templates/instapage/settings.php' );
		$form->plugin_file = plugin_basename( INSTAPAGE_PLUGIN_FILE );
		$form->user_id = $user_id;
		$form->error = false;

		if( $_POST && !$user_id )
		{
			try
			{
				$response = instapage::getInstance()->includes[ 'api' ]->instapageApiCall
				(
					'user-login',
					array
					(
						'email' => base64_encode( trim( $_POST[ 'email' ] ) ),
						'password' => base64_encode( trim( $_POST[ 'password' ] ) )
					)
				);
			}
			catch( InstapageApiCallException $e )
			{
				$form->error = $e->getMessage();
			}

			if( $response->error )
			{
				$form->error = $response->error_message;
			}

			if( $response->success )
			{
				add_option( 'instapage.user_id', false );
				add_option( 'instapage.plugin_hash', false );
				update_option( 'instapage.user_id', $response->data[ 'user_id' ] );
				update_option( 'instapage.plugin_hash', $response->data[ 'plugin_hash' ] );
				$user_id = $form->user_id = $response->data[ 'user_id' ];
			}
		}

		if( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'disconnect' )
		{
			update_option( 'instapage.user_id', false );
			update_option( 'instapage.plugin_hash', false );
			$form->user_id = null;
		}

		if ( isset( $_POST[ 'action' ] ) && $_POST[ 'action' ] == 'cross_origin_proxy_services' )
		{
			update_option( 'instapage.cross_origin_proxy_services', $_POST[ 'cross_origin_proxy_services' ] );
		}

		$form->cross_origin_proxy_services = get_option( 'instapage.cross_origin_proxy_services' );

		if( $user_id )
		{
			try
			{
				$response = instapage::getInstance()->includes[ 'api' ]->instapageApiCall
				(
					"get_user/?id=" . $user_id . "&plugin_hash=" . get_option( 'instapage.plugin_hash' ),
					array
					(
						'user_id' => $user_id,
						'plugin_hash' => get_option( 'instapage.plugin_hash' )
					)
				);

				$form->user = $response->user;
			}
			catch( InstapageApiCallException $e )
			{
				$form->error = $e->getMessage();
			}
		}

		echo $form->fetch();
	}

	public function pluginOptionsMenu()
	{
		add_options_page( 'Instapage', 'Instapage', 'administrator', INSTAPAGE_PLUGIN_FILE, array( &$this, 'showSettingsPage' ) );
	}

	/**
	 * Add a link to the settings page from the plugins page
	 */
	public function addPluginActionLink( $links, $file )
	{
		static $this_plugin;

		if( empty( $this_plugin ) ) $this_plugin = plugin_basename( INSTAPAGE_PLUGIN_FILE );

		if ( $file == $this_plugin )
		{
			$settings_link = '<a href="' . admin_url( 'options-general.php?page=' . $this_plugin ) . '">' . __('Settings', 'Instapage') . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	public function formatError( $msg )
	{
		$form = self::getInstance()->includes[ 'view' ];
		$form->init( INSTAPAGE_PLUGIN_DIR .'/includes/templates/instapage/error-formatted.php' );
		$form->msg = $msg;
		return $form->fetch();
	}
}