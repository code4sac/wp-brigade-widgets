<?php
/*
Plugin Name: GitHub Repository Sidebar widget
Plugin URI: https://github.com/code4sac/wp-brigade-widgets
Description: A Sidebar widget to display information about a specific repository
Version: 1.0
Author: Kaleb Clark (Code4Sac)
Author URI: https://github.com/KalebClark
*/

class bw_github extends WP_Widget {
  public function __construct() {
    parent::__construct(
      'bw_github', 
      __('Brigade Github Repository', 'text_domain'),
      array('description' => __('A Github Repository Widget', 'text_domain'), )
    );
  }

  public function widget($args, $instance) {
    $gh_path  = get_field('project_github');
    list($gh_user, $gh_repo) = explode('/', $instance['repo']);

    $request = WP_Http;

    /* check for repo
     * ============== */
    $url  = "https://api.github.com/repos/";
    $url .= $gh_path;
    $url .= "?client_id=".$instance['client_id'];
    $url .= "&client_secret=".$instance['client_secret'];

    $res  = wp_remote_get($url);
    $repo = json_decode($res['body'], true);
    $clone_url  = $repo['clone_url'];
    $issue_url  = $repo['url']."/issues";
    $commit_url = $repo['url']."/events";
    $repo_title = $repo['name'];
    if($repo['message'] == "Not Found") {
      return;
    }

    /* get issue count
     * =============== */

    $issue_res  = wp_remote_get($issue_url.$api_key_s);
    $issue_ret  = json_decode($issue_res['body'], true);
    $issues_count = count($issue_ret);

    /* get commits
     * =========== */

    $commit_res  = wp_remote_get($commit_url.$api_key_s);
    $commit_ret  = json_decode($commit_res['body'], true);
    if(!isset($commit_ret['message'])) {
      $contributors = array();
      foreach($commit_ret as $event) {
        if($event['type'] != 'PushEvent') { continue; };
        foreach($event['payload']['commits'] as $commit) {
          $contributors[$commit['author']['name']]++;
        }
      }
    }

    ?>
    <style>
    <?php include('brigade-widgets.css');?>
    .github-menu ul {
      list-style-type: none;
      margin: 0;
      padding: 0;
      text-align: center;
    }
    .github-menu li {
      display: inline;
    }
    .github-menu a {
      display: inline-block;
      padding-right: 10px;
    }
    .github-widget-section {
      font-weight: bold;
      padding-top: 10px;
      padding-bottom: 3px;
    }
    .fleft {
      float: left;
    }
    .github-widget-name {
      width: 180px;
    }
    .github-widget-repo-title {
      font-weight: bold;
      text-align: center;
    }
    </style>
    <div class="brigade-widget-box">
      <div class="brigade-widget-header">
        <?php echo $instance['bw_title'];?>
      </div>
      <div class="github-widget-repo-title"><?php echo $gh_path;?></div>
      <div id="brigade-widget-menu">
       <ul>
        <li><a target="_blank" href="http://github.com/<?php echo $gh_path; ?>">Code</a></li>
        <li><a target="_blank" href="http://github.com/<?php echo $gh_path; ?>/issues">Todo / Issuess (<?php echo $issues_count; ?>)</a></li> 
        <li><a target="_blank" href="http://github.com/<?php echo $gh_path; ?>/wiki">Wiki</a></li>
       </ul>
      </div>
      <div class="github-widget-section">Commits</div>
      <?php

      /* Display Contributors
       * ==================== */
      if(count($contributors > 0)) {
        foreach($contributors as $name => $count) {
     //     print "<div>$name - ($count)</div>";
          print "<div class='fleft github-widget-name'>".$name."</div><div class=''>".$count."</div>";
        }
      }

      /* Display Clone URL
       * ================= */
      ?>
      <div class="github-widget-section">HTTPS clone URL</div>
      <input name="clone-path" type="text" size="25" value="<?php echo $clone_url;?>" />
    </div>
    <?php


  }

  public function form($instance) {
    $instance = wp_parse_args( (array) $instance, array(
      'bw_title'      => 'Widget Title',
      'client_id'     => 'API Client ID',
      'client_secret' => 'API Client Secret'
    ));
    foreach($instance as $field => $val) {
      if( isset( $instance[$field])) {
        $$field = strip_tags( $instance[$field]);
      }
    }
    /*
    if( isset( $instance['client_id'])) {
      $client_id = $instance['client_id'];
    } else {
      $client_id = __('github clientID', 'text_domain');
    }
    if( isset( $instance['client_secret'])) {
      $client_secret = $instance['client_secret'];
    } else {
      $client_secret = __('github clientSecret', 'text_domain');
    }
    */

    ?>
    <p>
    <label for="<?php echo $this->get_field_id('bw_title'); ?>">
      <?php _e('Widget Title:'); ?>
    </label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('bw_title'); ?>" name="<?php echo $this->get_field_name('bw_title'); ?>" value="<?php echo esc_attr($bw_title); ?>"/>
    <label for="<?php echo $this->get_field_id('client_id'); ?>">
      <?php _e('API Client ID:'); ?>
    </label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('client_id'); ?>" name="<?php echo $this->get_field_name('client_id'); ?>" value="<?php echo esc_attr($client_id); ?>"/>

    <label for="<?php echo $this->get_field_id('client_secret'); ?>">
      <?php _e('API Client Secret:'); ?>
    </label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('client_secret'); ?>" name="<?php echo $this->get_field_name('client_secret'); ?>" value="<?php echo esc_attr($client_secret); ?>"/>
    </p>
    <?php
  }

  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['bw_title']      = (!empty($new_instance['bw_title']))     ? strip_tags($new_instance['bw_title'])  : '';
    $instance['client_id']     = (!empty($new_instance['client_id']))     ? strip_tags($new_instance['client_id']) : '';
    $instance['client_secret'] = (!empty($new_instance['client_secret'])) ? strip_tags($new_instance['client_secret']) : '';

    return $instance;
  }
}

/* Register Widget
 * =============== */
add_action( 'widgets_init',
  create_function('', 'return register_widget("bw_github");')
);
