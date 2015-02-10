<?php 

// Import and setup Guardian script
include '../NarniaGuardian/NarniaGuardian.php';
$Guard = new NarniaGD;

// Clean files within this $root to search. $root is relative to index.php not NarniaGuardian.php
$Guard->cleanFiles('../wordpress/');
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
// define('WP_USE_THEMES', true);

/** Loads the WordPress Environment and Template */
// require( dirname( __FILE__ ) . '/wp-blog-header.php' );