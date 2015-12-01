<?php
class InstapageLogException extends Exception
{
}

class InstapageLog extends instapage
{
	var $logTableName = '';

	function __construct()
	{
		global $wpdb;
		$this->logTableName = $wpdb->prefix . 'instapage_log';
	}

	public function write( $value, $add_caller = true )
	{
		global $wpdb;

		try
		{
			$this->checkLogTable();

			if ( is_array( $value ) || is_object( $value ) )
			{
				$value = print_r( $value, true );
			}

			$caller = '';

			if( $add_caller )
			{
				$trace = debug_backtrace();
				$caller = isset( $trace[ 1 ] ) ? $trace[ 1 ] : null;
				$caller_function = isset( $caller[ 'function' ] ) ? $caller[ 'function' ] : null;

				if( $caller_function ==  'writeLog' )
				{
					$caller = isset( $trace[ 2 ] ) ? $trace[ 2 ] : null;
				}

				$caller_function = isset( $caller[ 'function' ] ) ? $caller[ 'function' ] : null;
				$caller_class = isset( $caller[ 'class' ] ) ? $caller[ 'class' ] . ' :: ' : null;
				$caller = $caller_class . $caller_function;
			}

			$data = array(
				'id' => 'NULL',
				'time' => current_time( 'mysql' ),
				'text' => $value,
				'caller' => $caller
			);

			$wpdb->insert( $this->logTableName, $data );
		}
		catch ( Exception $e )
		{
			echo $e->getMessage();
		}
	}

	public function clear()
	{
		try
		{
			if( $this->checkLogTable( false ) )
			{
				global $wpdb;

				$sql = "DELETE FROM $this->logTableName";
				$wpdb->query( $sql );
			}
		}
		catch ( Exception $e )
		{
			echo $e->getMessage();
		}
	}

	private function checkLogTable( $create = true )
	{
		global $wpdb;

		if( $wpdb->get_var( "SHOW TABLES LIKE '$this->logTableName'" ) == $this->logTableName )
		{
			return true;
		}
		else
		{
			if( $create )
			{
				$result = null;
				$charset_collate = $wpdb->get_charset_collate();
				$sql = "CREATE TABLE $this->logTableName(
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					text text NOT NULL,
					caller varchar(255) DEFAULT '' NOT NULL,
					UNIQUE KEY id (id)
					) $charset_collate;";

				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

				try
				{
					$result = dbDelta( $sql );

					if( empty( $result ) )
					{
						throw new InstapageLogException( __( "Couldn't create {$this->logTableName} table" ) );
					}
				}
				catch( Exception $e)
				{
					throw new InstapageLogException( $e->getMessage );
				}
			}
		}
	}
}
