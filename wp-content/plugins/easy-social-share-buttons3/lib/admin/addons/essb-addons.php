<?php

$essb_addons = ESSBAddonsHelper::get_instance();
$essb_addons->call_remove_addon_list_update();

$current_list = $essb_addons->get_addons();

//print_r($current_list);


?>

<div class="wrap">
	<div class="essb-title-panel">

		<h3>Addons for Easy Social Share Buttons for WordPress</h3>
		<p>
			Version <strong><?php echo ESSB3_VERSION;?></strong>. &nbsp;<strong><a
				href="http://fb.creoworx.com/essb/change-log/" target="_blank">See
					what's new in this version</a></strong>&nbsp;&nbsp;&nbsp;<strong><a
				href="http://codecanyon.net/item/easy-social-share-buttons-for-wordpress/6394476?ref=appscreo"
				target="_blank">Easy Social Share Buttons plugin homepage</a></strong>
		</p>
	</div>

	<div class="wp-list-table widefat plugin-install">
		<div id="the-list">
		<?php 
		
		foreach ($current_list as $addon_key => $addon_data) {
			print '<div class="plugin-card">';
			print '<div class="plugin-card-top">';
			print '<h4><a href="'.$addon_data['page'].'" target="_blank">'.$addon_data["name"].'</a></h4>';
			print '<a href="'.$addon_data['page'].'" target="_blank"><img src="'.$addon_data["image"].'" style="max-width: 100%;"/></a>';
			print '<p>'.$addon_data['description'].'</p>';
			print '<a class="button" target="_blank"  href="'.$addon_data['page'].'">Learn more</a>';
			print '<div class="plugin-action-buttons">Price: <b>'.$addon_data['price'].'</b></div>';
			print '</div>';

			print '<div class="plugin-card-bottom">';
			print '<div class="column-downloaded">';
			
			$check_exist = $addon_data['check'];
			$is_installed = false;
			
			if (!empty($check_exist)) {
				if (defined($check_exist)) {
					$is_installed = true;
				}
			}
			
			if (!$is_installed) {
				print '<a class="button button-primary" target="_blank"  href="'.$addon_data['page'].'">Get it now '.$addon_data['price'].'</a>';
			}
			else {
				print '<span class="button button-primary button-disabled">Installed</span>';
			}
			print '</div>';
			print '<div class="column-compatibility">';
			$addon_requires = $addon_data['requires'];
			if (version_compare (ESSB3_VERSION, $addon_requires, '<')) {
				print '<span class="compatibility-untested">Requires Easy Social Share Buttons for WordPress version <b>'.$addon_requires.'</b> or newer</span>';
			}
			else {
				print '<span class="compatibility-compatible"><b>Compatible</b> with your version of Easy Social Share Buttons for WordPress</span>';
				
			}
			print '</div>';
			print '</div>';
			print '</div>';
		}
		
		?>
		</div>
	</div>
</div>