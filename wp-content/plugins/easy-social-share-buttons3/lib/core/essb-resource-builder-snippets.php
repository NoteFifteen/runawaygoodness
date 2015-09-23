<?php

/**
 * Generate predefined CSS and javascript code snippets based on settings
 *
 */
class ESSBResourceBuilderSnippets {
	
	public static $snippet;
	
	/*
	 * Code pattern snippet builder
	 */
	public static function snippet_start() {
		self::$snippet = array();
	}
	
	public static function snippet_add($code) {
		self::$snippet[] = $code;
	}
	
	public static function snippet_end() {
		return implode(" ", self::$snippet);
	}
	
	/*
	 * end: Code pattern snippet builder
	 */
	
	/*
	 * --------------------------------------------------------------
	 * CSS
	 * --------------------------------------------------------------
	 */
	
	public static function css_build_animation_code($animation) {
		self::snippet_start();
		
		$singleTransition = '.essb_links a { -webkit-transition: all 0.2s linear;-moz-transition: all 0.2s linear;-ms-transition: all 0.2s linear;-o-transition: all 0.2s linear;transition: all 0.2s linear;}';
		self::snippet_add($singleTransition);
		
		switch ($animation) {
			case "pop":
				self::snippet_add('.essb_links a:hover {transform: translateY(-5px);-webkit-transform:translateY(-5px);-moz-transform:translateY(-5px);-o-transform:translateY(-5px); }');
				break;
			case "zoom":
				self::snippet_add('.essb_links a:hover {transform: scale(1.2);-webkit-transform:scale(1.2);-moz-transform:scale(1.2);-o-transform:scale(1.2); }');
				break;
			case "flip":
				self::snippet_add('.essb_links a:hover {transform: rotateZ(360deg);-webkit-transform:rotateZ(360deg);-moz-transform:rotateZ(360deg);-o-transform:rotateZ(360deg); }');
				break;		
		}		
		return self::snippet_end();
	}
	
	public static function css_build_counter_style() {
		global $essb_options;
		self::snippet_start();
		$options = $essb_options;
		$activate_total_counter_text = isset($options['activate_total_counter_text']) ? $options['activate_total_counter_text'] : '';
		
		if ($activate_total_counter_text != '') {
			self::snippet_add('.essb_links_list li.essb_totalcount_item .essb_t_l_big .essb_t_nb:after, .essb_links_list li.essb_totalcount_item .essb_t_r_big .essb_t_nb:after { '.
					'color: #777777;'.
					'content: "'.$activate_total_counter_text.'";'.
					'display: block;'.
					'font-size: 11px;'.
					'font-weight: normal;'.
					'text-align: center;'.
					'text-transform: uppercase;'.
					'margin-top: -5px; } ');
			
			self::snippet_add('.essb_links_list li.essb_totalcount_item .essb_t_l_big, .essb_links_list li.essb_totalcount_item .essb_t_r_big { text-align: center; }');
			self::snippet_add('.essb_displayed_sidebar .essb_links_list li.essb_totalcount_item .essb_t_l_big .essb_t_nb:after, .essb_displayed_sidebar .essb_links_list li.essb_totalcount_item .essb_t_r_big .essb_t_nb:after { '.					
					'margin-top: 0px; } ');
			self::snippet_add('.essb_displayed_sidebar_right .essb_links_list li.essb_totalcount_item .essb_t_l_big .essb_t_nb:after, .essb_displayed_sidebar_right .essb_links_list li.essb_totalcount_item .essb_t_r_big .essb_t_nb:after { '.					
					'margin-top: 0px; } ');
		}
		
		self::snippet_add('.essb_totalcount_item_before, .essb_totalcount_item_after { display: block !important; }');
		self::snippet_add('.essb_totalcount_item_before .essb_totalcount, .essb_totalcount_item_after .essb_totalcount { border: 0px !important; }');
		self::snippet_add('.essb_counter_insidebeforename { margin-right: 5px; font-weight: bold; }');
		return self::snippet_end();
	}
	
	public static function css_build_morepopup_css() {
		self::snippet_start();
		
		self::snippet_add('.essb_morepopup_shadow {position:fixed;
	_position:absolute; /* hack for IE 6*/
	height:100%;
	width:100%;
	top:0;
	left:0;
	background: rgba(33, 33, 33, 0.85);
	z-index:100000;
	display: none; }');
		
		self::snippet_add('.essb_morepopup { 	background-color: #ffffff;
	z-index: 100001;
	-webkit-box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
	-moz-box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
	-ms-box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
	-o-box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
	box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
	display: none;
	color: #111;
	-webkit-border-radius: 5px;
-moz-border-radius: 5px;
border-radius: 5px;}');
		
		self::snippet_add('.essb_morepopup_content { padding: 15px;
	margin: 0;
	text-align: center;}');
		
		self::snippet_add('.essb_morepopup_content .essb_links a { text-align: left; }');
		
		self::snippet_add('.essb_morepopup_close { width:12px;
    height:12px;
    display:inline-block;
    position:absolute;
    top:10px;
    right:10px;
    -webkit-transition:all ease 0.50s;
    transition:all ease 0.75s;
	font-weight:bold;
    text-decoration:none;
    color:#111;
    line-height:160%;
	font-size:24px;
	background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/PjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+PHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB3aWR0aD0iNDEuNzU2cHgiIGhlaWdodD0iNDEuNzU2cHgiIHZpZXdCb3g9IjAgMCA0MS43NTYgNDEuNzU2IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA0MS43NTYgNDEuNzU2OyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PGc+PHBhdGggZD0iTTI3Ljk0OCwyMC44NzhMNDAuMjkxLDguNTM2YzEuOTUzLTEuOTUzLDEuOTUzLTUuMTE5LDAtNy4wNzFjLTEuOTUxLTEuOTUyLTUuMTE5LTEuOTUyLTcuMDcsMEwyMC44NzgsMTMuODA5TDguNTM1LDEuNDY1Yy0xLjk1MS0xLjk1Mi01LjExOS0xLjk1Mi03LjA3LDBjLTEuOTUzLDEuOTUzLTEuOTUzLDUuMTE5LDAsNy4wNzFsMTIuMzQyLDEyLjM0MkwxLjQ2NSwzMy4yMmMtMS45NTMsMS45NTMtMS45NTMsNS4xMTksMCw3LjA3MUMyLjQ0LDQxLjI2OCwzLjcyMSw0MS43NTUsNSw0MS43NTVjMS4yNzgsMCwyLjU2LTAuNDg3LDMuNTM1LTEuNDY0bDEyLjM0My0xMi4zNDJsMTIuMzQzLDEyLjM0M2MwLjk3NiwwLjk3NywyLjI1NiwxLjQ2NCwzLjUzNSwxLjQ2NHMyLjU2LTAuNDg3LDMuNTM1LTEuNDY0YzEuOTUzLTEuOTUzLDEuOTUzLTUuMTE5LDAtNy4wNzFMMjcuOTQ4LDIwLjg3OHoiLz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PGc+PC9nPjxnPjwvZz48Zz48L2c+PC9zdmc+);
	background-size: 12px;
	z-index: 1001; }');
		
		return self::snippet_end();
	}
	
	public static function css_build_generate_column_width() {
		self::snippet_start();
		
		self::snippet_add('.essb_width_columns_1 li { width: 100%; }');
		self::snippet_add('.essb_width_columns_1 li a { width: 92%; }');

		self::snippet_add('.essb_width_columns_2 li { width: 49%; }');
		self::snippet_add('.essb_width_columns_2 li a { width: 86%; }');

		self::snippet_add('.essb_width_columns_3 li { width: 32%; }');
		self::snippet_add('.essb_width_columns_3 li a { width: 80%; }');
		
		self::snippet_add('.essb_width_columns_4 li { width: 24%; }');
		self::snippet_add('.essb_width_columns_4 li a { width: 70%; }');		

		self::snippet_add('.essb_width_columns_5 li { width: 19.5%; }');
		self::snippet_add('.essb_width_columns_5 li a { width: 60%; }');
		
		self::snippet_add('.essb_links li.essb_totalcount_item_before, .essb_width_columns_1 li.essb_totalcount_item_after { width: 100%; text-align: left; }');
		
		self::snippet_add('.essb_network_align_center a { text-align: center; }');
		self::snippet_add('.essb_network_align_right .essb_network_name { float: right;}');
		
		return self::snippet_end();
	}
	
	public static function css_build_sidebar_options() {
		global $essb_options;
		
		$custom_sidebarpos = ESSBOptionValuesHelper::options_value($essb_options, 'sidebar_fixedtop');
		$custom_appearance_pos = ESSBOptionValuesHelper::options_value($essb_options, 'sidebar_leftright_percent');
		
		self::snippet_start();
		
		if ($custom_sidebarpos != '') {
			self::snippet_add('.essb_displayed_sidebar_right, .essb_displayed_sidebar { top: '.$custom_sidebarpos.' !important;}');
		}
		if ($custom_appearance_pos != '') {
			self::snippet_add('.essb_displayed_sidebar_right, .essb_displayed_sidebar { display: none; -webkit-transition: all 0.5s; -moz-transition: all 0.5s;-ms-transition: all 0.5s;-o-transition: all 0.5s;transition: all 0.5s;}');
		}
		
		return self::snippet_end();
	}
	
	public static function css_build_generate_align_code() {
		self::snippet_start();
		
		self::snippet_add('.essb_links_right { text-align: right; }');
		self::snippet_add('.essb_links_center { text-align: center; }');
		self::snippet_add('.essb_hide_icon .essb_icon { display: none !important; }');
		
		return self::snippet_end();
	}
	
	public static function css_build_fixedwidth_button($salt, $width, $align) {
		self::snippet_start();

		$main_class = sprintf('essb_fixedwidth_%1$s', $width.'_'.$align);
		
		self::snippet_add(sprintf('.%1$s a { width: %2$spx;}', $main_class, $width));
		if ($align == "") {
			self::snippet_add(sprintf('.%1$s a { text-align: center;}', $main_class));
		}
		if ($align == "right") {
			self::snippet_add(sprintf('.%1$s .essb_network_name { float: right;}', $main_class));
		}		
		
		return self::snippet_end();
	}
	
	public static function css_build_fullwidth_button($button_width, $buttons_correction_width, $container_width) {
		$main_class = "essb_fullwidth_".$button_width.'_'.$buttons_correction_width.'_'.$container_width;
		
		self::snippet_start();
		
		self::snippet_add(sprintf('.%1$s { width: %2$s;}', $main_class, $container_width.'%'));
		self::snippet_add(sprintf('.%1$s .essb_links_list { width: 100%;}', $main_class));
		self::snippet_add(sprintf('.%1$s li { width: %2$s;}', $main_class, $button_width.'%'));
		self::snippet_add(sprintf('.%1$s li.essb_totalcount_item_before { width: %2$s;}', $main_class, '100%'));
		self::snippet_add(sprintf('.%1$s li a { width: %2$s;}', $main_class, $buttons_correction_width.'%'));
		
		return self::snippet_end();
	}
	
	public static function css_build_compile_display_locations_code() {
		global $essb_options;
		
		self::snippet_start();
		
		// topbar customizations
		$topbar_top_pos = isset($essb_options['topbar_top']) ? $essb_options['topbar_top'] : '';
		$topbar_top_loggedin = isset($essb_options['topbar_top_loggedin']) ? $essb_options['topbar_top_loggedin'] : '';
		
		$topbar_bg_color = isset($essb_options['topbar_bg']) ? $essb_options['topbar_bg'] : '';
		$topbar_bg_color_opacity = isset($essb_options['topbar_bg_opacity']) ? $essb_options['topbar_bg_opacity'] : '';
		$topbar_maxwidth = isset($essb_options['topbar_maxwidth']) ? $essb_options['topbar_maxwidth'] : '';
		$topbar_height = isset($essb_options['topbar_height']) ? $essb_options['topbar_height'] : '';
		$topbar_contentarea_width = isset($essb_options['topbar_contentarea_width']) ? $essb_options['topbar_contentarea_width'] : '';
		if ($topbar_contentarea_width == '' && ESSBOptionValuesHelper::options_bool_value($essb_options, 'topbar_contentarea')) {
			$topbar_contentarea_width = "30";
		}
		
		$topbar_top_onscroll = isset($essb_options['topbar_top_onscroll']) ? $essb_options['topbar_top_onscroll'] : '';
		
		if (is_user_logged_in() && $topbar_top_loggedin != '') {
			$topbar_top_pos = $topbar_top_loggedin;
		}
		
		if ($topbar_bg_color_opacity != '' && $topbar_bg_color == '') {
			$topbar_bg_color = "#ffffff";
		}
		
		if ($topbar_top_pos != '') {
			self::snippet_add(sprintf('.essb_topbar { top: %1$spx !important; }', $topbar_top_pos));
		}
		if ($topbar_bg_color != '') {
			if ($topbar_bg_color_opacity != '') {
				$topbar_bg_color = self::hex2rgba($topbar_bg_color, $topbar_bg_color_opacity);
			}
			self::snippet_add(sprintf('.essb_topbar { background: %1$s !important; }', $topbar_bg_color));
		}
		if ($topbar_maxwidth != '') {
			self::snippet_add(sprintf('.essb_topbar .essb_topbar_inner { max-width: %1$spx; margin: 0 auto; padding-left: 0px; padding-right: 0px;}', $topbar_maxwidth));
		}
		if ($topbar_height != '') {
			self::snippet_add(sprintf('.essb_topbar { height: %1$spx; }', $topbar_height));
		}
		if ($topbar_contentarea_width != '') {
			$topbar_contentarea_width = str_replace('%', '', $topbar_contentarea_width);
			$topbar_contentarea_width = intval($topbar_contentarea_width);
			
			$topbar_buttonarea_width = 100 - $topbar_contentarea_width;
			self::snippet_add(sprintf('.essb_topbar .essb_topbar_inner_buttons { width: %1$s; }', $topbar_buttonarea_width.'%'));
			self::snippet_add(sprintf('.essb_topbar .essb_topbar_inner_content { width: %1$s; }', $topbar_contentarea_width.'%'));
		}
		
		if ($topbar_top_onscroll != '') {
			self::snippet_add('.essb_topbar { margin-top: -200px; }');
		}
		
		// end: topbar customizations

		// bottombar customizations
		
		$topbar_bg_color = isset($essb_options['bottombar_bg']) ? $essb_options['bottombar_bg'] : '';
		$topbar_bg_color_opacity = isset($essb_options['bottombar_bg_opacity']) ? $essb_options['bottombar_bg_opacity'] : '';
		$topbar_maxwidth = isset($essb_options['bottombar_maxwidth']) ? $essb_options['bottombar_maxwidth'] : '';
		$topbar_height = isset($essb_options['bottombar_height']) ? $essb_options['bottombar_height'] : '';
		$topbar_contentarea_width = isset($essb_options['bottombar_contentarea_width']) ? $essb_options['bottombar_contentarea_width'] : '';
		if ($topbar_contentarea_width == '' && ESSBOptionValuesHelper::options_bool_value($essb_options, 'bottombar_contentarea')) {
			$topbar_contentarea_width = "30";
		}
		
		$topbar_top_onscroll = isset($essb_options['bottombar_top_onscroll']) ? $essb_options['bottombar_top_onscroll'] : '';
				
		if ($topbar_bg_color_opacity != '' && $topbar_bg_color == '') {
			$topbar_bg_color = "#ffffff";
		}
		
		if ($topbar_bg_color != '') {
			if ($topbar_bg_color_opacity != '') {
				$topbar_bg_color = self::hex2rgba($topbar_bg_color, $topbar_bg_color_opacity);
			}
			self::snippet_add(sprintf('.essb_bottombar { background: %1$s !important; }', $topbar_bg_color));
		}
		if ($topbar_maxwidth != '') {
			self::snippet_add(sprintf('.essb_bottombar .essb_bottombar_inner { max-width: %1$spx; margin: 0 auto; padding-left: 0px; padding-right: 0px;}', $topbar_maxwidth));
		}
		if ($topbar_height != '') {
			self::snippet_add(sprintf('.essb_bottombar { height: %1$spx; }', $topbar_height));
		}
		if ($topbar_contentarea_width != '') {
			$topbar_contentarea_width = str_replace('%', '', $topbar_contentarea_width);
			$topbar_contentarea_width = intval($topbar_contentarea_width);
				
			$topbar_buttonarea_width = 100 - $topbar_contentarea_width;
			self::snippet_add(sprintf('.essb_bottombar .essb_bottombar_inner_buttons { width: %1$s; }', $topbar_buttonarea_width.'%'));
			self::snippet_add(sprintf('.essb_bottombar .essb_bottombar_inner_content { width: %1$s; }', $topbar_contentarea_width.'%'));
		}
		
		if ($topbar_top_onscroll != '') {
			self::snippet_add('.essb_bottombar { margin-bottom: -200px; }');
		}
		
		// end: bottombar customizations
		
		// float from top customizations
		$top_pos = isset($essb_options['float_top']) ? $essb_options['float_top'] : '';
		$float_top_loggedin = isset($essb_options['float_top_loggedin']) ? $essb_options['float_top_loggedin'] : '';
		
		$bg_color = isset($essb_options['float_bg']) ? $essb_options['float_bg'] : '';
		$bg_color_opacity = isset($essb_options['float_bg_opacity']) ? $essb_options['float_bg_opacity'] : '';
		$float_full = isset($essb_options['float_full']) ? $essb_options['float_full'] : '';
		$float_remove_margin = isset($essb_options['float_remove_margin']) ? $essb_options['float_remove_margin'] : '';
		$float_full_maxwidth = isset($essb_options['float_full_maxwidth']) ? $essb_options['float_full_maxwidth'] : '';
		
		if (is_user_logged_in() && $float_top_loggedin != '') {
			$top_pos = $float_top_loggedin;
		}
		
		if ($bg_color_opacity != '' && $bg_color == '') {
			$bg_color = "#ffffff";
		}
		
		if ($top_pos != '') {
			self::snippet_add(sprintf('.essb_fixed { top: %1$spx !important; }', $top_pos));
		}
		if ($bg_color != '') {
			if ($bg_color_opacity != '') {
				$bg_color = self::hex2rgba($bg_color, $bg_color_opacity);
			}
			self::snippet_add(sprintf('.essb_fixed { background: %1$s !important; }', $bg_color));
		}
		
		if ($float_full == 'true') {
			self::snippet_add('.essb_fixed { left: 0; width: 100%; min-width: 100%; padding-left: 10px; }');
		}
		if ($float_remove_margin == 'true') {
			self::snippet_add('.essb_fixed { margin: 0px !important; }');
		}
		
		if ($float_full_maxwidth != '') {
			self::snippet_add(sprintf('.essb_fixed.essb_links ul { max-width: %1$spx; margin: 0 auto !important; } .essb_fixed { padding-left: 0px; }', $float_full_maxwidth));
		}
		// end: float from top
		
		// postfloat
		
		$postfloat_marginleft = ESSBOptionValuesHelper::options_value($essb_options, 'postfloat_marginleft');
		$postfloat_margintop = ESSBOptionValuesHelper::options_value($essb_options, 'postfloat_margintop');
		$postfloat_top = ESSBOptionValuesHelper::options_value($essb_options, 'postfloat_top');
		$postfloat_percent = ESSBOptionValuesHelper::options_value($essb_options, 'postfloat_percent');
		$postfloat_initialtop = ESSBOptionValuesHelper::options_value($essb_options, 'postfloat_initialtop');
		
		if ($postfloat_marginleft != '') {
			self::snippet_add(sprintf('.essb_displayed_postfloat { margin-left: %1$spx !important; }', $postfloat_marginleft));
		}
		if ($postfloat_margintop != '') {
			self::snippet_add(sprintf('.essb_displayed_postfloat { margin-top: %1$spx !important; }', $postfloat_margintop));
		}
		if ($postfloat_top != '') {
			self::snippet_add(sprintf('.essb_displayed_postfloat.essb_postfloat_fixed { top: %1$spx !important; }', $postfloat_top));
		}
		if ($postfloat_initialtop != '') {
			self::snippet_add(sprintf('.essb_displayed_postfloat { top: %1$spx !important; }', $postfloat_initialtop));
		}
		if ($postfloat_percent != '') {
			self::snippet_add('.essb_displayed_postfloat { opacity: 0; }');			
		}
		
		// end: postfloat
		
		return self::snippet_end();
	}
	
	public static function hex2rgba($color, $opacity = false) {
	
		$default = 'rgb(0,0,0)';
	
		//Return default if no color provided
		if(empty($color))
			return $default;
	
		//Sanitize $color if "#" is provided
		if ($color[0] == '#' ) {
			$color = substr( $color, 1 );
		}
	
		//Check if color has 6 or 3 characters and get values
		if (strlen($color) == 6) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}
	
		//Convert hexadec to rgb
		$rgb =  array_map('hexdec', $hex);
	
		//Check if opacity is set(rgba or rgb)
		if($opacity){
			if(abs($opacity) > 1)
				$opacity = 1.0;
			$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
		} else {
			$output = 'rgb('.implode(",",$rgb).')';
		}
	
		//Return rgb(a) color string
		return $output;
	}
	
	public static function css_build_customizer() {
		global $post, $essb_options, $essb_networks;
		
		$options = $essb_options;
	
		$is_active = ESSBOptionValuesHelper::options_bool_value($essb_options, 'customizer_is_active');
	
		if (isset ( $post )) {
			$post_activate_customizer = get_post_meta ( $post->ID, 'essb_post_customizer', true );
				
			if ($post_activate_customizer != '') {
				if ($post_activate_customizer == "yes") {
					$is_active = true;
				} else {
					$is_active = false;
				}
			}
		}
	
		if ($is_active) {
			self::snippet_start();
			$global_bgcolor = isset ( $options ['customizer_bgcolor'] ) ? $options ['customizer_bgcolor'] : '';
			$global_textcolor = isset ( $options ['customizer_textcolor'] ) ? $options ['customizer_textcolor'] : '';
			$global_hovercolor = isset ( $options ['customizer_hovercolor'] ) ? $options ['customizer_hovercolor'] : '';
			$global_hovertextcolor = isset ( $options ['customizer_hovertextcolor'] ) ? $options ['customizer_hovertextcolor'] : '';
				
			$global_remove_bg_effects = ESSBOptionValuesHelper::options_bool_value($options, 'customizer_remove_bg_hover_effects');
			$css = "";
				
			// @since 2.0
			$customizer_totalbgcolor = ESSBOptionValuesHelper::options_value($options, 'customizer_totalbgcolor');
			$customizer_totalcolor = ESSBOptionValuesHelper::options_value($options, 'customizer_totalcolor');
			$customizer_totalnobgcolor = ESSBOptionValuesHelper::options_value($options, 'customizer_totalnobgcolor');
			$customizer_totalfontsize = ESSBOptionValuesHelper::options_value($options, 'customizer_totalfontsize');
			$customizer_totalfontsize_after = ESSBOptionValuesHelper::options_value($options, 'customizer_totalfontsize_after');
				
			$customizer_totalfontsize_beforeafter = ESSBOptionValuesHelper::options_value($options, 'customizer_totalfontsize_beforeafter');
				
			if ($customizer_totalbgcolor != '') {
				self::snippet_add('.essb_totalcount { background: '.$customizer_totalbgcolor.' !important;} ');
			}
			if ($customizer_totalnobgcolor == "true") {
				self::snippet_add('.essb_totalcount { background: none !important;} ');
			}
			if ($customizer_totalcolor != '') {
				self::snippet_add('.essb_totalcount, .essb_totalcount .essb_t_nb:after { color: '.$customizer_totalcolor.' !important;} ');
			}
			if ($customizer_totalfontsize != '') {
				self::snippet_add('.essb_totalcount .essb_t_nb { font-size: '.$customizer_totalfontsize.'!important; line-height:'.$customizer_totalfontsize.'!important;}');
			}
			if ($customizer_totalfontsize_after != '') {
				self::snippet_add('.essb_totalcount .essb_t_nb:after { font-size: '.$customizer_totalfontsize_after.'!important; }');
			}
	
			if ($customizer_totalfontsize_beforeafter != '') {
				self::snippet_add('.essb_totalcount_item_before .essb_t_before, .essb_totalcount_item_after .essb_t_before { font-size: '.$customizer_totalfontsize_beforeafter.'!important; }');
			}
	
			if ($global_remove_bg_effects) {
				self::snippet_add('.essb_links a:hover, .essb_links a:focus { background: none !important; }');
			}
				
		
	
			$checkbox_list_networks = array();
			foreach ($essb_networks as $key => $object) {
				$checkbox_list_networks[$key] = $object['name'];
			}
				
			if ($global_bgcolor != '' || $global_textcolor != '' || $global_hovercolor != '' || $global_hovertextcolor != '') {
				foreach ( $checkbox_list_networks as $k => $v ) {
					if ($k != '') {
						$singleCss = "";
						if ($global_bgcolor != '' || $global_textcolor != '') {
							$singleCss .= '.essb_links .essb_link_' . $k . ' a { ';
							if ($global_bgcolor != '') {
								$singleCss .= 'background-color:' . $global_bgcolor . '!important;';
							}
							if ($global_textcolor != '') {
								$singleCss .= 'color:' . $global_bgcolor . '!important;';
							}
							$singleCss .= '}';
						}
						if ($global_hovercolor != '' || $global_hovertextcolor != '') {
							$singleCss .= '.essb_links .essb_link_' . $k . ' a:hover, .essb_links .essb_link_' . $k . ' a:focus { ';
							if ($global_hovercolor != '') {
								$singleCss .= 'background-color:' . $global_hovercolor . '!important;';
							}
							if ($global_hovertextcolor != '') {
								$singleCss .= 'color:' . $global_hovertextcolor . '!important;';
							}
							$singleCss .= '}';
						}
	
						self::snippet_add($singleCss);
					}
	
				}
			}
				
			// single network color customization
			foreach ( $essb_networks as $k => $v ) {
				if ($k != '') {
					$network_bgcolor = isset ( $options ['customizer_' . $k . '_bgcolor'] ) ? $options ['customizer_' . $k . '_bgcolor'] : '';
					$network_textcolor = isset ( $options ['customizer_' . $k . '_textcolor'] ) ? $options ['customizer_' . $k . '_textcolor'] : '';
					$network_hovercolor = isset ( $options ['customizer_' . $k . '_hovercolor'] ) ? $options ['customizer_' . $k . '_hovercolor'] : '';
					$network_hovertextcolor = isset ( $options ['customizer_' . $k . '_hovertextcolor'] ) ? $options ['customizer_' . $k . '_hovertextcolor'] : '';
						
					$network_icon = isset ( $options ['customizer_' . $k . '_icon'] ) ? $options ['customizer_' . $k . '_icon'] : '';
					$network_hovericon = isset ( $options ['customizer_' . $k . '_hovericon'] ) ? $options ['customizer_' . $k . '_hovericon'] : '';
					$network_iconbgsize = isset ( $options ['customizer_' . $k . '_iconbgsize'] ) ? $options ['customizer_' . $k . '_iconbgsize'] : '';
					$network_hovericonbgsize = isset ( $options ['customizer_' . $k . '_hovericonbgsize'] ) ? $options ['customizer_' . $k . '_hovericonbgsize'] : '';
						
					$sigleCss = "";
						
					if ($network_bgcolor != '' || $network_textcolor != '') {
						$sigleCss .= '.essb_links .essb_link_' . $k . ' a { ';
						if ($network_bgcolor != '') {
							$sigleCss .= 'background-color:' . $network_bgcolor . '!important;';
						}
						if ($network_textcolor != '') {
							$sigleCss .= 'color:' . $network_textcolor . '!important;';
						}
						$sigleCss .= '}';
						
						if ($k == "more") {
							$sigleCss .= '.essb_links .essb_link_less a { ';
							if ($network_bgcolor != '') {
								$sigleCss .= 'background-color:' . $network_bgcolor . '!important;';
							}
							if ($network_textcolor != '') {
								$sigleCss .= 'color:' . $network_textcolor . '!important;';
							}
							$sigleCss .= '}';
						}
					}
					if ($network_hovercolor != '' || $network_hovertextcolor != '') {
						$sigleCss .= '.essb_links .essb_link_' . $k . ' a:hover, .essb_links .essb_link_' . $k . ' a:focus { ';
						if ($network_hovercolor != '') {
							$sigleCss .= 'background-color:' . $network_hovercolor . '!important;';
						}
						if ($network_hovertextcolor != '') {
							$sigleCss .= 'color:' . $network_hovertextcolor . '!important;';
						}
						$sigleCss .= '}';
						
						if ($k == "more") {
							$sigleCss .= '.essb_links .essb_link_less a:hover, .essb_links .essb_link_less a:focus { ';
							if ($network_hovercolor != '') {
								$sigleCss .= 'background-color:' . $network_hovercolor . '!important;';
							}
							if ($network_hovertextcolor != '') {
								$sigleCss .= 'color:' . $network_hovertextcolor . '!important;';
							}
							$sigleCss .= '}';
						}
					}
						
					if ($network_icon != '') {
						$sigleCss .= '.essb_links .essb_link_' . $k . ' .essb_icon { background: url("' . $network_icon . '") !important; }';
	
						if ($network_iconbgsize != '') {
							$sigleCss .= '.essb_links .essb_link_' . $k . ' .essb_icon { background-size: ' . $network_iconbgsize . '!important; }';
						}
					}
					if ($network_hovericon != '') {
						$sigleCss .= '.essb_links .essb_link_' . $k . ' a:hover .essb_icon { background: url("' . $network_hovericon . '") !important; }';
	
						if ($network_hovericonbgsize != '') {
							$sigleCss .= '.essb_links .essb_link_' . $k . ' a:hover .essb_icon { background-size: ' . $network_hovericonbgsize . '!important; }';
						}
					}
						
					self::snippet_add($sigleCss);
				}
					
			}
		}
	
		$global_user_defined_css = isset ( $options ['customizer_css'] ) ? $options ['customizer_css'] : '';
		$global_user_defined_css = stripslashes ( $global_user_defined_css );
	
		if ($global_user_defined_css != '') {			
			self::snippet_add($global_user_defined_css);
		}

		
		return self::snippet_end();
	
	}
	
	public static function css_build_fanscounter_customizer() {
		global $essb_options;
		
		$is_active = ESSBOptionValuesHelper::options_bool_value($essb_options, 'activate_fanscounter_customizer');
		if (!$is_active) { return '';}
		self::snippet_start();
		$network_list = ESSBSocialFansCounterHelper::available_social_networks();
		
		foreach ($network_list as $network => $title) {			
			$color_isset = ESSBOptionValuesHelper::options_value($essb_options, 'fanscustomizer_'.$network);
			if ($color_isset != '') {
				self::snippet_add('.essbfc-c-'.$network.' { color: '.$color_isset.' !important; }');
				self::snippet_add('.essbfc-bg-'.$network.' { background-color: '.$color_isset.' !important; }');
			}
		}
		return self::snippet_end();
	}
	
	public static function css_build_imageshare_customizer() {
		global $essb_options;
		
		$is_active = ESSBOptionValuesHelper::options_bool_value($essb_options, 'activate_imageshare_customizer');
		if (!$is_active) {
			return '';
		}
		self::snippet_start();
		$listOfNetworksAdvanced = array( "facebook" => "Facebook", "twitter" => "Twitter", "google" => "Google", "linkedin" => "LinkedIn", "pinterest" => "Pinterest", "tumblr" => "Tumblr", "reddit" => "Reddit", "digg" => "Digg", "delicious" => "Delicious", "vkontakte" => "VKontakte", "odnoklassniki" => "Odnoklassniki");
		
		foreach ($listOfNetworksAdvanced as $network => $title) {
			$color_isset = ESSBOptionValuesHelper::options_value($essb_options, 'imagecustomizer_'.$network);
			if ($color_isset != '') {
				self::snippet_add('.essbis .essbis-'.$network.'-btn { background-color: '.$color_isset.' !important; }');
				self::snippet_add('.essbis .essbis-'.$network.'-btn:hover { background-color: '.$color_isset.' !important; }');
			}
		}
		return self::snippet_end();
	}
	
	public static function css_build_footer_css() {
		global $essb_options;
		
		self::snippet_start();
		
		$global_user_defined_css = isset ( $essb_options ['customizer_css_footer'] ) ? $essb_options ['customizer_css_footer'] : '';
		$global_user_defined_css = stripslashes ( $global_user_defined_css );
		
		if ($global_user_defined_css != '') {
			self::snippet_add($global_user_defined_css);
		}
		
		return self::snippet_end();
	}
	
	public static function css_build_mobile_compatibility() {
		global $essb_options;
		
		$mobile_css_screensize = ESSBOptionValuesHelper::options_value($essb_options, 'mobile_css_screensize');
		if (empty($mobile_css_screensize)) {
			$mobile_css_screensize = "750";
		}
		$mobile_css_readblock = ESSBOptionValuesHelper::options_bool_value($essb_options, 'mobile_css_readblock');
		$mobile_css_all = ESSBOptionValuesHelper::options_value($essb_options, 'mobile_css_all');
		$mobile_css_optimized = ESSBOptionValuesHelper::options_bool_value($essb_options, 'mobile_css_optimized');

		self::snippet_start();
		
		if ($mobile_css_readblock) {
			self::snippet_add('@media screen and (max-width: '.$mobile_css_screensize.'px) { .essb_links.essb_displayed_sidebar, .essb_links.essb_displayed_sidebar_right, .essb_links.essb_displayed_postfloat { display: none; } }');
		}
		if ($mobile_css_all) {
			self::snippet_add('@media screen and (max-width: '.$mobile_css_screensize.'px) { .essb_links { display: none; } }');				
		}
		
		if ($mobile_css_optimized) {
			self::snippet_add('@media screen and (max-width: '.$mobile_css_screensize.'px) { .essb-mobile-sharebar, .essb-mobile-sharepoint, .essb-mobile-sharebottom, .essb-mobile-sharebottom .essb_links, .essb-mobile-sharebar-window .essb_links, .essb-mobile-sharepoint .essb_links { display: block; } }');
			self::snippet_add('@media screen and (max-width: '.$mobile_css_screensize.'px) { .essb-mobile-sharebar .essb_native_buttons, .essb-mobile-sharepoint .essb_native_buttons, .essb-mobile-sharebottom .essb_native_buttons, .essb-mobile-sharebottom .essb_native_item, .essb-mobile-sharebar-window .essb_native_item, .essb-mobile-sharepoint .essb_native_item { display: none; } }');
			self::snippet_add('@media screen and (min-width: '.$mobile_css_screensize.'px) { .essb-mobile-sharebar, .essb-mobile-sharepoint, .essb-mobile-sharebottom { display: none; } }');				
		}
		else {
			self::snippet_add(' .essb-mobile-sharebar, .essb-mobile-sharepoint, .essb-mobile-sharebottom { display: none; } ');
				
		}
		
		return self::snippet_end();
	}
	
	public static function css_build_mobilesharebar_fix_code() {
		self::snippet_start();
		
		self::snippet_add('.essb-mobile-sharebottom .essb_links { margin: 0px !important; }');
		self::snippet_add('.essb-mobile-sharebottom .essb_width_columns_2 li a { width: 100% !important; }');
		self::snippet_add('.essb-mobile-sharebottom .essb_width_columns_3 li a { width: 100% !important; }');
		self::snippet_add('.essb-mobile-sharebottom .essb_width_columns_4 li a { width: 100% !important; }');
		self::snippet_add('.essb-mobile-sharebottom .essb_width_columns_5 li a { width: 100% !important; }');
		self::snippet_add('.essb-mobile-sharebottom .essb_width_columns_6 li a { width: 100% !important; }');
		
		return self::snippet_end();
	}
	
	/*
	 * -----------------------------------------------------------------
	 * Javascript
	 * -----------------------------------------------------------------
	 */
	
	public static function js_build_admin_ajax_access_code() {
		global $essb_options;
		
		$code_options = array();
		$code_options['ajax_url'] = admin_url ('admin-ajax.php');
		$code_options['essb3_nonce'] = wp_create_nonce('essb3_ajax_nonce');
		$code_options['essb3_plugin_url'] = ESSB3_PLUGIN_URL;
		$code_options['essb3_facebook_total'] = ESSBOptionValuesHelper::options_bool_value($essb_options, 'facebooktotal');
		$code_options['essb3_admin_ajax'] = ESSBOptionValuesHelper::options_bool_value($essb_options, 'force_counters_admin');
		$code_options['essb3_internal_counter'] = ESSBOptionValuesHelper::options_bool_value($essb_options, 'active_internal_counters');
		$code_options['essb3_stats'] = ESSBOptionValuesHelper::options_bool_value($essb_options, 'stats_active');
		$code_options['essb3_ga'] = ESSBOptionValuesHelper::options_bool_value($essb_options, 'activate_ga_tracking');
		$code_options['essb3_ga_mode'] = ESSBOptionValuesHelper::options_value($essb_options, 'ga_tracking_mode');
		$code_options['essb3_counter_button_min'] = intval(ESSBOptionValuesHelper::options_value($essb_options, 'button_counter_hidden_till'));
		$code_options['essb3_counter_total_min'] = intval(ESSBOptionValuesHelper::options_value($essb_options, 'total_counter_hidden_till'));
		$code_options['blog_url'] = get_site_url().'/';
		$code_options['ajax_type'] = ESSBOptionValuesHelper::options_value($essb_options, 'force_counters_admin_type');
		$code_options['essb3_postfloat_stay'] = ESSBOptionValuesHelper::options_bool_value($essb_options, 'postfloat_always_visible');
		$code_options['essb3_no_counter_mailprint'] = ESSBOptionValuesHelper::options_bool_value($essb_options, 'deactive_internal_counters_mail');
		$code_options['essb3_single_ajax'] = ESSBOptionValuesHelper::options_bool_value($essb_options, 'force_counters_admin_single');
		
		$postfloat_top = ESSBOptionValuesHelper::options_value($essb_options, 'postfloat_top');
		if (!empty($postfloat_top)) {
			$code_options['postfloat_top'] = $postfloat_top;
		}
		
		$hide_float_from_top = ESSBOptionValuesHelper::options_value($essb_options, 'float_top_disappear');
		if (!empty($hide_float_from_top)) {
			$code_options['hide_float'] = $hide_float_from_top;
		}
		$top_pos = isset($essb_options['float_top']) ? $essb_options['float_top'] : '';
		$float_top_loggedin = isset($essb_options['float_top_loggedin']) ? $essb_options['float_top_loggedin'] : '';
		if (is_user_logged_in() && $float_top_loggedin != '') {
			$top_pos = $float_top_loggedin;
		}
		if (!empty($top_pos)) {
			$code_options['float_top'] = $top_pos;
		}
		
		self::snippet_start();
		
		self::snippet_add(sprintf('var essb_settings = %1$s;', json_encode($code_options)));
		
		return self::snippet_end();
	}
	
	public static function js_build_ga_tracking_code() {
		$script = '
		var essb_ga_tracking = function(oService, oPosition, oURL) {
				var essb_ga_type = essb_settings.essb3_ga_mode;
				
				if ( \'ga\' in window && window.ga !== undefined && typeof window.ga === \'function\' ) {
					if (essb_ga_type == "extended") {
						ga(\'send\', \'event\', \'social\', oService + \' \' + oPosition, oURL);
					}
					else {
						ga(\'send\', \'event\', \'social\', oService, oURL);
					}
				}
			};
		';
		
		return $script;
	}
	
	public static function js_build_window_print_code() {
		$script = '
var essb_print = function (oInstance) {	
	essb_tracking_only(\'\', \'print\', oInstance);
	window.print();
};
		';
		
		return $script;
	}
	
	public static function js_build_window_open_code() {
		$script = '
var essb_window = function(oUrl, oService, oInstance) {
	var element = jQuery(\'.essb_\'+oInstance);
	var instance_post_id = jQuery(element).attr("data-essb-postid") || "";
	var instance_position = jQuery(element).attr("data-essb-position") || "";
	var wnd;
	var w = 800 ; var h = 500;
	if (oService == "twitter") { 
		w = 500; h= 300; 
	} 
	var left = (screen.width/2)-(w/2); 
	var top = (screen.height/2)-(h/2); 
	
	if (oService == "twitter") { 
		wnd = window.open( oUrl, "essb_share_window", "height=300,width=500,resizable=1,scrollbars=yes,top="+top+",left="+left ); 
	}  
	else { 
		wnd = window.open( oUrl, "essb_share_window", "height=500,width=800,resizable=1,scrollbars=yes,top="+top+",left="+left ); 
	} 
	
	if (typeof(essb_settings) != "undefined") {
		if (essb_settings.essb3_stats) {
			if (typeof(essb_handle_stats) != "undefined") {
				essb_handle_stats(oService, instance_post_id, oInstance);
			}
		}	

		if (essb_settings.essb3_ga) {
			essb_ga_tracking(oService, oUrl, instance_position);
		}
	}
	essb_self_postcount(oService, instance_post_id); 
	
	var pollTimer = window.setInterval(function() {
		if (wnd.closed !== false) { 
			window.clearInterval(pollTimer); 
			essb_smart_onclose_events(oService, instance_post_id);
		}
	}, 200);  
};

var essb_self_postcount = function(oService, oCountID) {
	if (typeof(essb_settings) != "undefined") {
		oCountID = String(oCountID);

		jQuery.post(essb_settings.ajax_url, {
			\'action\': \'essb_self_postcount\',
			\'post_id\': oCountID,
			\'service\': oService,
			\'nonce\': essb_settings.essb3_nonce
		}, function (data) { if (data) {
			
		}},\'json\');
	}	
};

var essb_smart_onclose_events = function(oService, oPostID) { 
	if (typeof (essbasc_popup_show) == \'function\') {   
		essbasc_popup_show(); 
	} 
	if (typeof essb_acs_code == \'function\') {   
		essb_acs_code(oService, oPostID); 
	} 
};

var essb_tracking_only = function(oUrl, oService, oInstance, oAfterShare) {
	var element = jQuery(\'.essb_\'+oInstance);
	
	if (oUrl == "") {
		oUrl = document.URL;
	}
	
	var instance_post_id = jQuery(element).attr("data-essb-postid") || "";
	var instance_position = jQuery(element).attr("data-essb-position") || "";

	if (typeof(essb_settings) != "undefined") {
		if (essb_settings.essb3_stats) {
			if (typeof(essb_handle_stats) != "undefined") {
				essb_handle_stats(oService, instance_post_id, oInstance);
			}
		}	

		if (essb_settings.essb3_ga) {
			essb_ga_tracking(oService, oUrl, instance_position);
		}
	}
	essb_self_postcount(oService, instance_post_id); 
	
	if (oAfterShare) {
		essb_smart_onclose_events(oService, instance_post_id);
	}	  	
};

var essb_pinterest_picker = function(oInstance) {
	essb_tracking_only(\'\', \'pinterest\', oInstance);
	var e=document.createElement(\'script\');
	e.setAttribute(\'type\',\'text/javascript\');
	e.setAttribute(\'charset\',\'UTF-8\');
	e.setAttribute(\'src\',\'//assets.pinterest.com/js/pinmarklet.js?r=\'+Math.random()*99999999);document.body.appendChild(e);	
};
		';
		
		return $script;
	}
	
	public static function js_build_generate_popup_mailform() {
		global $essb_options;
		$options = $essb_options;
			
		$salt = mt_rand ();
		$mailform_id = 'essb_mail_from_'.$salt;
	
		$mail_salt_check = get_option(ESSB3_MAIL_SALT);
		
		$translate_mail_title = isset($options['translate_mail_title']) ? $options['translate_mail_title'] : '';
		$translate_mail_email = isset($options['translate_mail_email']) ? $options['translate_mail_email'] : '';
		$translate_mail_recipient = isset($options['translate_mail_recipient']) ? $options['translate_mail_recipient'] : '';
		$translate_mail_subject = isset($options['translate_mail_subject']) ? $options['translate_mail_subject'] : '';
		$translate_mail_message = isset($options['translate_mail_message']) ? $options['translate_mail_message'] : '';
		$translate_mail_cancel = isset($options['translate_mail_cancel']) ? $options['translate_mail_cancel'] : '';
		$translate_mail_send = isset($options['translate_mail_send']) ? $options['translate_mail_send'] : '';
		
		$mail_disable_editmessage = isset($options['mail_disable_editmessage']) ? $options['mail_disable_editmessage'] : 'false';
		
		$mail_edit_readonly = "";
		if ($mail_disable_editmessage == "true") {
			$mail_edit_readonly = ' readonly="readonly"';
		}
	
		$mail_captcha = isset($options['mail_captcha']) ? $options['mail_captcha'] : '';
		$mail_captcha_answer = isset($options['mail_captcha_answer']) ? $options['mail_captcha_answer'] : '';
	
		$captcha_html = '';
		if ($mail_captcha != '' && $mail_captcha_answer != '') {
			$captcha_html = '\'<div class="vex-custom-field-wrapper"><strong>'.$mail_captcha.'</strong></div><input name="captchacode" type="text" placeholder="Captcha Code" />\'+';
		}
	
	
		$siteurl = ESSB3_PLUGIN_URL. '/';
	
		$html = 'function essb_mailer(oTitle, oMessage, oSiteTitle, oUrl, oImage, oPermalink) {
		vex.defaultOptions.className = \'vex-theme-os\';
		vex.dialog.open({
		message: \''.($translate_mail_title != '' ? $translate_mail_title : 'Share this with a friend').'\',
		input: \'\' +
		\'<div class="vex-custom-field-wrapper"><strong>'. ($translate_mail_email != '' ? $translate_mail_email : 'Your Email').'</strong></div>\'+
		\'<input name="emailfrom" type="text" placeholder="'. ($translate_mail_email != '' ? $translate_mail_email : 'Your Email').'" required />\' +
		\'<div class="vex-custom-field-wrapper"><strong>'.($translate_mail_recipient != '' ? $translate_mail_recipient : 'Recipient Email'). '</strong></div>\'+
		\'<input name="emailto" type="text" placeholder="'.($translate_mail_recipient != '' ? $translate_mail_recipient : 'Recipient Email'). '" required />\' +
		\'<div class="vex-custom-field-wrapper" style="border-bottom: 1px solid #aaa !important; margin-top: 10px;"><h3></h3></div>\'+
		\'<div class="vex-custom-field-wrapper" style="margin-top: 10px;"><strong>'.($translate_mail_subject != '' ? $translate_mail_subject : 'Subject').'</strong></div>\'+
		\'<input name="emailsubject" type="text" placeholder="Subject" required value="\'+oTitle+\'" />\' +
		\'<div class="vex-custom-field-wrapper" style="margin-top: 10px;"><strong>'.($translate_mail_message != '' ? $translate_mail_message : 'Message').'</strong></div>\'+
		\'<textarea name="emailmessage" placeholder="Message" required" rows="6" '.$mail_edit_readonly.'>\'+oMessage+\'</textarea>\' +
		'.$captcha_html. '
		\'\',
		buttons: [
		jQuery.extend({}, vex.dialog.buttons.YES, { text: \''.($translate_mail_send != '' ? $translate_mail_send : 'Send').'\' }),
		jQuery.extend({}, vex.dialog.buttons.NO, { text: \''.($translate_mail_cancel != '' ? $translate_mail_cancel : 'Cancel').'\' })
		],
		callback: function (data) {
		if (data.emailfrom && typeof(data.emailfrom) != "undefined") {
		var c = typeof(data.captchacode) != "undefined" ? data.captchacode : "";
		essb_sendmail_ajax'.$salt.'(data.emailfrom, data.emailto, data.emailsubject, data.emailmessage, c, oSiteTitle, oUrl, oImage, oPermalink);
	}
	}
	
	});
	};
	function essb_sendmail_ajax'.$salt.'(emailfrom, emailto, emailsub, emailmessage, c, oSiteTitle, oUrl, oImage, oPermalink) {
	
	var get_address = "' . ESSB3_PLUGIN_URL . '/public/essb-mail.php?from="+emailfrom+"&to="+emailto+"&sub="+emailsub+"&message="+emailmessage+"&t="+oSiteTitle+"&u="+oUrl+"&img="+oImage+"&p="+oPermalink+"&c="+c+"&salt='.$mail_salt_check.'";
	jQuery.getJSON(get_address)
	.done(function(data){
	alert(data.message);
	});
	};
	';

		return $html;
	}
	
	public static function js_build_generate_more_button_inline() {
		$output = "";
		
		$output .= 'jQuery(document).ready(function($){
			jQuery.fn.essb_toggle_more = function(){
				return this.each(function(){
					$single = $(this);
						
					$single.removeClass(\'essb_after_more\');
					$single.addClass(\'essb_before_less\');
				});
			};
			jQuery.fn.essb_toggle_less = function(){
				return this.each(function(){
					$single = $(this);
						
					$single.addClass(\'essb_after_more\');
					$single.removeClass(\'essb_before_less\');
				});
			};
		});
		function essb_toggle_more(unique_id) {
			jQuery(\'.essb_\'+unique_id+\' .essb_after_more\').essb_toggle_more();
			$more_button = jQuery(\'.essb_\'+unique_id).find(\'.essb_link_more\');
			if (typeof($more_button) != "undefined") {
				$more_button.hide();
				$more_button.addClass(\'essb_hide_more_sidebar\');
			}
			$more_button = jQuery(\'.essb_\'+unique_id).find(\'.essb_link_more_dots\');
			if (typeof($more_button) != "undefined") {
				$more_button.hide();
				$more_button.addClass(\'essb_hide_more_sidebar\');
			}
		};
		
		function essb_toggle_less(unique_id) {
			jQuery(\'.essb_\'+unique_id+\' .essb_before_less\').essb_toggle_less();
			$more_button = jQuery(\'.essb_\'+unique_id).find(\'.essb_link_more\');
			if (typeof($more_button) != "undefined") {
				$more_button.show();
				$more_button.removeClass(\'essb_hide_more_sidebar\');
			};
			$more_button = jQuery(\'.essb_\'+unique_id).find(\'.essb_link_more_dots\');
			if (typeof($more_button) != "undefined") {
				$more_button.show();
				$more_button.removeClass(\'essb_hide_more_sidebar\');
			};
		};';
		
		return $output;
	}
	
	public static function js_build_generate_more_button_popup() {
		$output = 'var essb_morepopup_opened = false;';
		
		$output .= 'function essb_toggle_more_popup(unique_id) {
	jQuery.fn.extend({
        center: function () {
            return this.each(function() {
                var top = (jQuery(window).height() - jQuery(this).outerHeight()) / 2;
                var left = (jQuery(window).width() - jQuery(this).outerWidth()) / 2;
                jQuery(this).css({position:\'fixed\', margin:0, top: (top > 0 ? top : 0)+\'px\', left: (left > 0 ? left : 0)+\'px\'});
            });
        }
    }); 
	
    if (essb_morepopup_opened) {
      essb_toggle_less_popup(unique_id);
      return;
    }
    
    var is_from_mobilebutton = false;
    var height_of_mobile_bar = 0;
    if (jQuery(".essb-mobile-sharebottom").length) {
    	is_from_mobilebutton = true;
    	height_of_mobile_bar = jQuery(".essb-mobile-sharebottom").outerHeight();
    }
    
	var win_width = jQuery( window ).width();
	var win_height = jQuery(window).height();
	var doc_height = jQuery(\'document\').height();
	
	var base_width = 550;
	
	if (win_width < base_width) { base_width = win_width - 30; }

	var instance_mobile = false;

	var element_class = ".essb_morepopup_"+unique_id;
	var element_class_shadow = ".essb_morepopup_shadow_"+unique_id;
	
	jQuery(element_class).css( { width: base_width+\'px\'});
		
	jQuery(element_class).fadeIn(400);
	jQuery(element_class).center();
	if (is_from_mobilebutton) {
		jQuery(element_class).css( { top: \'5px\'});
		jQuery(element_class).css( { height: (win_height - height_of_mobile_bar - 10)+\'px\'});
		var element_content_class = ".essb_morepopup_content_"+unique_id;
		
		jQuery(element_content_class).css( { height: (win_height - height_of_mobile_bar - 40)+\'px\', "overflowY" :"auto"});
		jQuery(element_class_shadow).css( { height: (win_height - height_of_mobile_bar)+\'px\'});
	}
	jQuery(element_class_shadow).fadeIn(200);
	essb_morepopup_opened = true;
};

function essb_toggle_less_popup(unique_id) {
	var element_class = ".essb_morepopup_"+unique_id;
	var element_class_shadow = ".essb_morepopup_shadow_"+unique_id;
	jQuery(element_class).fadeOut(200);
	jQuery(element_class_shadow).fadeOut(200);
	essb_morepopup_opened = false;
};';
		
		return $output;
	}
	
	public static function js_build_generate_sidebar_reveal_code() {
		global $essb_options;
		
		$appear_pos = ESSBOptionValuesHelper::options_value($essb_options, 'sidebar_leftright_percent');
		$disappear_pos = ESSBOptionValuesHelper::options_value($essb_options, 'sidebar_leftright_percent_hide');
		
		if (empty($appear_pos)) {
			$appear_pos = "0";			
		}
		if (empty($disappear_pos)) {
			$disappear_pos = "0";
		}
		
		$output = '';

		//$appear_pos = ESSBOptionValuesHelper::options_value($essb_options, 'sidebar_leftright_percent');
		
		if (ESSBOptionValuesHelper::options_bool_value($essb_options, 'sidebar_leftright_close')) {
			$output .= '
			jQuery(document).ready(function($){
			
				$(".essb_link_sidebar-close a").each(function() {
				
					$(this).click(function(event) {
						event.preventDefault();
						var links_list = $(this).parent().parent().get(0);
						
						if (!$(links_list).length) { return; }
						
						$(links_list).find(".essb_item").each(function(){
							if (!$(this).hasClass("essb_link_sidebar-close")) {
								$(this).toggleClass("essb-sidebar-closed-item");
							}
							else {
								$(this).toggleClass("essb-sidebar-closed-clicked");
							}
						});
						
					});
				
				});
			});
			
			';
		}
		
		if ($appear_pos != '' || $disappear_pos != '') {
		$output .= '
		jQuery(document).ready(function($){
		
			$(window).scroll(essb_sidebar_onscroll);
		
			function essb_sidebar_onscroll() {
				var current_pos = $(window).scrollTop();
				var height = $(document).height()-$(window).height();
				var percentage = current_pos/height*100;

				var value_disappear = "'.$disappear_pos.'";
				var value_appear = "'.$appear_pos.'";
				
				var element;
				if ($(".essb_displayed_sidebar").length) {
					element = $(".essb_displayed_sidebar");
				}
				if ($(".essb_displayed_sidebar_right").length) {
					element = $(".essb_displayed_sidebar_right");
				}
				
				if (!element || typeof(element) == "undefined") { return; }
				
				value_disappear = parseInt(value_disappear);
				value_appear = parseInt(value_appear);
				
				if (value_appear > 0 && value_disappear == 0) {
					if (percentage >= value_appear && !element.hasClass("active-sidebar")) {
						element.fadeIn(100);
						element.addClass("active-sidebar");
						return;
					}
					
					if (percentage < value_appear && element.hasClass("active-sidebar")) {
						element.fadeOut(100);
						element.removeClass("active-sidebar");
						return;
					}
				}
				
				if (value_disappear > 0 && value_appear == 0) {
					if (percentage >= value_disappear && !element.hasClass("hidden-sidebar")) {
						element.fadeOut(100);
						element.addClass("hidden-sidebar");
						return;
					}
					
					if (percentage < value_disappear && element.hasClass("hidden-sidebar")) {
						element.fadeIn(100);
						element.removeClass("hidden-sidebar");
						return;
					}
				}
				
				if (value_appear > 0 && value_disappear > 0) {
					if (percentage >= value_appear && percentage < value_disappear && !element.hasClass("active-sidebar")) {
						element.fadeIn(100);
						element.addClass("active-sidebar");
						return;
					}
					
					if ((percentage < value_appear || percentage >= value_disappear) && element.hasClass("active-sidebar")) {
						element.fadeOut(100);
						element.removeClass("active-sidebar");
						return;
					}
				}
			}
		});		
		';
		}
		return $output;
	}

	public static function js_build_generate_postfloat_reveal_code() {
		global $essb_options;
	
	
		$output = '';
	
		$appear_pos = ESSBOptionValuesHelper::options_value($essb_options, 'postfloat_percent');
		if (empty($appear_pos)) {
			$appear_pos = "0";
		}
	
	
		if ($appear_pos != '') {
			$output .= '
		jQuery(document).ready(function($){
	
			$(window).scroll(essb_postfloat_onscroll);
	
			function essb_postfloat_onscroll() {
				var current_pos = $(window).scrollTop();
				var height = $(document).height()-$(window).height();
				var percentage = current_pos/height*100;
	
				var value_appear = "'.$appear_pos.'";
	
				var element;
				if ($(".essb_displayed_postfloat").length) {
					element = $(".essb_displayed_postfloat");
				}
	
				if (!element || typeof(element) == "undefined") { return; }
	
	
				value_appear = parseInt(value_appear);
	
				if (value_appear > 0 ) {
					if (percentage >= value_appear && !element.hasClass("essb_active_postfloat")) {
						
						
						element.addClass("essb_active_postfloat");
						return;
					}
			
					if (percentage < value_appear && element.hasClass("essb_active_postfloat")) {
						
						element.removeClass("essb_active_postfloat");
						return;
					}
				}
	
			}
		});
		';
		}
		return $output;
	}
	

	public static function js_build_generate_topbar_reveal_code() {
		global $essb_options;
	
	
		$output = '';
	
		$appear_pos = ESSBOptionValuesHelper::options_value($essb_options, 'topbar_top_onscroll');
		$topbar_hide = ESSBOptionValuesHelper::options_value($essb_options, 'topbar_hide');
	
	
		if ($appear_pos != '' || $topbar_hide != '') {
			$output .= '
		jQuery(document).ready(function($){
	
			$(window).scroll(essb_topbar_onscroll);
	
			function essb_topbar_onscroll() {
				var current_pos = $(window).scrollTop();
				var height = $(document).height()-$(window).height();
				var percentage = current_pos/height*100;
	
				var value_appear = "'.$appear_pos.'";
				var value_disappear = "'.$topbar_hide.'";
	
				var element;
				if ($(".essb_topbar").length) {
					element = $(".essb_topbar");
				}
	
				if (!element || typeof(element) == "undefined") { return; }
	
	
				value_appear = parseInt(value_appear);
				value_disappear = parseInt(value_disappear);
	
				if (value_appear > 0 ) {
					if (percentage >= value_appear && !element.hasClass("essb_active_topbar")) {
						
						
						element.addClass("essb_active_topbar");
						return;
					}
			
					if (percentage < value_appear && element.hasClass("essb_active_topbar")) {
						
						element.removeClass("essb_active_topbar");
						return;
					}
				}
				
				if (value_disappear > 0) {
					if (percentage >= value_disappear && !element.hasClass("hidden-float")) {
						element.addClass("hidden-float");
						element.css( {"opacity": "0"});
						return;
					}
					if (percentage < value_disappear && element.hasClass("hidden-float")) {
						element.removeClass("hidden-float");
						element.css( {"opacity": "1"});
						return;
					}
				}
	
			}
		});
		';
		}
		return $output;
	}
	
	public static function js_build_generate_bottombar_reveal_code() {
		global $essb_options;
	
	
		$output = '';
	
		$appear_pos = ESSBOptionValuesHelper::options_value($essb_options, 'bottombar_top_onscroll');
		$bottombar_hide = ESSBOptionValuesHelper::options_value($essb_options, 'bottombar_hide');
		
	
		if ($appear_pos != '' || $bottombar_hide != '') {
			$output .= '
			jQuery(document).ready(function($){
	
			$(window).scroll(essb_bottombar_onscroll);
	
			function essb_bottombar_onscroll() {
			var current_pos = $(window).scrollTop();
			var height = $(document).height()-$(window).height();
			var percentage = current_pos/height*100;
	
			var value_appear = "'.$appear_pos.'";
			var value_disappear = "'.$bottombar_hide.'";
			var element;
			if ($(".essb_bottombar").length) {
			element = $(".essb_bottombar");
		}
	
		if (!element || typeof(element) == "undefined") { return; }
	
	
		value_appear = parseInt(value_appear);
		value_disappear = parseInt(value_disappear);
		if (value_appear > 0 ) {
			if (percentage >= value_appear && !element.hasClass("essb_active_bottombar")) {
				element.addClass("essb_active_bottombar");
				return;
			}
			
			if (percentage < value_appear && element.hasClass("essb_active_bottombar")) {
	
				element.removeClass("essb_active_bottombar");
				return;
			}
		}
		if (value_disappear > 0) {
					if (percentage >= value_disappear && !element.hasClass("hidden-float")) {
						element.addClass("hidden-float");
						element.css( {"opacity": "0"});
						return;
					}
					if (percentage < value_disappear && element.hasClass("hidden-float")) {
						element.removeClass("hidden-float");
						element.css( {"opacity": "1"});
						return;
					}
				}
		}
		});
		';
		}
		return $output;
	}	
}

?>