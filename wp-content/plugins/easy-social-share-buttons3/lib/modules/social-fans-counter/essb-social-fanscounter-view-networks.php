<?php $widget_columns = ESSBSocialFansCounterUtils::widget_columns(); // widget columns count   ?>
<?php $column_class = ESSBSocialFansCounterUtils::column_class(); // widget columns css class   ?>
<?php $show_numbers = ESSBSocialFansCounterUtils::show_numbers(); //   ?>
<?php $show_total = ESSBSocialFansCounterUtils::show_total(); //   ?>
<?php $total_type = ESSBSocialFansCounterUtils::get_total_type(); //   ?>
<?php $total_width = ESSBSocialFansCounterUtils::get_total_width(); ?>
<?php $total_text_pos = ESSBSocialFansCounterUtils::get_total_text_position(); ?>
<?php $new_window = ESSBSocialFansCounterUtils::new_window(); //   ?>
<?php $nofollow = ESSBSocialFansCounterUtils::nofollow(); //   ?>
<?php $show_diff = ESSBSocialFansCounterUtils::show_diff(); //   ?>
<?php $show_diff_lt_zero = ESSBSocialFansCounterUtils::show_diff_lt_zero(); //   ?>
<?php $fans_total_text = ESSBSocialFansCounterUtils::fans_text( 'total' ); //    ?>
<?php $fans_total = 0; //    ?>
<?php $total_css_bg_class = ESSBSocialFansCounterUtils::css_bg_class( 'total' ); // social background color css class   ?>
<?php $total_css_text_color_class = ESSBSocialFansCounterUtils::css_text_color_class( 'total' ); //   ?>
<?php $diff_count_text_color = ESSBSocialFansCounterUtils::diff_count_text_color(); ?>
<?php $diff_count_bg_color = ESSBSocialFansCounterUtils::diff_count_bg_color(); ?>
<?php $lazy_load = ESSBSocialFansCounterUtils::lazy_load(); ?>
<?php $animate_numbers = ESSBSocialFansCounterUtils::animate_numbers(); ?>
<?php $max_duration = ESSBSocialFansCounterUtils::animate_numbers(); ?>
<?php
$lazy_css_class = '';
$fans_count = '...';
$diff_count = 0;
$show_diff_lt_zero = false;

if ( $lazy_load ) {
  $lazy_css_class = "essbfc-widget-lazy";
  ?>
  <div class="essbfc-loader-holder">
  <ul class="essbfc-loader">
    <li></li>
    <li></li>
    <li></li>
    <li></li>
  </ul>
</div>
<?php }
?>
  <?php if ( $show_numbers && $show_numbers ) {
    $fans_total = ESSBSocialFansCounterUtils::total_fans();
    $css_total = ESSBSocialFansCounterUtils::css_total_class($total_type, $total_width);
  } ?>


<div class="<?php echo $lazy_css_class; ?>" data-hide_numbers="<?php echo (!$show_numbers) ? 1 : 0; ?>" data-show_total="<?php echo ($show_total) ? 1 : 0; ?>">
  <?php if ( count( ESSBSocialFansCounterUtils::enabled_socials() ) > 0 && $show_total && $show_numbers && $total_type == 'text' && $total_text_pos == 'top' ) { ?>
    <div  class="<?php echo $css_total; ?>" data-social="total">
      <div class="essbfc-front essbfc-total-astext">
          <span class="essbfc-total-astext-number"><?php echo $fans_total; ?></span>
          <small class="essbfc-total-astext-text"><?php _e( $fans_total_text , ESSB3_TEXT_DOMAIN ); ?></small>
        </div>
      </div>
<?php } ?>

  <?php foreach ( ESSBSocialFansCounterUtils::enabled_socials() as $social ) { ?>

    <?php $effect_class = ESSBSocialFansCounterUtils::effect_class( $social ); // current widget effect css class ?>
    <?php $shake_class = ESSBSocialFansCounterUtils::shake_class( $social ); // current widget shake css class ?>
    <?php $css_bg_class = ESSBSocialFansCounterUtils::css_bg_class( $social ); // social background color css class ?>
    <?php $css_text_color_class = ESSBSocialFansCounterUtils::css_text_color_class( $social ); // ?>
    <?php $css_icon_image_class = ESSBSocialFansCounterUtils::css_icon_image_class( $social ); // ?>
    <?php $css_icon_color_class = ESSBSocialFansCounterUtils::css_icon_color_class( $social ); // ?>
    <?php $css_sp_class = ESSBSocialFansCounterUtils::css_sp_class( $social ); // ?>
    <?php $css_hover_bg_color_class = ESSBSocialFansCounterUtils::css_hover_text_bg_color_class( $social ); // ?>
    <?php $css_hover_text_color_class = ESSBSocialFansCounterUtils::css_hover_text_color_class( $social ); // ?>
    <?php $social_url = ESSBSocialFansCounterUtils::social_url( $social ); // ?>
    <?php $fans_text = ESSBSocialFansCounterUtils::fans_text( $social ); // ?>
    <?php $fans_hover_text = ESSBSocialFansCounterUtils::fans_hover_text( $social ); //  ?>
    <?php if ( $show_numbers && !$lazy_load ) { $fans_count = ESSBSocialFansCounterUtils::fans_count( $social ); } ?>
    <?php if ( $show_numbers && $show_diff ) { $diff_count = ESSBSocialFansCounterUtils::get_social_diff( $social ); }  ?>
    <?php
      $social_data = '';
      if ( $animate_numbers && !$lazy_load && $show_numbers ) {

        $social_data .= 'data-count="' . ESSBSocialFansCounterUtils::fans_count( $social , false ) . '"';
        $social_data .= 'data-count_formated="' . $fans_count . '"';
      }
      ?>
  <div class="essbfc-block essbfc-view <?php echo $effect_class; ?> <?php if ( $effect_class == 'essbfc-no-effect' ) {  echo $shake_class;  } ?> <?php echo $column_class; ?>" data-social="<?php echo $social;?>" <?php echo $social_data;?>>
        <?php if ( $widget_columns > 1 ) { // more than 1 column   ?>
          <div class="essbfc-front <?php echo $css_bg_class; ?>">
            <?php if ( $show_numbers && $show_diff ) { ?>
              <?php if ( ($diff_count > 0 ) || ($diff_count < 0 && $show_diff_lt_zero ) ) { ?>
                <div class="weekly-added" style="<?php echo $diff_count_text_color; ?> <?php echo $diff_count_bg_color; ?>">
                  <i><?php if ( $diff_count > 0 ) {  echo '&#9650'; } else { echo '&#9660'; } ?></i>
                  <span><?php echo $diff_count; ?></span>
                </div>
              <?php } ?>
            <?php } ?>
            <a class="<?php echo $css_text_color_class; ?>"  href="<?php if ( $effect_class == 'essbfc-no-effect' ) {echo $social_url;} else {echo 'javascript:void(0);';} ?>" target="<?php echo $new_window; ?>" rel="<?php echo $nofollow; ?>">
              <i class="<?php echo $css_icon_image_class; ?> <?php echo $css_icon_color_class; ?>"></i>
              <?php if ( $show_numbers ) { // show numbers      ?>
                <div class="essbfc-spe <?php echo $css_sp_class; ?>"></div>
                <span class="essbfc-social-count <?php echo $css_text_color_class; ?>"><?php echo $fans_count; ?></span>
                <div class="clearfix"></div>
                <small class="<?php echo $css_text_color_class; ?>"><?php _e( $fans_text , ESSB3_TEXT_DOMAIN ); ?></small>
              <?php } // end show numbers    ?>
            </a>
          </div>
          <div class="essbfc-back essbfc-mask <?php echo $shake_class; ?>">
            <a href="<?php echo $social_url; ?>" class="essbfc-join btn  btn-xs shake shake-slow <?php echo $css_hover_bg_color_class; ?> <?php echo $css_hover_text_color_class; ?>" target="<?php echo $new_window; ?>" rel="<?php echo $nofollow; ?>"><?php _e( $fans_hover_text , ESSB3_TEXT_DOMAIN ); ?></a>
          </div>
        <?php } else { ?>
          <div class="<?php echo $css_bg_class; ?> <?php echo $effect_class; ?> <?php if ( $effect_class == 'essbfc-no-effect' ) {  echo $shake_class;  } ?> essbfc-col-exception">
            <div class="essbfc-col-one-icon pull-left">
              <a class="<?php echo $css_text_color_class; ?>" href="<?php if ( $effect_class == 'essbfc-no-effect' ) { echo $social_url; } else { echo 'javascript:void(0);'; } ?>" target="<?php echo $new_window; ?>" rel="<?php echo $nofollow; ?>">
                <i class="<?php echo $css_icon_image_class; ?> <?php echo $css_icon_color_class; ?>"></i>
              </a>
              <?php if ( $show_numbers && $show_diff ) { ?>
                <?php if ( ($diff_count > 0 ) || ($diff_count < 0 && $show_diff_lt_zero ) ) { ?>
                  <div class="weekly-added weekly-added-onecolumn" style="<?php echo $diff_count_text_color; ?> <?php echo $diff_count_bg_color; ?>">
                    <i><?php if ( $diff_count > 0 ) { echo '&#9650'; } else { echo '&#9660'; } ?></i><span><?php echo $diff_count; ?></span>
                  </div>
                <?php } ?>
              <?php } ?>
            </div><!-- End essbfc-col-one-icon -->
            <div class="essbfc-front pull-right">
              <?php if ( $show_numbers ) { ?>
                  <a class="<?php echo $css_text_color_class; ?>" href="<?php echo $social_url; ?>" target="<?php echo $new_window; ?>" rel="<?php echo $nofollow; ?>">
                    <span class="essbfc-social-count <?php echo $css_text_color_class; ?>"><?php echo $fans_count; ?></span>
                    <div class="clearfix"></div>
                    <small class="<?php echo $css_text_color_class; ?>"><?php _e( $fans_text , ESSB3_TEXT_DOMAIN ); ?></small>
                  </a>
              <?php } ?>
            </div><!-- End essbfc-front -->
            <div class="essbfc-back essbfc-mask pull-right <?php echo $shake_class; ?>">
              <a href="<?php echo $social_url; ?>" class="essbfc-join btn <?php echo $css_hover_bg_color_class; ?> <?php echo $css_hover_text_color_class; ?> btn-xs" target="<?php echo $new_window; ?>"><?php _e( $fans_hover_text , ESSB3_TEXT_DOMAIN ); ?></a>
            </div><!-- End essbfc-back -->
          </div><!-- End essbfc-col-exception -->
        <?php } ?>
    </div><!-- End essbfc-block -->
<?php } ?>


  <?php if ( count( ESSBSocialFansCounterUtils::enabled_socials() ) > 0 && $show_total && $show_numbers && $total_type != 'text' ) { ?>
  <?php $css_icon_color_class = ESSBSocialFansCounterUtils::css_icon_color_class( 'total' ); // ?>
    <div class="<?php echo $css_total; ?>"  data-social="total">
      <div class="essbfc-front <?php echo $total_css_bg_class; ?>">
        <div class="essbfc-love <?php echo $total_css_text_color_class; ?>">
          <i class="-essbfc-icon-heart <?php echo $css_icon_color_class; ?>"></i>
          <div class="essbfc-spe "></div>
          <span class="<?php echo $total_css_text_color_class; ?>"><?php echo $fans_total; ?></span>
          <div class="clearfix"></div>
          <small class="<?php echo $total_css_text_color_class; ?>"><?php _e( $fans_total_text , ESSB3_TEXT_DOMAIN ); ?></small>
        </div>
      </div>
    </div>
<?php } ?>
  <?php if ( count( ESSBSocialFansCounterUtils::enabled_socials() ) > 0 && $show_total && $show_numbers && $total_type == 'text' && $total_text_pos == 'bottom' ) { ?>
    <div  class="<?php echo $css_total; ?>" data-social="total">
      <div class="essbfc-front essbfc-total-astext">
          <span class="essbfc-total-astext-number"><?php echo $fans_total; ?></span>
          <small class="essbfc-total-astext-text"><?php _e( $fans_total_text , ESSB3_TEXT_DOMAIN ); ?></small>
        </div>
      </div>
    
<?php } ?>
<div style="clear: both;"></div>
</div>