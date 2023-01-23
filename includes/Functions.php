<?php

if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', 'meta_enqueue_assets', 0);
add_action('admin_enqueue_scripts', 'meta_enqueue_assets', 0);

function meta_enqueue_assets() {
	wp_enqueue_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js', array('jquery'), '1.12.0');
	wp_enqueue_style( 'metabasket-css', ENJIN_PLUGIN_URL . 'assets/css/style.css', array(), '1.0.2' );
	
	wp_register_script( "metabasket-ajax", ENJIN_PLUGIN_URL . 'assets/js/script.js', array('jquery'), '1.0.4' );
	wp_localize_script( 'metabasket-ajax', 'metabasketAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'plugin_url' => ENJIN_PLUGIN_URL));  
	
	wp_enqueue_script( 'metabasket-ajax' );
}

// Create Cron Schedule
add_filter('cron_schedules','enjin_cron_schedules');
function enjin_cron_schedules($schedules){
    if(!isset($schedules["1min"])){
        $schedules["1min"] = array(
            'interval' => 60,
            'display' => __('Once every minute'));
    }
    return $schedules;
}

if(!wp_next_scheduled('enjin_status_schedule_hook')){
    wp_schedule_event(time(), '1min', 'enjin_status_schedule_hook');
}


// Ajax Calls
add_action("wp_ajax_meta_basket_check_status", "meta_basket_check_status");
function meta_basket_check_status() {
	
	enjin_status_schedule_hook_action();
	
	$result['type'] = "success";
	
	$result = json_encode($result);
    echo $result;
	die();
}