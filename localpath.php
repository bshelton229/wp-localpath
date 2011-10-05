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
if (_localpath_enabled()) {
  add_filter('the_content', 'localpath_filter');
}
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

  if(!empty($hosts)) {
    $pattern = '~(href|src|HREF|SRC)="((http|https)://(' . preg_quote(implode('|', $hosts)) . ')([^"]*))"~x';
    $content = preg_replace_callback($pattern, 'localpath_replace_callback', $content);
  }

  return $content;
}

/**
 * The replace callback for preg_replace_callback
 *
 * @NOTE: We need to determine if we should ditch HTTPS
 *
 * @param array $matches
 *  Provided by preg_replace_callback, this is an array of matches
 * @return string
 *  Returns a string for the replacement
 */
function localpath_replace_callback($matches) {
  //echo "<pre>"; print_r($matches); echo "</pre>"; // For debugging the regex

  $out = $matches[1] . '="';
  $out .= preg_replace('~^(http|https)?://(' . $matches[4] . ')~', WP_SITEURL, $matches[2]);
  $out .= '"';

  return $out;
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

  // Get the option from the DB or the
  if (defined('LOCALPATH_HOSTS')) {
    $localpath_options = trim(LOCALPATH_HOSTS);
  }
  else {
    $localpath_options = trim(get_option('localpath_hosts', ''));
  }

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
    $out = array_map('trim', $replace);
    return array_map('strtolower', $out);
  }
}

/**
 * Function to try to read the LOCALPATH_ENABLED constant
 * to see if the plugin has been disabled in the wp-config.php file
 *
 * @return boolean
 */
function _localpath_enabled() {
  return (defined('LOCALPATH_ENABLED') && !LOCALPATH_ENABLED) ? FALSE : TRUE;
}
