<?php
/*
Plugin Name: Meetup Widget
Plugin URI: https://github.com/code4sac/wp-github-widget
Description: A Sidebar widget to display meetups from Meetup.com
Version: 0.1
Author: Kaleb Clark (Code4Sac)
Author URI: https://github.com/KalebClark
*/

class cfa_meetup extends WP_Widget {
  public function __construct() {
    parent::__construct(
      'meetup_widget', 
      __('Meetup Widget', 'text_domain'),
      array('description' => __('A Meetup Widget', 'text_domain'), )
    );
  }

  public function widget($args, $instance) {
    $group_name = $instance['group_name'];
    $api_key    = "&key=".$instance['api_key'];

    $request = WP_Http;

    $url = "https://api.meetup.com/2/events?&sign=true&group_urlname=".$group_name."&page=3".$api_key;

    $res      = wp_remote_get($url);
    $events   = json_decode($res['body'], true);
    ?>
    <style>
    .meetup-widget-box {
      width: 100%;
      padding: 4px;
      border: 1px solid #CCC;
    }
    .meetup-widget-date{
      font-size: 110%;
      font-weight: bold;
    }
    .meetup-widget-time {

    }
    .meetup-widget-title {
      font-size: 120%;
      font-weight: bolder;
    }
    .meetup-widget-rsvp {
      cursor: pointer;
      font-weight: bolder;
      text-align: center;
      height: 20px;
      width: 100px;
      margin-top: 10px;
      margin-right: 4px;
      margin-left: 4px;
      background-color: #62ac75;
      border: 1px solid #000;
      padding-top: 5px;
      display: block;
      text-decoration: none;
      color: #000;
    }
    .meetup-widget-rsvp a:hover {
      text-decoration: none;
    }

    .meetup-widget-count {
      margin-top: 3px;
      margin-bottom: 20px;
    }

    </style>
    <div class="meetup-widget-box">
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
        <div class="meetup-widget-title"><?php echo $event_title;?></div>
        <div class="meetup-widget-date"><?php echo $event_date;?></div>
        <div class="meetup-widget-time"><?php echo $event_time;?></div>
        <a href="<?php echo $meetup_page;?>" class="meetup-widget-rsvp">RSVP</a>
        <div class="meetup-widget-count"><?php echo $attending;?> attending</div>
        
      </div>
      <?php
    }
    ?></div><?php

  }

  public function form($instance) {
    if( isset( $instance['group_name'])) {
      $group_name = $instance['group_name'];
    } else {
      $group_name = __('Meetup Groupname', 'text_domain');
    }
    if( isset( $instance['api_key'])) {
      $api_key = $instance['api_key'];
    } else {
      $api_key = __('Meetup API Key', 'text_domain');
    }


    ?>
    <p>
    <label for="<?php echo $this->get_field_id('group_name'); ?>">
      <?php _e('API Group Name:'); ?>
    </label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('group_name'); ?>" name="<?php echo $this->get_field_name('group_name'); ?>" value="<?php echo esc_attr($group_name); ?>"/>

    <label for="<?php echo $this->get_field_id('api_key'); ?>">
      <?php _e('API Key:'); ?>
    </label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('api_key'); ?>" name="<?php echo $this->get_field_name('api_key'); ?>" value="<?php echo esc_attr($api_key); ?>"/>
    </p>
    <?php
  }

  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['group_name'] = (!empty($new_instance['group_name'])) ? strip_tags($new_instance['group_name']) : '';
    $instance['api_key']    = (!empty($new_instance['api_key']))    ? strip_tags($new_instance['api_key']) : '';

    return $instance;
  }
}

/* Register Widget
 * =============== */
add_action( 'widgets_init',
  create_function('', 'return register_widget("cfa_meetup");')
);