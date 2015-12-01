<?php

class InstapageIndex extends instapage
{
	public function init()
	{
		if ( isset( $_GET[ 'post_type' ] ) && $_GET[ 'post_type' ] == 'instapage_post' )
		{
			add_filter( 'manage_edit-instapage_post_columns', array( &$this, 'editPostsColumns' ) );
			add_action( 'manage_posts_custom_column', array( &$this, 'populateColumns' ) );
			add_filter( 'post_row_actions', array( &$this, 'removeQuickEdit' ), 10, 2 );
		}
	}

	public function editPostsColumns( $columns )
	{
		$cols = array();
		$cols[ 'cb' ] = $columns[ 'cb' ];
		$cols[ 'instapage_post_preview' ] = __( 'Preview', 'instapage' );
		$cols[ 'instapage_post_name' ] = __( 'Landing Page Title', 'instapage' );
		$cols[ 'instapage_post_stats' ] = '<span class="instapage-variation-stats-column-text">' . __( 'Variation Testing Stats', 'instapage' ) . '</span> <a href="#" class="instapage-hide-stats">(Hide Stats)</a>';
		$cols[ 'instapage_post_visits' ] = __( 'Visits', 'instapage' );
		$cols[ 'instapage_post_conversions' ] = __( 'Conversions', 'instapage' );
		$cols[ 'instapage_post_conversion_rate' ] = __( 'Conversion Rate', 'instapage' );

		return $cols;
	}

	public function populateColumns( $column )
	{
		if( !self::getInstance()->includes[ 'main' ]->getUserId() )
		{
			self::getInstance()->includes[ 'admin' ]->error_message = 'You haven\'t connected Instapage account yet. Please go to: <a href="' . INSTAPAGE_PLUGIN_SETTINGS_URI . '">Instapage Settings</a>';
			self::getInstance()->includes[ 'admin' ]->getErrorMessageHTML();
			return false;
		}

		$post_id = get_the_ID();
		$instapage_slug = get_post_meta( $post_id, 'instapage_slug', true );
		$page_url = self::getInstance()->includes[ 'page' ]->getPageUrl( $post_id );
		$page_preview = self::getInstance()->includes[ 'page' ]->getPageScreenshot( $post_id );
		$page_edit_url = self::getInstance()->includes[ 'page' ]->getPageEditUrl( $post_id );
		$page_name = self::getInstance()->includes[ 'page' ]->getPageName( $post_id );
		$delete_link = get_delete_post_link( $post_id, null, true );

		$page_stats = self::getInstance()->includes[ 'page' ]->getPageStats( $post_id );
		$additional_class = '';

		switch ( $column )
		{
			case 'instapage_post_preview':
				if ( !empty( $page_preview ) )
				{
					echo '<a href="' . $page_url . '" target="_blank"><img class="instapage-post-preview-image" src="' . $page_preview . '" /></a>';
				}
				else
				{
					echo '<img class="instapage-post-preview-image" src="' . INSTAPAGE_PLUGIN_URI . '/assets/img/wordpress-thumb.jpg" />';
				}
				break;

			case 'instapage_post_name':
				$wp_post_id = url_to_postid( $page_url );

				if( $wp_post_id )
				{
					$wp_post_edit_url = get_edit_post_link( $wp_post_id );
					echo '<div class="error">' . sprintf( __( '<p>Instapage URL (<a href="%s">%s</a>) is duplicated. Instapage plugin will override post settings, Instapage will be displayed.</p><p>To avoid permalink overriding <a href="%s">edit the post</a> and change permalink or <a href="%s">edit Instapage</a> and change custom URL.' ), $page_url, $page_url, $wp_post_edit_url, $page_edit_url ) . '</p></div>';
					$additional_class = ' instapage-warning ';

				}

				$test_path = get_home_path() . $instapage_slug;

				if( $instapage_slug != '' && is_dir( $test_path ) )
				{
					echo '<div class="error"><p>' . sprintf( '<strong>' . __( 'Custom URL' ) . '</strong>' . __( ' is incorrect, it leads to an existing directory (%s). <a href="%s">Edit Instapage</a> and change custom URL to prevent 403 server error. ' ), $test_path, $page_edit_url ) . '</p></div>';
					$additional_class = ' instapage-warning ';
				}

				echo '<div class="instapage-post-name ' . $additional_class . '"><strong><a href="' . $page_edit_url .'">' .  $page_name . '</a></strong></div>';
				echo '<div class="instapage-post-url">Landing Page URL: <a href="' . $page_url . '" target="_blank">' . $page_url . '</a></div>';
				echo '<div class="instapage-delete"><a class="submitdelete" href="' . $delete_link . '">' . __( 'Delete from WP' ) . '</a></div>';
				break;

			case 'instapage_post_stats':
				$view = self::getInstance()->includes[ 'view' ];
				$view->init( INSTAPAGE_PLUGIN_DIR .'/includes/templates/instapage/index_page_stats.php' );
				$view->page_stats = $page_stats;
				echo $view->fetch();
				break;

			case 'instapage_post_visits':
				echo $page_stats[ 'visits' ];
				break;

			case 'instapage_post_conversions':
				echo $page_stats[ 'conversions' ];
				break;

			case 'instapage_post_conversion_rate':
				echo self::getInstance()->includes[ 'page' ]->calcAvarageConversion( $page_stats[ 'visits' ], $page_stats[ 'conversions' ] ) . '%';
				break;

			default:
				break;
		}
	}

	public function removeQuickEdit( $actions )
	{
		global $post;

		if ( $post->post_type == 'instapage_post' )
		{
			unset( $actions[ 'inline hide-if-no-js' ] );
		}

		return $actions;
	}
}