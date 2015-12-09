<?php

remove_action( 'genesis_entry_header', 'genesis_do_post_title' );
remove_action( 'genesis_entry_header', 'genesis_post_info', 12 );

function rg_display_newsletter() {

	if( get_field( 'use_affiliate_links' )[0] == 'Yes' ) {
		$aff_link = '?tag=runawaygoodness-20';
	} else {
		$aff_link = '';
	}
	

	if( have_rows('book') ) {
		echo '<body style="background-color:#AB8BAA;">';
			$html = '<p id="top-newsletter-links" style="text-align:center;font-size:11px;">Were you forwarded this email? <a href="https://runawaygoodness.com/?ref=emailforward">Click here to subscribe</a><br />';
			$html .= '<a href="'. get_permalink( get_the_ID() ) .'">View this email in a browser</a></p>';
			$html .= '<table border="0" width="100%" height="100%" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF;" leftmargin="0" rightmargin="0" marginwidth="0" marginheight="0">';
				$html .= '<tbody>';
					$html .= '<tr>';
						$html .= '<td align="center" valign="top" bgcolor="#FFFFFF" style="background-color:#FFFFFF">';
							// 600px container
							$html .= '<table border="0" width="600" cellpadding="0" cellspacing="0" class="container" style="background-color:#AB8BAA;width:600px;max-width:600px;" bgcolor="#AB8BAA">';
								$html .= '<tbody>';
									$html .= '<tr>';
										$html .= '<td class="container-padding header" align="left" style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:0;">';
							// Header Table
											$html .= '<table cellpadding="0" cellspacing="0" width="580" style="width:580px;max-width:580px;background-color:#987197;border-radius:5px;" bgcolor="#987197">';
												$html .= '<tbody>';
													$html .= '<tr>';
														$html .= '<td style="text-align:center; color:#ffffff;font-weight:bold">';
														$html .= '<a href="http://www.runawaygoodness.com" target="_blank"><img align="middle" alt="Runaway Goodness" src="http://runawaygoodness.com/wp-content/uploads/2015/08/logo-white-360x80.png" style="border:0 none;min-height:80px"></a><br />';
														$html .= 'Book Deals For '. get_field( 'newsletter_send_date' );

														
					  									$html .= '</td>';
					  								$html .= '</tr>';
					  							$html .= '</tbody>';
					  						$html .= '</table>';
										$html .= '</td>';
									$html .= '</tr>';

									// Grab hero row
									if( get_field( 'hero_title' ) ) {
										$html .= '<tr>';
											$html .= '<td class="container-padding intro" align="left" style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:0;">';
												$html .= '<table cellpadding="0" cellspacing="0" width="580" style="width:580px;max-width:580px;background-color:#ffffff;border-radius:5px;" bgcolor="#ffffff">';
													$html .= '<tbody>';
														$html .= '<tr>';
															$html .= '<td style="font-size:2px;padding:10px">';
																$html .= '<div style="font-family:Trebuchet MS;font-size:24px;font-weight:bold;color:#987197;line-height: 1.5;">';
																	$html .= get_field( 'hero_title' );
																$html .= '</div>';
																$html .= '<br>';
																$html .= '<div style="font-family:Trebuchet MS;font-size:16px;color:#888888;line-height:1.4;display:block;margin-bottom:5px">';
																	$html .= get_field( 'hero_blurb' );
																$html .= '</div>';
															$html .= '</td>';
														$html .= '</tr>';
													$html .= '</tbody>';
												$html .= '</table>';
											$html .= '</td>';
										$html .= '</tr>';
									}

									$buttons = 0;

									// loop through the rows of data
									while ( have_rows('book') ) : the_row();
										$row_counter++;
										// grab some image information first
										$image = get_sub_field( 'book_cover' );

										if( !empty($image) ) {

											// vars
											$url = $image['url'];
											$title = $image['title'];
											$alt = $image['alt'];
											$caption = $image['caption'];

											// thumbnail
											$size = 'newslettercover';
											$thumb = $image['sizes'][ $size ];
											$width = $image['sizes'][ $size . '-width' ];
											$height = $image['sizes'][ $size . '-height' ];
										}

										$facebook_share_link = 'http://www.facebook.com/sharer/sharer.php?u='. get_permalink( get_the_ID() ) . '#book' . $row_counter;
										$twitter_share_link = 'https://twitter.com/home?status='. urlencode( get_sub_field( 'book_title' ) . ' is on sale! ' . get_permalink( get_the_ID() ) . '#book' . $row_counter . ' @runawaygoodness' );
										
										$html .= '<tr>';
											$html .= '<td class="container-padding item" align="left" style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:0;">';
											$html .= '<a id="book'. $row_counter.'" class="anchor"></a>';
												$html .= '<table cellpadding="0" cellspacing="0" width="580" style="width:580px;max-width:580px;background-color:#ffffff;border-radius:5px;" bgcolor="#ffffff">';
													$html .= '<tbody>';
														$html .= '<tr>';
															$html .= '<td style="padding:10px"><!--[if mso]>';
																$html .= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
																	$html .= '<tr>';
																		$html .= '<td width="25%" valign="top"><![endif]-->';
																			$html .= '<table width="120" border="0" cellpadding="0" cellspacing="0" align="left" class="force-row">';
																				$html .= '<tbody>';
																					$html .= '<tr>';
																						$html .= '<td class="col" valign="top" style="padding:0;width:100%">';
																							if( get_sub_field( 'amazon_asin' ) ) {
																								if( get_sub_field( 'book_price' ) == '0' ) {
																									$html .= '<a href="http://www.amazon.com/dp/' . get_sub_field( 'amazon_asin' ) . '/'. $aff_link.'"><img src="' . $thumb . '"  alt="' . get_sub_field( 'book_title' ) . '" style="max-width:120px;min-width:120px;margin-bottom:10px;" align="left"></a>';
																									$amazon_url = 'http://www.amazon.com/dp/'. get_sub_field( 'amazon_asin' ) . '/'. $aff_link;
																								} else {
																									$html .= '<a href="http://www.amazon.com/dp/' . get_sub_field( 'amazon_asin') . '/?tag=runawaygoodness-20"><img src="' . $thumb . '"  alt="' . get_sub_field( 'book_title' ) . '" style="max-width:120px;min-width:120px;margin-bottom:10px;" align="left"></a>';
																									$amazon_url = 'http://www.amazon.com/dp/'. get_sub_field( 'amazon_asin' ) . '/?tag=runawaygoodness-20';
																								}
																							} else {
																								$html .= '<img src="' . get_sub_field( 'book_cover' ) . '" alt="' . get_sub_field( 'book_title' ) . '" style="max-width:120px;min-width:120px;margin-bottom:10px;" align="left">';
																							}

																							$html .= '<div style="width:100%;text-align:center;display:block;">';
																							$html .= '<div align="center" style="font-size:14px;font-weight:bold;">Share the Deal<br />';
																							$html .= '<a href="'. $facebook_share_link .'" target="_blank"><img height="24px" width="24px" src="https://runawaygoodness.com/wp-content/plugins/social-media-feather/synved-social/image/social/regular/48x48/facebook.png"></a>&nbsp;';
																							$html .= '<a href="'. $twitter_share_link .'" target="_blank"><img height="24px" width="24px" src="https://runawaygoodness.com/wp-content/plugins/social-media-feather/synved-social/image/social/regular/48x48/twitter.png"></a></div>';
																							$html .= '</div>';
																						$html .= '</td>';
																					$html .= '</tr>';
																				$html .= '</tbody>';
																			$html .= '</table><!--[if mso]>';
																		$html .= '</td>';
																		$html .= '<td width="75%" valign="top"><![endif]-->';
																			$html .= '<table width="420" border="0" cellpadding="0" cellspacing="0" align="right" class="force-row">';
																				$html .= '<tbody>';
																					$html .= '<tr>';
																						$html .= '<td class="col" valign="top" style="font-size:2px;width:100%;">';
																							$html .= '<div style="font-family:Trebuchet MS;color:#987197;font-size:20px;font-weight:bold;line-height:1.38">'. get_sub_field( 'book_title' ) .'</div><br />';
																							$html .= '<div style="font-family:Trebuchet MS;color:#888888;font-size:18px;line-height:1.38">'. get_sub_field( 'author_name' ) .'</div><br />';

																							if( get_sub_field( 'sparkle') ) {
																								$html .= '<div style="font-family:Trebuchet MS;color:#8C001A;font-size:20px;line-height:1.38">'. get_sub_field( 'sparkle' ) .'</div><br />';
																							}

																							// $html .= '<div style="font-family:Trebuchet MS;color:#987197;font-size:18px;line-height:1.38">'. get_sub_field( 'book_category' ) .'</div><br />';
																							if( strlen( get_sub_field( 'book_blurb' ) ) > 250 ) {
																								$html .= '<div style="font-family:Trebuchet MS;color:#555555;font-size:16px;line-height:1.38">'. substr( get_sub_field( 'book_blurb' ), 0, 250 ) .'... <a href="'. $amazon_url.'">Read More</a></div><br /><br />';
																							} else {
																								$html .= '<div style="font-family:Trebuchet MS;color:#555555;font-size:16px;line-height:1.38">'. get_sub_field( 'book_blurb' ) .'</div><br /><br />';
																							}

																							// lower_price
																							if( get_sub_field( 'lower_price' ) ) {
																								$html .= '<div style="font-family:Trebuchet MS;color:#8C001A;font-size:20px;line-height:1.38">New Lower Price!</div><br />';
																							} else {
																								if( get_sub_field( 'book_original_price' ) ) {
																									$html .= '<div style="font-family:Trebuchet MS;color:#8C001A;font-size:20px;line-height:1.38">Originally: <span style="text-decoration:line-through;">$'. get_sub_field( 'book_original_price' ) .'</span></div><br />';
																								}
																							}

																							$urgency = array( 'Available for a limited time', 'Ending Soon', 'Almost Gone', 'Offer Ending Soon' );
																							// $rand_urgency = array_rand( $urgency, 1 );
																							$rand_urgency = $urgency[mt_rand(0, count($urgency) - 1)];

																							$html .= '<div style="font-family:Trebuchet MS;color:#555555;font-size:14px;line-height:1.38">'. $rand_urgency .'</div>';

																							if( get_sub_field( 'book_price' ) == '0' || get_sub_field( 'book_price' ) == '0.00' ) {
																								// $html .= '<div style="font-family:Trebuchet MS;color:#ff2800;font-size:32px;line-height:1.38"><strong>Download Free!</strong></div><br />';
																								$cur_book_price = "Download Free!";
																							} else {
																								// $html .= '<div style="font-family:Trebuchet MS;color:#ff2800;font-size:32px;line-height:1.38">Buy <strong>$'. get_sub_field( 'book_price' ) .'</strong></div><br /><br /><br /><br />';
																								$cur_book_price = 'Own it for $'. get_sub_field( 'book_price' );
																							}
																							
																							$html .= '<table cellpadding="0" cellspacing="0" width="366">';
																								$html .= '<tbody>';
																									$html .= '<tr>';
																										// Kindle
																										if( get_sub_field( 'amazon_asin' ) ) {
																											$html .= '<td style="height:65px;width:180px;max-width:180px;background-color:#008000;border-radius:5px;text-align:center" bgcolor="#008000">';
																												$html .= '<a href="'. $amazon_url.'" style="font-family:Trebuchet MS;color:#ffffff;display:inline-block;font-size:20px;text-align:center;text-decoration:none;white-space:nowrap;width:100%">'. $cur_book_price .'</a>';
																											$html .= '</td>';
																										}
																								$html .= '</tbody>';
																							$html .= '</table><br />';
																							if( get_sub_field( 'ht_site_name' ) && get_sub_field( 'ht_site_url' ) ) {
																								$html .= '<div style="font-size:16px;font-weight:bold;">Hat Tip: <a href="'. get_sub_field( 'ht_site_url' ) .'" target="_blank">'. get_sub_field( 'ht_site_name' ) .'</a></div>';
																							}
																						$html .= '</td>';
																					$html .= '</tr>';
																				$html .= '</tbody>';
																			$html .= '</table>';
																			$html .= '<div style="clear:both;"></div><!--[if mso]>';
																		$html .= '</td>';
																	$html .= '</tr>';
																$html .= '</table><![endif]-->';
															$html .= '</td>';
														$html .= '</tr>';
													$html .= '</tbody>';
												$html .= '</table>';
											$html .= '</td>';
										$html .= '</tr>';
									endwhile;

									// Call to action row 1
									if( get_field( 'cta_title_1' ) ) {

										// see if we have an image
										if( get_field( 'cta_image_1' ) ) {
											$html .= bt_cta( 1, true );
										} else {
											$html .= bt_cta( 1, false );
										}
									}

									// Call to action row 2
									if( get_field( 'cta_title_2' ) ) {

										// see if we have an image
										if( get_field( 'cta_image_2' ) ) {
											$html .= bt_cta( 2, true );
										} else {
											$html .= bt_cta( 2, false );
										}
									}

									// footer stuff
									$html .= '<tr>';
										$html .= '<td class="container-padding footer" align="left" style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:10px;">';
											$html .= '<table cellpadding="0" cellspacing="0" width="580" style="width:580px;max-width:580px;">';
												$html .= '<tbody>';
													$html .= '<tr>';
														$html .= '<td style="font-size:2px;padding:10px">';
															$html .= '<div style="font-family:Trebuchet MS;font-size:16px;color:#EEEEEE;line-height:1.4;display:block;margin-bottom:5px">The prices listed above were confirmed by Runaway Goodness at the time this email was sent. Please note that prices change without notice so please verify that the price is still available prior to downloading. For free Kindle books, confirm that Amazon\'s "Kindle Price" is still listed as free.</div>';
														$html .= '</td>';
													$html .= '</tr>';
												$html .= '</tbody>';
											$html .= '</table>';
										$html .= '</td>';
									$html .= '</tr>';
								$html .= '</tbody>';
							$html .= '</table>';
						$html .= '</td>';
					$html .= '</tr>';
				$html .= '</tbody>';
			$html .= '</table>';
		// $html .= '</body>';

	}

	echo $html;

	if( current_user_can( 'manage_options' ) ) {
		echo '<br /><br /><br />';

		$html = str_replace('runawaygoodness-20', 'rgnewsletter-20', $html );

		echo '<textarea rows="25" cols="100">' . $html .'</textarea>';
	}

}

add_action( 'genesis_entry_content', 'rg_display_newsletter' );


function bt_cta( $cta_num, $image ) {
	if( $image == true ) {
		$data .= '<tr>';
			$data .= '<td class="container-padding giveaway" align="left" style="padding-left:10px;padding-right:10px;padding-top:10px;padding-bottom:0;">';
				$data .= '<table cellpadding="0" cellspacing="0" width="580" style="width:580px;max-width:580px;background-color:#ffffff;border-radius:5px;" bgcolor="#ffffff">';
					$data .= '<tbody>';
						$data .= '<tr>';
							$data .= '<td><!--[if mso]>';
								$data .= '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
									$data .= '<tr>';
										$data .= '<td width="50%" valign="top"><![endif]-->';
											$data .= '<table width="264" border="0" cellpadding="0" cellspacing="0" align="left" class="force-row">';
												$data .= '<tbody>';
													$data .= '<tr>';
														$data .= '<td class="col" valign="top" style="padding:5px;width:100%">';
															$data .= '<div style="font-family:Trebuchet MS;color:#ff2800;font-size:24px;font-weight:bold;line-height:1.38">'. get_field( 'cta_title_' . $cta_num ) .'</div><br />';
															$data .= '<div style="font-family:Trebuchet MS;color:#888888;font-size:16px;">'. get_field( 'cta_blurb_' . $cta_num ) .'</div>';
														$data .= '</td>';
													$data .= '</tr>';
												$data .= '</tbody>';
											$data .= '</table><!--[if mso]>';
										$data .= '</td>';
										$data .= '<td width="50%" valign="top"><![endif]-->';
											$data .= '<table width="264" border="0" cellpadding="0" cellspacing="0" align="right" class="force-row">';
												$data .= '<tbody>';
													$data .= '<tr>';
														$data .= '<td align="right" class="col" valign="top" style="color:#ffffff;font-size:24px;font-family:Trebuchet MS;padding-right:10px;padding-top:10px;width:100%">';
															$data .= '<img alt="'. get_field( 'cta_title_' . $cta_num ) .'" src="'. get_field( ('cta_image_' . $cta_num ) ) .'" style="max-width:240px;min-width:240px" align="right">';
														$data .= '</td>';
													$data .= '</tr>';
												$data .= '</tbody>';
											$data .= '</table>';
											$data .= '<div style="clear:both;"></div><!--[if mso]>';
										$data .= '</td>';
									$data .= '</tr>';
								$data .= '</table><![endif]-->';
							$data .= '</td>';
						$data .= '</tr>';
						$data .= '<tr>';
							$data .= '<td align="center" style="font-size:10px"><br /><br />';
								$data .= '<table cellpadding="0" cellspacing="0" width="220" align="center">';
									$data .= '<tbody>';
										$data .= '<tr>';
											$data .= '<td style="height:86px;width:220px;max-width:220px;background-color:#008000;border-radius:5px;text-align:center" bgcolor="#008000">';
												$data .= '<a href="'. get_field( 'cta_url_' . $cta_num ) .'" style="font-family:Trebuchet MS;color:#ffffff;display:inline-block;font-size:20px;text-align:center;text-decoration:none;width:100%" target="_blank">'. get_field( 'cta_link_text_' . $cta_num ) .'</a>';
											$data .= '</td>';
										$data .= '</tr>';
									$data .= '</tbody>';
								$data .= '</table>';
								$data .= '<br>&nbsp;<br>';
							$data .= '</td>';
						$data .= '</tr>';
					$data .= '</tbody>';
				$data .= '</table>';
			$data .= '</td>';
		$data .= '</tr>';
	}

		return $data;
}

add_action( 'genesis_entry_content', 'rg_share_the_deals', 1 );

function rg_share_the_deals() {
	echo '<div style="text-align:center;width:100%;">';
		echo '<div style="width:600px; background-color: #AB8BAA; margin: 0 auto 20px;padding:10px 0;">';
			echo '<h2 style="color:#FFF;">Share Today\'s Newsletter</h2>';
				echo '<a href="https://www.facebook.com/sharer/sharer.php?u='. urlencode( get_permalink( get_the_ID() ) ) .'" target="_blank"><img src="http://runawaygoodness.com/wp-content/uploads/2015/09/facebook.jpg"></a> ';
				echo '<a href="https://twitter.com/home?status='. urlencode( 'Check out the amazing book deals from the Runaway Goodness newsletter! ' . get_permalink( get_the_ID() ) . ' @runawaygoodness' ) .'" target="_blank"><img src="http://runawaygoodness.com/wp-content/uploads/2015/09/twitter.jpg"></a> ';
				echo '<a href="https://plus.google.com/share?url='. urlencode( get_permalink( get_the_ID() ) ) .'" target="_blank"><img src="http://runawaygoodness.com/wp-content/uploads/2015/09/googleplus.jpg"></a> ';
				echo '<a href="mailto:?subject='. urlencode( 'Runaway Goodness ' . get_the_title() ).'&body=Check out these great deals I found in the Runaway Goodness newsletter!%0A%0A'. get_permalink( get_the_ID() ) .'" target="_blank"><img src="https://runawaygoodness.com/wp-content/uploads/2015/11/email-icon.jpg"></a>';
		echo '</div>';
	echo '</div>';
}

genesis();


