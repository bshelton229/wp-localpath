<?php
/**
* @package LocalPath
* @version 0.1
*/
/*
Plugin Name: LocalPath
Description: Re-write URLs away from admin URLs to the current host.
Author: University Communications at UW-Madison
Version: 0.1
*/

// Register our hooks
add_filter('the_content', 'localpath_filter');
add_action('admin_menu',  'localpath_adminmenu');

/**
 * localpath_filter() does the replacement in the page/post content
 *
 * @param string $content
 *
 * @return string
 *  Returns a filtered string
 */
function localpath_filter($content) {
  // Grab our hosts
  $hosts = _localpath_hosts();

  // Bail if we don't have hosts defined
  if(empty($hosts)) {
    return $content;
  }

  if(!in_array($_SERVER['HTTP_HOST'], $hosts)) {
    foreach($hosts as $host) {
      $content = preg_replace("/$host/", $_SERVER['HTTP_HOST'], $content);
    }
  }

  return $content;
}

/**
 * Register the admin page
 */
function localpath_adminmenu() {
  add_options_page('LocalPath Options', 'LocalPath Options', 'manage_options', 'localpath_admin', 'localpath_admin');
}

/**
 * Render the admin page
 */
function localpath_admin() {
  // See if we need to handle form submission
  if(isset($_POST['action']) && $_POST['action'] == "update") {
    update_option( 'localpath_hosts', trim($_POST['hosts']) );
    $localpath_notice = "Settings Saved";
  }
  // Include the form
  include dirname(__FILE__) . '/admin/settings_form.php';
  return TRUE;
}

/**
 * _localpath_opts() - Helper function to grab the localpath WP options
 *
 * This function is safe to call multiple times. It will only hit
 * the database once and will subsequently return the static variable.
 *
 * @return string
 *  Returns the localpath_hosts option from the wp options table.
 */
function _localpath_opts() {
  // Make the return a static var so we only pull from the DB once
  static $localpath_options;
  if(isset($localpath_options)) { return $localpath_options; }

  // Gert the option from the DB
  $localpath_options = trim(get_option('localpath_hosts', ''));
  return $localpath_options;
}

/**
 * _localpath_hosts() - Helper function to return an array of hosts
 *
 * @return array
 *  Returns an array of hosts considered local
 */
function _localpath_hosts() {
  $opts = _localpath_opts();
  if(empty($opts)) {
    return array();
  }
  else {
    $replace = preg_split('/\s/', preg_replace('/\s+/', ' ', $opts));
    return array_map('trim', $replace);
  }
}
