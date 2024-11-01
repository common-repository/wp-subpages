<?php
/*
Plugin Name: WP Subpages
Plugin URI: http://blondish.net/wp-subpages-widget/
Description: WP Subpages is a simple plugin to allow for multiple widget instances to show child pages.
Author: Nile Flores
Version: 1.2
Author URI: http://www.blondish.net/
*/

class SubpagesWidget extends WP_Widget {
  private $defaults = array(
                          'parentid' => -1,
                          'usecurrentparent' => 0,
                          'useparenttitle' => 1,
                          'customtitle' => '',
                          'showsubpages' => 1
                        );  
  
  function SubpagesWidget() {
    $options = array(
                  'classname' => 'SubpagesWidget',
                  'description' => 'SubpagesWidget Description'
                );
    $this->WP_Widget('SubpagesWidget', 'Subpages Widget', $options);             
    add_filter( 'plugin_action_links', array(&$this, 'plugin_action_links'), 10, 2 );

/* Instances */
  }
  
  function widget($args, $instance) {
    extract($args, EXTR_SKIP);
    
    if ($instance['usecurrentparent'] == 1) {
      if (is_page()) {
        $parentid = get_the_ID(); 
      } else {
        return;
      }
    } else {
      $parentid = $instance['parentid'];
    }
    
    if ($instance['useparenttitle'] == 1) {
      $details = get_post($parentid);
      $title = $details->post_title;
    } else {
      $title = $instance['customtitle'];
    }

/* Retrieve WP child pages of parent */

    $output = wp_list_pages("title_li=0&sort_column=menu_order&echo=0&child_of=".$parentid);
    
    if (stripos($output, "Start CustomMenuLinks Ver") !== false) {
      $before = substr($output, 0, stripos($output, '<!-- Start CustomMenuLinks'));
      $after = substr($output, stripos($output, '<!-- End CustomMenuLinks -->') + 30, strlen($output));
      $output = $before.$after;
    }
    
    if (strlen($output) > 0) {
      echo $before_widget;
      echo $before_title.$title.$after_title;
      echo "<ul>\n";
      echo $output;
      echo "</ul>\n";
          
      echo $after_widget;
    }
  }
  
  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['parentid'] = $new_instance['parentid'];
    $instance['usecurrentparent'] = $new_instance['usecurrentparent'];
    $instance['useparenttitle'] = $new_instance['useparenttitle'];
    $instance['customtitle'] = $new_instance['customtitle'];
    $instance['showsubpages'] = $new_instance['showsubpages'];
    
    return $instance;
  }
  
  function form($instance) {
    $instance = wp_parse_args( (array) $instance, $this->defaults);
    $parentid = $instance['parentid'];
    $usecurrentparent = $instance['usecurrentparent'];
    $useparenttitle = $instance['useparenttitle'];
    $customtitle = $instance['customtitle'];
    $showsubpages = $instance['showsubpages'];
?>

/* Widget option output html */
  <p>
    <label for="<?php echo $this->get_field_id('usecurrentparent'); ?>">Use current page as parent:</label><br />
    <input type="radio" id="<?php echo $this->get_field_id('usecurrentparent'); ?>" name="<?php echo $this->get_field_name('usecurrentparent'); ?>" value="1" <?php if ($instance['usecurrentparent'] == 1) { echo "checked='checked'"; } ?>>Yes
    <input type="radio" id="<?php echo $this->get_field_id('usecurrentparent'); ?>" name="<?php echo $this->get_field_name('usecurrentparent'); ?>" value="0" <?php if ($instance['usecurrentparent'] == 0) { echo "checked='checked'"; } ?>>No
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('parentid'); ?>">Parent Page<?php if ($instance['usecurrentparent'] == 1) { echo " (<i>Using current page as parent this setting is ignored</i>)"; } ?>:
      <?php
        wp_dropdown_pages(array('selected' => $parentid, 'name' => $this->get_field_name('parentid'), 'sort_column'=> 'menu_order, post_title'));
      ?>
    </label>
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('useparenttitle'); ?>">Use Parent Title<?php if ($instance['usecurrentparent'] == 1) { echo " (<i>Using current page as parent this setting is recommended to be set to yes</i>)"; } ?>:</label><br />
    <input type="radio" id="<?php echo $this->get_field_id('useparenttitle'); ?>" name="<?php echo $this->get_field_name('useparenttitle'); ?>" value="1" <?php if ($instance['useparenttitle'] == 1) { echo "checked='checked'"; } ?>>Yes
    <input type="radio" id="<?php echo $this->get_field_id('useparenttitle'); ?>" name="<?php echo $this->get_field_name('useparenttitle'); ?>" value="0" <?php if ($instance['useparenttitle'] == 0) { echo "checked='checked'"; } ?>>No
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('customtitle'); ?>">Custom Title:
      <input class="widefat" id="<?php echo $this->get_field_id('customtitle'); ?>" name="<?php echo $this->get_field_name('customtitle'); ?>" type="text" value="<?php echo attribute_escape($customtitle); ?>" />
    </label>
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('showsubpages'); ?>">Show Subpages:</label><br />
    <input type="radio" id="<?php echo $this->get_field_id('showsubpages'); ?>" name="<?php echo $this->get_field_name('showsubpages'); ?>" value="1" <?php if ($instance['showsubpages'] == 1) { echo "checked='checked'"; } ?>>Yes
    <input type="radio" id="<?php echo $this->get_field_id('showsubpages'); ?>" name="<?php echo $this->get_field_name('showsubpages'); ?>" value="0" <?php if ($instance['showsubpages'] == 0) { echo "checked='checked'"; } ?>>No
  </p>

<?php
  }

  function plugin_action_links( $links, $file ) {
    static $this_plugin;
    
    if( empty($this_plugin) )
      $this_plugin = plugin_basename(__FILE__);

    if ( $file == $this_plugin )
      $links[] = '<a href="' . admin_url( 'widgets.php' ) . '">Widgets</a>';

    return $links;
  }
  
}

add_action('widgets_init', create_function('', 'return register_widget("SubpagesWidget");'));

?>