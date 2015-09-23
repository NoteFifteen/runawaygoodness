<?php $show_title = ESSBSocialFansCounterUtils::show_title(); ?>
<?php $box_width = ESSBSocialFansCounterUtils::box_width(); // widget columns count       ?>
<?php $lazy_load = ESSBSocialFansCounterUtils::lazy_load(); ?>
<?php $animate_numbers = ESSBSocialFansCounterUtils::animate_numbers(); ?>
<?php $max_duration = ESSBSocialFansCounterUtils::animate_numbers(); ?>
<?php
$data = '';

if ( $animate_numbers ) {
  $data .= 'data-animate_numbers="1" ';
}

if ( $lazy_load ) {
  $data .= 'data-is_lazy="1" ';
}

if ( $max_duration ) {
  $data .= 'data-duration="' . $max_duration . '" ';
}
?>
<?php if ( $show_title ) { ?>
  <?php echo $before_widget; ?>
  <?php echo $before_title; ?>
  <?php echo $title; ?>
    <?php echo $after_title; ?>
  <div class="essbfc-widget-holder" style="<?php echo $box_width; ?>" <?php echo $data; ?>>
    <?php } else { ?>
    <div class="essbfc-widget-holder" style="<?php echo $box_width; ?>" <?php echo $data; ?>>
    <?php } ?>
    <?php include 'essb-social-fanscounter-view-networks.php'; ?>
  <?php if ( $show_title ) { ?>
    </div>
    <?php echo $after_widget; ?>
<?php } else { ?>
  </div>
<?php } ?>
        
