<?php
class InstapageIO
{
	//source = post | get | request | server | cookie | session | globals
	public static function getVar( $value, $default = null, $source = null )
	{
		$ret = null;

		if( $source !== null )
		{
			switch( $source )
			{
				case 'request':
					$ret = isset( $_REQUEST[ $value ] ) ? $_REQUEST[ $value ] : $default;
				break;
				case 'get':
					$ret = isset( $_GET[ $value ] ) ? $_GET[ $value ] : $default;
				break;
				case 'post':
					$ret = isset( $_POST[ $value ] ) ? $_POST[ $value ] : $default;
				break;
				case 'server':
					$ret = isset( $_SERVER[ $value ] ) ? $_SERVER[ $value ] : $default;
				break;
				case 'cookie':
					$ret = isset( $_COOKIE[ $value ] ) ? $_COOKIE[ $value ] : $default;
				break;
				case 'session':
					$ret = isset( $_SESSION[ $value ] ) ? $_SESSION[ $value ] : $default;
				break;
				case 'globals':
					$ret = isset( $GLOBALS[ $value ] ) ? $GLOBALS[ $value ] : $default;
				break;
				default:

					if( is_array( $source ) && isset( $source[ $value ] ) )
					{
						$ret = $source[ $value ];
					}
					else
					{
						$ret = $default;
					}

				break;
			}
		}
		else
		{
			$ret = isset( $value ) ? $value : $default;
		}

		return $ret;
	}

	//$level = updated | update-nag | error
	public static function addNotice( $notice, $level = 'updated' )
	{
		$instapage = instapage::getInstance();
		$form = $instapage->includes[ 'view' ];

		$form->init( INSTAPAGE_PLUGIN_DIR .'/includes/templates/instapage/notice.php' );
		$form->level = $level;
		$form->notice = $notice;

		$notices = get_option( 'instapage_notices', array() );
		$notices[] = $form->fetch();

    	return update_option( 'instapage_notices', $notices );
	}

	public static function writeLog( $value )
	{
		$instapage = instapage::getInstance();
		$instapage->includes[ 'log' ]->write( $value );
	}
}
