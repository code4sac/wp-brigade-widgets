<?php
/*
Plugin Name: Project Widget
Plugin URI: https://github.com/code4sac/wp-brigade-widgets
Description: A Sidebar widget to display project status based on ACF Fields
Version: 0.1
Author: Kaleb Clark (Code4Sac)
Author URI: https://github.com/KalebClark
*/

class bw_project extends WP_Widget {
  public function __construct() {
    parent::__construct(
      'bw_project', 
      __('Brigade Project Widget', 'text_domain'),
      array('description' => __('A Project Status Widget', 'text_domain'), )
    );
  }

  public function widget($args, $instance) {
    $group_name           = $instance['group_name'];
    $project              = Array();
    $project['title']     = get_the_title($post->post_parent);
    $project['state']     = get_field('project_state');
    $project['forum']     = get_field('project_forum');
    $project['startdate'] = get_field('project_startdate');
    $project['trello']    = get_field('project_trello');

/*  Checks to see if anything is empty - implement later
    if(in_array("", $project)) {
      print "Harras a Captain, they forgot to set something up!";
    }
*/
    ?>
    <style><?php include('brigade-widgets.css');?></style>
    <div class="brigade-widget-box">
    <div class="brigade-widget-header"><?php echo $project['title'];?></div>
    <table>
      <tr>
        <th>Start Date:</th>
        <td><?php echo $project['startdate'];?></td>
      </tr>
      <tr>
        <th>State:</th>
        <td><?php echo $project['state'];?></td>
      </tr>
      <tr>
        <th>Discussion:</th>
        <td>
          <a target="_blank" href="<?php echo $project['forum'];?>">Google Groups</a>
        </td>
      </tr>
      <!-- Check for Trello -->
      <?php if($project['trello'] != '') { ?>
      <tr>
        <th>Trello Board:</th>
        <td>
          <a target="_blank" href="<?php echo $project['trello'];?>">View Board</a>
        </td>
      </tr>
      <?php } ?><!-- End Trello Check -->
      <!-- Check for Asana -->
      <?php if($project['asana'] != '') { ?>
      <tr>
        <th>Asana Page:</th>
        <td>
          <a target="_blank" href="<?php echo $project['asana'];?>">View Page</a>
        </td>
      </tr>
      <?php } ?><!-- End Trello Check -->
    </table>
    </div>
      <!--
      <div><strong>Start Date: </strong></div><div><?php echo $project['startdate']; ?></div>
      <div><strong>Project State: </strong></div><div><?php echo $project['state']; ?></div>
      <div><a href="<?php echo $project['forum'];?>">Project Discussion</a></div><div> on Google Groups</div>
    </div>
      -->
    <?php
  }

  public function form($instance) {
    $instance = wp_parse_args( (array) $instance, array(
      'bw_title'    => 'Widget Title'
    ));
    foreach($instance as $field => $val) {
      if( isset( $instance[$field])) {
        $$field = strip_tags( $instance[$field]);
      }
    }

    ?>
    <p>
    <label for="<?php echo $this->get_field_id('bw_title'); ?>">
      <?php _e('Widget Title:'); ?>
    </label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('bw_title'); ?>" name="<?php echo $this->get_field_name('bw_title'); ?>" value="<?php echo esc_attr($bw_title); ?>"/>
    <?php
  }

  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['bw_title']   = (!empty($new_instance['bw_title']))   ? strip_tags($new_instance['bw_title'])   : '';

    return $instance;
  }
}

/* Register Widget
 * =============== */
add_action( 'widgets_init',
  create_function('', 'return register_widget("bw_project");')
);
