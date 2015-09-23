<?php

add_action( 'widgets_init' , create_function( '' , 'return register_widget( "ESSBSocialFansCounterWidget" );' ) );

class ESSBSocialFansCounterWidget extends WP_Widget {

  public function __construct() {

    $options = array( 'description' => __( 'Social Fans Counter' , ESSB3_TEXT_DOMAIN ) );

    parent::__construct( false , __( 'Easy Social Share Buttons: Fans Counter' , ESSB3_TEXT_DOMAIN ) , $options );

  }

  public function form( $instance ) {

    $defaults = array(
        'title' => 'Social Fans' ,
        'new_window' => 1 ,
        'nofollow' => 1 ,
        'hide_numbers' => 0 ,
        'hide_title' => 1 ,
        'show_total' => 1 ,
        'box_width' => '' ,
        'is_lazy' => 0 ,
        'animate_numbers' => 0 ,
        'max_duration' => 5 ,
        'columns' => 3 ,
        'effects' => 'essbfc-no-effect' ,
        'shake' => '' ,
        'icon_color' => 'light' ,
        'bg_color' => 'colord' ,
        'hover_text_color' => 'light' ,
        'hover_text_bg_color' => 'colord' ,
        'show_diff' => 0 ,
        'show_diff_lt_zero' => 0 ,
        'diff_count_text_color' => '' ,
        'diff_count_bg_color' => '' ,
    	'template' => 'flat',
    );

    $instance = wp_parse_args( ( array ) $instance , $defaults );

    ?>

<p>
  <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo __( 'Title' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" id="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat" value="<?php echo $instance['title']; ?>" />
</p>
<p>
  <label for="<?php echo $this->get_field_id( 'hide_title' ); ?>"><?php echo __( 'Do not show widget title' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <input type="checkbox" name="<?php echo $this->get_field_name( 'hide_title' ); ?>" id="<?php echo $this->get_field_id( 'hide_title' ); ?>" value="1" <?php if ( 1 == $instance['hide_title'] ) { echo ' checked="checked"'; } ?> />
  <span style="font-weight: 700; font-size: 0.9em"><em></em></span>
</p>
<p>
  <label for="<?php echo $this->get_field_id( 'new_window' ); ?>"><?php echo __( 'Open links in new window' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <input type="checkbox" name="<?php echo $this->get_field_name( 'new_window' ); ?>" id="<?php echo $this->get_field_id( 'new_window' ); ?>" value="1" <?php if ( 1 == $instance['new_window'] ) { echo ' checked="checked"'; } ?> />
</p>

<p>
  <label for="<?php echo $this->get_field_id( 'nofollow' ); ?>"><?php echo __( 'Add nofollow to links' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <input type="checkbox" name="<?php echo $this->get_field_name( 'nofollow' ); ?>" id="<?php echo $this->get_field_id( 'nofollow' ); ?>" value="1" <?php if ( 1 == $instance['nofollow'] ) { echo ' checked="checked"'; } ?> />
</p>


<p>
  <label for="<?php echo $this->get_field_id( 'show_total' ); ?>"><?php echo __( 'Display total number of fans' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <input type="checkbox" name="<?php echo $this->get_field_name( 'show_total' ); ?>" id="<?php echo $this->get_field_id( 'show_total' ); ?>" value="1" <?php if ( 1 == $instance['show_total'] ) { echo ' checked="checked"'; } ?> />
</p>

<p>
  <label for="<?php echo $this->get_field_id( 'box_width' ); ?>"><?php echo __( 'Custom buttons element width' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <input type="text" name="<?php echo $this->get_field_name( 'box_width' ); ?>" id="<?php echo $this->get_field_id( 'box_width' ); ?>" value="<?php echo $instance['box_width'];?>" size="5" /> px
  <span style="font-weight: 300; font-size: 0.9em"><br /><em><?php echo __( 'Provide custom width of buttons element' , ESSB3_TEXT_DOMAIN ); ?></em></span>
</p>

<p>
  <label for="<?php echo $this->get_field_id( 'animate_numbers' ); ?>"><?php echo __( 'Animate number of followers' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <input type="checkbox" name="<?php echo $this->get_field_name( 'animate_numbers' ); ?>" id="<?php echo $this->get_field_id( 'animate_numbers' ); ?>" value="1" <?php if ( 1 == $instance['animate_numbers'] ) { echo ' checked="checked"'; } ?> />
</p>

<p>
  <label for="<?php echo $this->get_field_id( 'max_duration' ); ?>"><?php echo __( 'Animation duration' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <input type="text" name="<?php echo $this->get_field_name( 'max_duration' ); ?>" id="<?php echo $this->get_field_id( 'max_duration' ); ?>" value="<?php echo $instance['max_duration'];?>" />
</p>
<h5 style="background-color: #efefef; padding: 6px 3px;">Visual settings</h5>
<p>
  <label for="<?php echo $this->get_field_id( 'columns' ); ?>"><?php echo __( 'Columns' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <select name="<?php echo $this->get_field_name( 'columns' ); ?>" id="<?php echo $this->get_field_id( 'columns' ); ?>" class="widefat">
    <option value="1" <?php if ( $instance['columns'] == 1 ) { echo 'selected="selected"'; } ?>>1 <?php echo __( 'Column' , ESSB3_TEXT_DOMAIN ); ?></option>
    <option value="2" <?php if ( $instance['columns'] == 2 ) { echo 'selected="selected"'; } ?>>2 <?php echo __( 'Columns' , ESSB3_TEXT_DOMAIN ); ?></option>
    <option value="3" <?php if ( $instance['columns'] == 3 ) { echo 'selected="selected"'; } ?>>3 <?php echo __( 'Columns' , ESSB3_TEXT_DOMAIN ); ?></option>
    <option value="4" <?php if ( $instance['columns'] == 4 ) { echo 'selected="selected"'; } ?>>4 <?php echo __( 'Columns' , ESSB3_TEXT_DOMAIN ); ?></option>
    <option value="5" <?php if ( $instance['columns'] == 5 ) { echo 'selected="selected"'; } ?>>5 <?php echo __( 'Columns' , ESSB3_TEXT_DOMAIN ); ?></option>
        <option value="6" <?php if ( $instance['columns'] == 6 ) { echo 'selected="selected"'; } ?>>6 <?php echo __( 'Columns' , ESSB3_TEXT_DOMAIN ); ?></option>
        <option value="7" <?php if ( $instance['columns'] == 7 ) { echo 'selected="selected"'; } ?>>7 <?php echo __( 'Columns' , ESSB3_TEXT_DOMAIN ); ?></option>
        <option value="8" <?php if ( $instance['columns'] == 8 ) { echo 'selected="selected"'; } ?>>8 <?php echo __( 'Columns' , ESSB3_TEXT_DOMAIN ); ?></option>
        <option value="9" <?php if ( $instance['columns'] == 9 ) { echo 'selected="selected"'; } ?>>9 <?php echo __( 'Columns' , ESSB3_TEXT_DOMAIN ); ?></option>
                <option value="10" <?php if ( $instance['columns'] == 10 ) { echo 'selected="selected"'; } ?>>10 <?php echo __( 'Columns' , ESSB3_TEXT_DOMAIN ); ?></option>
                <option value="11" <?php if ( $instance['columns'] == 11 ) { echo 'selected="selected"'; } ?>>11 <?php echo __( 'Columns' , ESSB3_TEXT_DOMAIN ); ?></option>
                <option value="12" <?php if ( $instance['columns'] == 12 ) { echo 'selected="selected"'; } ?>>12 <?php echo __( 'Columns' , ESSB3_TEXT_DOMAIN ); ?></option>
        </select>
</p>

<p>
  <label for="<?php echo $this->get_field_id( 'template' ); ?>"><?php echo __( 'Template' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <select name="<?php echo $this->get_field_name( 'template' ); ?>" id="<?php echo $this->get_field_id( 'template' ); ?>" class="widefat">
    <option value="color" <?php if ( $instance['template'] == 'color' ) { echo 'selected="selected"'; } ?>>Color icons</option>
    <option value="roundcolor" <?php if ( $instance['template'] == 'roundcolor' ) { echo 'selected="selected"'; } ?>>Round Color icons</option>
    <option value="grey" <?php if ( $instance['template'] == 'grey' ) { echo 'selected="selected"'; } ?>>Grey icons</option>
    <option value="roundgrey" <?php if ( $instance['template'] == 'roundgrey' ) { echo 'selected="selected"'; } ?>>Round Grey icons</option>
    <option value="metro" <?php if ( $instance['template'] == 'metro' ) { echo 'selected="selected"'; } ?>>Metro</option>
        <option value="flat" <?php if ( $instance['template'] == 'flat' ) { echo 'selected="selected"'; } ?>>Flat</option>
        <option value="dark" <?php if ( $instance['template'] == 'dark' ) { echo 'selected="selected"'; } ?>>Dark</option>
        <option value="lite" <?php if ( $instance['template'] == 'lite' ) { echo 'selected="selected"'; } ?>>Lite icons on transparent background</option>
        <option value="grey-transparent" <?php if ( $instance['template'] == 'grey-transparent' ) { echo 'selected="selected"'; } ?>>Grey icons on transparent background</option>
        <option value="color-transparent" <?php if ( $instance['template'] == 'color-transparent' ) { echo 'selected="selected"'; } ?>>Color icons on transparent background</option>
    <option value="tinycolor" <?php if ( $instance['template'] == 'tinycolor' ) { echo 'selected="selected"'; } ?>>Tiny Color</option>
    <option value="tinylight" <?php if ( $instance['template'] == 'tinylight' ) { echo 'selected="selected"'; } ?>>Tiny Light</option>
        <option value="tinygrey" <?php if ( $instance['template'] == 'tinygrey' ) { echo 'selected="selected"'; } ?>>Tiny Grey</option>
    </select>
</p>

<p>
  <label for="<?php echo $this->get_field_id( 'effects' ); ?>"><?php echo __( 'Display follow text on hover' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <select name="<?php echo $this->get_field_name( 'effects' ); ?>" id="<?php echo $this->get_field_id( 'effects' ); ?>" class="widefat">
    <option value="essbfc-no-effect" <?php if ( $instance['effects'] == 'essbfc-no-effect' ) { echo 'selected="selected"'; } ?>><?php echo __( 'No hover text' , ESSB3_TEXT_DOMAIN ); ?></option>
    <option value="essbfc-view-first" <?php if ( $instance['effects'] == 'essbfc-view-first' ) { echo 'selected="selected"'; } ?>><?php echo __( 'Design' , ESSB3_TEXT_DOMAIN ); ?> 1</option>
    <option value="essbfc-view-two" <?php if ( $instance['effects'] == 'essbfc-view-two' ) { echo 'selected="selected"'; } ?>><?php echo __( 'Design' , ESSB3_TEXT_DOMAIN ); ?> 2</option>
    <option value="essbfc-view-three" <?php if ( $instance['effects'] == 'essbfc-view-three' ) { echo 'selected="selected"'; } ?>><?php echo __( 'Design' , ESSB3_TEXT_DOMAIN ); ?> 3</option>
  </select>
</p>
<h5 style="background-color: #efefef; padding: 6px 3px;">On hover text design</h5>

<p>
  <label for="<?php echo $this->get_field_id( 'hover_text_color' ); ?>"><?php echo __( 'Hover Text Color' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <select name="<?php echo $this->get_field_name( 'hover_text_color' ); ?>" id="<?php echo $this->get_field_id( 'hover_text_color' ); ?>" class="widefat">
    <option value="light" <?php if ( $instance['hover_text_color'] == 'light' ) { echo 'selected="selected"'; } ?>><?php echo __( 'Light' , ESSB3_TEXT_DOMAIN ); ?></option>
    <option value="dark" <?php if ( $instance['hover_text_color'] == 'dark' ) { echo 'selected="selected"'; } ?>><?php echo __( 'Dark' , ESSB3_TEXT_DOMAIN ); ?></option>
    <option value="colord" <?php if ( $instance['hover_text_color'] == 'colord' ) { echo 'selected="selected"'; } ?>><?php echo __( 'Colored' , ESSB3_TEXT_DOMAIN ); ?></option>
  </select>
</p>

<p>
  <label for="<?php echo $this->get_field_id( 'hover_text_bg_color' ); ?>"><?php echo __( 'Hover Text Background Color' , ESSB3_TEXT_DOMAIN ); ?>:</label>
  <select name="<?php echo $this->get_field_name( 'hover_text_bg_color' ); ?>" id="<?php echo $this->get_field_id( 'hover_text_bg_color' ); ?>" class="widefat">
    <option value="light" <?php if ( $instance['hover_text_bg_color'] == 'light' ) { echo 'selected="selected"'; } ?>><?php echo __( 'Light' , ESSB3_TEXT_DOMAIN ); ?></option>
    <option value="dark" <?php if ( $instance['hover_text_bg_color'] == 'dark' ) { echo 'selected="selected"'; } ?>><?php echo __( 'Dark' , ESSB3_TEXT_DOMAIN ); ?></option>
    <option value="colord" <?php if ( $instance['hover_text_bg_color'] == 'colord' ) { echo 'selected="selected"'; } ?>><?php echo __( 'Colored' , ESSB3_TEXT_DOMAIN ); ?></option>
  </select>
</p>
    
    
    <?php 

  }

  public function update( $new_instance , $old_instance ) {

    $instance = $old_instance;

    $instance['title']                 = trim( $new_instance['title'] );

    $instance['box_width']             = intval( $new_instance['box_width'] );
    $instance['columns']               = intval( $new_instance['columns'] );
    $instance['effects']               = $new_instance['effects'];
    $instance['shake']                 = $new_instance['shake'];

    $instance['hide_numbers']          = $new_instance['hide_numbers'];
    $instance['hide_title']            = $new_instance['hide_title'];
    $instance['new_window']            = $new_instance['new_window'];
    $instance['nofollow']              = $new_instance['nofollow'];
    $instance['show_total']            = $new_instance['show_total'];

    $instance['icon_color']            = $new_instance['icon_color'];
    $instance['bg_color']              = $new_instance['bg_color'];
    $instance['hover_text_color']      = $new_instance['hover_text_color'];
    $instance['hover_text_bg_color']   = $new_instance['hover_text_bg_color'];

    $instance['show_diff']             = $new_instance['show_diff'];
    $instance['show_diff_lt_zero']     = $new_instance['show_diff_lt_zero'];
    $instance['diff_count_text_color'] = $new_instance['diff_count_text_color'];
    $instance['diff_count_bg_color']   = $new_instance['diff_count_bg_color'];

    $instance['is_lazy']               = $new_instance['is_lazy'];
    $instance['animate_numbers']       = $new_instance['animate_numbers'];
    $instance['max_duration']          = $new_instance['max_duration'];
    $instance['template']          	   = $new_instance['template'];

    return $instance;

  }

  public function widget( $args , $instance ) {

    extract( $args );

    $before_widget = $args['before_widget'];
    $before_title  = $args['before_title'];
    $after_title   = $args['after_title'];
    $after_widget  = $args['after_widget'];
    $title         = $instance['title'];
    // register current widget options
    ESSBSocialFansCounterUtils::register_options( ( array ) $instance );
    
    include ESSB3_PLUGIN_ROOT . 'lib/modules/social-fans-counter/essb-social-fanscounter-widget-view.php';

  }

}