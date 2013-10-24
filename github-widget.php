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

    /* Setup API Key URL
     * ================= */
    $api_url  = "?client_id=".$instance['client_id'];
    $api_url .= "&client_secret=".$instance['client_secret'];

    /* check for repo
     * ============== */
    $url  = "https://api.github.com/repos/";
    $url .= $gh_path;
    $url .= $api_url;

    $res  = wp_remote_get($url);
    $repo = json_decode($res['body'], true);
    $clone_url  = $repo['clone_url'];
    $issue_url  = $repo['url']."/issues".$api_url;
    $commit_url = $repo['url']."/events".$api_url;
    $repo_title = $repo['name'];
    if($repo['message'] == "Not Found") {
      return;
    }

    /* get issue count
     * =============== */
    $issue_res  = wp_remote_get($issue_url);
    $issue_ret  = json_decode($issue_res['body'], true);
    $issues_count = count($issue_ret);

    /* get commits
     * =========== */
    $commit_res  = wp_remote_get($commit_url);
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

    /* Build Chart Data JSON
     * ===================== */
    if($instance['display_chart']) {
      $chart_data = "[['Person', 'Commits'],";
      foreach($contributors as $person => $count) {
        $chart_data .= "['".$person."', ".$count."],";
      }
      $chart_data = preg_replace('/,$/', ']', $chart_data);
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
        <li><a target="_blank" href="http://github.com/<?php echo $gh_path; ?>/issues">To Do / Issues (<?php echo $issues_count; ?>)</a></li> 
        <li><a target="_blank" href="http://github.com/<?php echo $gh_path; ?>/wiki">Wiki</a></li>
       </ul>
      </div>
      <div style="clear: both"></div>
      <?php

      /* Display Contributors
       * ==================== */
      if($instance['display_chart']) {
        // Display Chart
        ?><div id='chart_div'></div><?php
      } else {
        // Display text
        if(count($contributors > 0)) {
          foreach($contributors as $name => $count) {
            print "<div class='fleft github-widget-name'>".$name."</div><div class=''>".$count."</div>";
          }
        }
      }

      /* Display Clone URL
       * ================= */
      ?>
      <div class="github-widget-section">HTTPS clone URL</div>
      <input name="clone-path" type="text" size="25" value="<?php echo $clone_url;?>" />
    </div>
    <?php
    if($instance['display_chart']) { ?>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <script type="text/javascript">
        google.load("visualization", "1", {packages:["corechart"]});
        google.setOnLoadCallback(drawChart);
        function drawChart() {
          var data = google.visualization.arrayToDataTable(<?php echo $chart_data;?>);
          var options = {
            title: 'Project Commits',
            fontSize: 12,
            legend: {
              position: 'left',
            },
            chartArea: {
              width: '100%',
              left: 2
            }
          };
          var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
            chart.draw(data, options);
          }
        </script>
    <?php
    } // end chart check


  }

  public function form($instance) {
    $instance = wp_parse_args( (array) $instance, array(
      'bw_title'      => 'Widget Title',
      'client_id'     => 'API Client ID',
      'client_secret' => 'API Client Secret',
      'display_chart' => 0
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
    <label for="<?php echo $this->get_field_id('client_id'); ?>">
      <?php _e('API Client ID:'); ?>
    </label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('client_id'); ?>" name="<?php echo $this->get_field_name('client_id'); ?>" value="<?php echo esc_attr($client_id); ?>"/>

    <label for="<?php echo $this->get_field_id('client_secret'); ?>">
      <?php _e('API Client Secret:'); ?>
    </label>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('client_secret'); ?>" name="<?php echo $this->get_field_name('client_secret'); ?>" value="<?php echo esc_attr($client_secret); ?>"/>

    <label for="<?php echo $this->get_field_id('display_chart'); ?>">
      <?php _e('Display Commits as chart: :'); ?>
    </label>
    <input class="checkbox" type="checkbox" <?php checked($instance['display_chart'], true); ?> id="<?php echo $this->get_field_id('display_chart'); ?>" name="<?php echo $this->get_field_name('display_chart'); ?>" />
    </p>
    <?php
  }

  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['bw_title']      = (!empty($new_instance['bw_title']))     ? strip_tags($new_instance['bw_title'])  : '';
    $instance['client_id']     = (!empty($new_instance['client_id']))     ? strip_tags($new_instance['client_id']) : '';
    $instance['client_secret'] = (!empty($new_instance['client_secret'])) ? strip_tags($new_instance['client_secret']) : '';
    $instance['display_chart'] = (!empty($new_instance['display_chart'])) ? 1 : 0;

    return $instance;
  }
}

/* Register Widget
 * =============== */
add_action( 'widgets_init',
  create_function('', 'return register_widget("bw_github");')
);
