<?php
/**
* @package LocalPath
* @version 0.4
*/
/*
Plugin Name: LocalPath
Description: Re-write URLs away from admin URLs to the current host.
Author: University Communications at the University of Wisconsin-Madison
Version: 0.3
*/

require_once dirname(__FILE__) . '/lib/localpath.class.php';
$localpath = new Localpath();
$localpath->wpInit();

/**
 * Helper function for replacing content
 * @param {String} $content
 *  The content to replace
 * @return {String}
 *  Returns the replaced content
 */
function localpath_filter($content) {
  $localpath = new Localpath();
  return $localpath->contentFilter($content);
}
