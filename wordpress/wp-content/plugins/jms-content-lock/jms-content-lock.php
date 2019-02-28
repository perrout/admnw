<?php

/**
 * Plugin Name: JMS Content Lock
 * Description: Show or hide the content
 * JMS Comunicação
 * Version: 1.0
 * Author URI: https://www.jmscomunicacao.com.br/
 */

require(dirname(__FILE__) . '/inc/class-content-lock.php');
require(dirname(__FILE__) . '/inc/class-content-lock-export.php');

function ajax_login_init()
{
    // version codes
    $js_ver = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'assets/js/main.js'));
    $css_ver = date("ymd-Gis", filemtime(plugin_dir_path(__FILE__) . 'assets/css/style.css'));

    wp_register_style('jms_content_lock_style', plugins_url('assets/css/style.css', __FILE__), false, $css_ver);
    wp_enqueue_style('jms_content_lock_style');

    wp_register_script('jms_content_lock_script', plugins_url('assets/js/main.js', __FILE__), array('jquery'), $js_ver);
    wp_enqueue_script('jms_content_lock_script');

    wp_localize_script('jms_content_lock_script', 'ajax_login_object', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'loggedin' => is_user_logged_in(),
        'redirecturl' => home_url(),
        'loadingmessage' => __('Enviando dados do usuário. Por favor, aguarde...')
    ));
    
}
    
// Execute the action only if the user isn't logged in
add_action('init', 'ajax_login_init');