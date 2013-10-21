<?php
/*
Plugin Name: Meetup Widget
Plugin URI: https://github.com/code4sac/wp-brigade-widgets
Description: A Sidebar widget to display meetups from Meetup.com
Version: 0.1
Author: Kaleb Clark (Code4Sac)
Author URI: https://github.com/KalebClark
*/

class bw_meetup extends WP_Widget {
  public function __construct() {
    parent::__construct(
      'bw_meetup', 
      __('Brigade Meetup Widget', 'text_domain'),
      array('description' => __('A Meetup Widget', 'text_domain'), )
    );
  }

  public function widget($args, $instance) {
    $group_name   = $instance['group_name'];

    $request = WP_Http;

    $url  = "https://api.meetup.com/2/events?";
    $url .= "&sign=true";
    $url .= "&group_urlname=".$group_name;
    $url .= "&page=".$instance['num_to_show'];
    $url .= "&key=".$instance['api_key'];

    print $url;


    $res      = wp_remote_get($url);
    $events   = json_decode($res['body'], true);
    ?>
    <style>
    <?php include('brigade-widgets.css'); ?>
    .meetup-widget-date{
    }
    .meetup-widget-time {

    }
    .meetup-widget-title {
      font-weight: bold;
      text-decoration: underline;
    }
    .meetup-widget-rsvp {
      cursor: pointer;
      font-weight: bolder;
      text-align: center;
      height: 23px;
      width: 100px;
      margin-top: 10px;
      margin-right: 4px;
      margin-left: 4px;
      background-color: #62ac75;
      border: 1px solid #000;
      display: block;
      text-decoration: none;
      color: #000;
    }
    .meetup-widget-rsvp  {
      color: #000; 
    }
    .meetup-widget-rsvp:hover {
      color: #000;
      text-decoration: none;
    }
    .meetup-widget-rsvp:visited {
      color: #000;
    }
    .meetup-widget-count {
      margin-top: 4px;
      padding-top: 5px;
      padding-left: 25px;
      margin-bottom: 20px;
    }
    .fleft {
      float: left;
    }
    </style>
    <div class="brigade-widget-box">
    <div class="brigade-widget-header">
      <?php echo $instance['bw_title'];?>
    </div>
    <?php
    foreach($events['results'] as $event) {
      if($event['status'] != 'upcoming') { continue; }    // Bail if not upcoming event
      /* Format Time
       * =========== */
      $offset = $event['utc_offset'];
      $offset_dir = substr($offset, 0, 1);
      $event_time = $event['time'];
      if($offset_dir == '-') {
        $event_time = $event_time - substr($offset, 1, 20);
      } elseif($offset_dir == '+') {
        $event_time = $event_time + substr($offset, 1, 20);
      }
      $event_time_utc = $event_time / 1000;
      $event_time     = date('g:ia', $event_time_utc);
      $event_date     = date('D M d', $event_time_utc);

      $event_title  = $event['name'];

      $attending    = $event['yes_rsvp_count'];

      $meetup_id    = $event['id'];
      $meetup_page  = "http://www.meetup.com/".$group_name."/events/".$meetup_id;

      ?>
      <div class="meetup-widget-event">
        <div class="meetup-widget-title"><a href="<?php echo $meetup_page;?>"><?php echo $event_title;?></a></div>
        <div class="meetup-widget-date"><?php echo $event_date."&nbsp;&nbsp".$event_time;?></div>
        <a href="<?php echo $meetup_page;?>" class="fleft meetup-widget-rsvp">RSVP</a>
        <div class="meetup-widget-count"><?php echo $attending;?> attending</div>
        
      </div>
      <?php
    }
    ?></div>
    <?php

  }

  public function form($instance) {
    $instance = wp_parse_args( (array) $instance, array(
      'bw_title'    => 'Widget Title',
      'group_name'  => 'Meetup Groupname',
      'api_key'     => 'Your API Key',
      'num_to_show' => '4'
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

    <label for="<?php echo $this->get_field_id('group_name'); ?>">
      <?php _e('API Group Name:'); ?>
    </label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('group_name'); ?>" name="<?php echo $this->get_field_name('group_name'); ?>" value="<?php echo esc_attr($group_name); ?>"/>

    <label for="<?php echo $this->get_field_id('api_key'); ?>">
      <?php _e('API Key:'); ?>
    </label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('api_key'); ?>" name="<?php echo $this->get_field_name('api_key'); ?>" value="<?php echo esc_attr($api_key); ?>"/>
    </p>

    <label for="<?php echo $this->get_field_id('num_to_show'); ?>">
      <?php _e('Number of meetups to show:'); ?>
    </label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('num_to_show'); ?>" name="<?php echo $this->get_field_name('num_to_show'); ?>" value="<?php echo esc_attr($num_to_show); ?>"/>
    </p>
    <?php
  }

  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['bw_title']   = (!empty($new_instance['bw_title']))   ? strip_tags($new_instance['bw_title'])   : '';
    $instance['group_name'] = (!empty($new_instance['group_name'])) ? strip_tags($new_instance['group_name']) : '';
    $instance['api_key']    = (!empty($new_instance['api_key']))    ? strip_tags($new_instance['api_key'])    : '';
    $instance['num_to_show']= (!empty($new_instance['num_to_show']))? strip_tags($new_instance['num_to_show']): '';

    return $instance;
  }
}

/* Register Widget
 * =============== */
add_action( 'widgets_init',
  create_function('', 'return register_widget("bw_meetup");')
);
