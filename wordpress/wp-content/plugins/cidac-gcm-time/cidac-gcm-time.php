<?php
/*
* Plugin Name: CIDAC GCM Time 
* Plugin URI: https://cidac.campos.rj.gov.br/
* Description: CIDAC - GCM Time
* Author: CIDAC | Marcus Perrout
* Version: 1.0
* Author URI: https://perrout.com.br
* Text Domain: gcm-time
* Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'CIDAC_GCM_TIME_PLUGIN_VERSION', '1.0.0' );
define( 'CIDAC_GCM_TIME_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'CIDAC_GCM_TIME_PLUGIN_FILE', dirname(plugin_basename(__FILE__)) );
define( 'CIDAC_GCM_TIME_PLUGIN_URL', plugins_url('', __FILE__));

if ( ! class_exists( 'cidacGCMTime' ) ) {
	require_once CIDAC_GCM_TIME_PLUGIN_DIR . '/core/classes/class-cidac-gcm-time.php';
	$class_jms_home_post_type = new cidacGCMTime;
}
