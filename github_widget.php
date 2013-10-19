<?php
/*
Plugin Name: GitHub Repository Sidebar widget
Plugin URI: <>
Description: A Sidebar widget to display information about a specific repository
Version: 0.1
Author: Kaleb Clark
Author URI: http://www.abraxxus.net
*/

class gitHub_Repos extends WP_Widget {
  public function __construct() {
    parent::__construct(
      'github_widget', 
      __('Github Repository', 'text_domain'),
      array('description' => __('A Github Repository Widget', 'text_domain'), )
    );
  }

  public function widget($args, $instance) {
    $gh_path  = get_field('project_github');
    $c_id     = $instance['client_id'];
    $c_secret = $instance['client_secret'];
    list($gh_user, $gh_repo) = explode('/', $instance['repo']);

    $request = WP_Http;
    $api_key_s = "?client_id=".$c_id."&client_secret=".$c_secret;

    /* check for repo
     * ============== */
    $url  = "https://api.github.com/repos/".$gh_path.$api_key_s;
    $res  = wp_remote_get($url);
    $repo = json_decode($res['body'], true);
    $clone_url  = $repo['clone_url'];
    $issue_url  = $repo['url']."/issues";
    $commit_url = $repo['url']."/events";
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
    .github-widget-box {
      border: 1px solid #CCC;
      padding: 5px;
    }
    .github-widget-header {
      width: 100%;
      border-bottom: 1px solid #CCC;
      font-size: 120%;
      font-weight: bolder;
      padding-bottom: 3px;
      text-align: center;
    }
    .github-widget-section {
      font-weight: bold;
      padding-top: 10px;
      padding-bottom: 3px;
    }
    </style>
    <div class="github-widget-box">
      <div class="github-widget-header">
        GitHub Resources
      </div>
      <div class="github-menu">
       <ul>
        <li><a target="_blank" href="http://github.com/<?php echo $gh_path; ?>">Code</a></li>
        <li><a target="_blank" href="http://github.com/<?php echo $gh_path; ?>/issues">Issues (<?php echo $issues_count; ?>)</a></li> 
        <li><a target="_blank" href="http://github.com/<?php echo $gh_path; ?>/wiki">Wiki</a></li>
       </ul>
      </div>
      <div class="github-widget-section">Commits</div>
      <?php
      if(count($contributors > 0)) {
        foreach($contributors as $name => $count) {
          print "<div>$name - ($count)</div>";
        }
      }
      ?>
      <div class="github-widget-section">HTTPS clone URL</div>
      <input name="clone-path" type="text" size="25" value="<?php echo $clone_url;?>" />
    </div>
    <?php


  }

  public function form($instance) {
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


    ?>
    <p>
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
    $instance['client_id'] = (!empty($new_instance['client_id'])) ? strip_tags($new_instance['client_id']) : '';
    $instance['client_secret'] = (!empty($new_instance['client_secret'])) ? strip_tags($new_instance['client_secret']) : '';

    return $instance;
  }
}

/* Register Widget
 * =============== */
add_action( 'widgets_init',
  create_function('', 'return register_widget("gitHub_Repos");')
);
