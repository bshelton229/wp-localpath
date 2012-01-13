<?php

class Localpath {
  /**
   * Init our Wordpress hooks
   */
  function wpInit() {
    if ( $this::isEnabled() ) {
      add_filter('the_content', array(&$this, 'contentFilter'));
    }
    add_action('admin_menu',  array(&$this, 'adminMenu'));
  }

  /**
   * The admin_menu callback
   */
  function adminMenu() {
    add_options_page('LocalPath Options', 'LocalPath Options', 'manage_options', 'localpath_admin', array(&$this, 'adminPage'));
  }

  /**
   * The callback to render the options page
   */
  function adminPage() {
    // See if we need to handle form submission
    if(isset($_POST['action']) && $_POST['action'] == "update") {
      update_option( 'localpath_hosts', trim($_POST['hosts']) );
      $localpath_notice = "Settings Saved";
    }

    // Include the form
    include dirname(__FILE__) . '/../admin/settings_form.php';
    return TRUE;
  }

  /**
   * The main content filter
   * callback for the the_content filter
   */
  function contentFilter($content) {
    // Grab our hosts
    $hosts = $this->hosts();

    // Bail if we don't have hosts defined
    if (empty($hosts)) {
      return $content;
    }

    $hosts = array_map(array(&$this, 'pregPrepare'), $hosts);
    $pattern = '~(href|src|HREF|SRC)="((http|https)://(' . implode('|', $hosts) . ')([^"]*))"~x';
    $content = preg_replace_callback($pattern, array(&$this, 'filterCallback'), $content);

    return $content;
  }

  /**
   * A callback for a preg_replace_callback in contentFiler()
   */
  function filterCallback($matches) {
    $attr = $matches[1];
    // Host with trailing slash
    $host = $matches[4];
    $out = $attr . '="';
    // $matches[4] is the host, and will inlude the trailing slash
    // so we need to append trailing slash to get_site_url()
    $out .= preg_replace('~^(http|https)?://(' . $host . ')~', get_site_url() . '/', $matches[2]);
    $out .= '"';
    return $out;
  }

  /**
   * Shape up our list of hosts
   *
   * @return array
   *  Returns an array of hosts
   */
  function hosts() {
    $opts = $this->getHostsOption();
    if(empty($opts)) {
      return array();
    }
    else {
      $replace = preg_split('/\s/', preg_replace('/\s+/', ' ', $opts));
      $raw = array_map('trim', $replace);
      $out = array();
      // Shap the raw option into an array of formatted hosts
      foreach ($raw as $k => $v) {
        // Remove any protocol prefixes
        $v = preg_replace('~^([a-z]+)://~i', '', $v);
        // Make sure we have a trailing slash
        if (!preg_match('~/$~', $v)) {
          $v .= '/';
        }
        $out[] = $v;
      }
      return array_map('strtolower', $out);
    }
  }

  /**
   * Pull out the hosts option from the database or
   * a constant in wp-config.php
   */
  function getHostsOption() {
    // Make the return a static var so we only pull from the DB once
    static $localpath_options;
    if (isset($localpath_options)) { return $localpath_options; }

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
   * Is the plugin enabled?
   *
   * @return boolean
   */
  static function isEnabled() {
    return (defined('LOCALPATH_ENABLED') && !LOCALPATH_ENABLED) ? FALSE : TRUE;
  }

  /**
   * Prepare a hostname to be used in a regular expression
   * Used in an array_map during the filter
   *
   * @return string
   */
  private function pregPrepare($s) {
    return preg_quote($s, "~");
  }
}
