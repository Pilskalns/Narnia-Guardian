<?php 
/* THIS you should include into your root index file, so script could be run on load of domain.
 * Or you can move anywhere you want - this script is safe of corrupting itself, as it does not include searched malware script parts.
 * If Narnia Guardian files will get corrupted - it will clean up itself.
 */

// Import and setup Guardian script
include '../NarniaGuardian/NarniaGuardian.php';
$Guard = new NarniaGD;

// Clean files within this $root to search. $root is relative to index.php not NarniaGuardian.php
// If you have parked multiple domains on one host, you would like to do clean up for each separately, because then
// 1: logs will be splitted
// 2: You can test and fine tune script on copy of infected files and then apply on working directory
$Guard->cleanFiles('../wordpress/');

// Stop 
if (true) exit;
?>
<?php
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define('WP_USE_THEMES', true);

/** Loads the WordPress Environment and Template */
require( dirname( __FILE__ ) . '/wp-blog-header.php' );