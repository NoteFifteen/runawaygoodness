<?php

class InstapageAdmin extends instapage
{
	var $error_message;

	public function init()
	{
		add_action( 'admin_enqueue_scripts', array( &$this, 'customizeAdministration' ), 11 );
		add_action( 'admin_menu', array( &$this, 'pluginOptionsMenu' ), 11 );
		add_filter( 'plugin_action_links', array( &$this, 'addPluginActionLink' ), 10, 2 );
		add_action( 'init', array( &$this, 'setCrossOriginProxyServicesIfNotExists' ), 10 );
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