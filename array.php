<?php

/*
Plugin Name: Array
Plugin URI: https://arrayschool.com/
Version: 1.0.0
Author: Alicia Wilson
License: GPLv2 or later
Text Domain: arrayschool
*/

/* --- Front page update based on date  --- */

add_filter("materialis_header_title", function ($title){ 
    if(is_front_page() && (is_spring_application_period() || is_fall_application_period())) {
        return "Apply for our Scholarship!";
    }
    return $title;
} );

function is_spring_application_period()
{
    $return_value = false;
    $application_open = get_option("spring_application_date_start", null);
    $application_close = get_option("spring_application_date_end", null);
    if($application_open && $application_close) {
        $today = new DateTime();
        $application_open = new DateTime($application_open);
        $application_close = new DateTime($application_close);
        if($today >= $application_open && $today <= $application_close) {
            $return_value = true;
        }
    }
    return $return_value;
}

function is_fall_application_period()
{
    $return_value_fall = false;
    $fall_ap_open = get_option("fall_application_date_start", null);
    $fall_ap_close = get_option("fall_application_date_end", null);
    if($fall_ap_open && $fall_ap_close) {
        $today = new DateTime();
        $fall_ap_open = new DateTime($fall_ap_open);
        $fall_ap_close = new DateTime($fall_ap_close);
        if($today >= $fall_ap_open && $today <= $fall_ap_close) {
            $return_value_fall = true;
        }
    }
    return $return_value_fall;
}


/* ------------- OPTIONS PAGE -------------*/
//add menu page
add_action("admin_menu", "array_admin_page"); 
function array_admin_page()
{
    add_menu_page(
        "Array Settings", 
        "Array Settings", 
        "administrator",
        "array-settings",
        "array_admin_page_output"
    );
}

//options page output
function array_admin_page_output()
{
    require_once __DIR__ . '/options-template.php'; 
}

//regsiter settings
add_action("admin_init", "register_array_admin_page_settings");
function register_array_admin_page_settings()
{
    register_setting("array-settings", "spring_application_date_start");
    register_setting("array-settings", "spring_application_date_end");
    register_setting("array-settings", "fall_application_date_start");
    register_setting("array-settings", "fall_application_date_end");
    register_setting("array-settings", "application_message");
}

/*----------- Enqueue Scripts ----------*/
add_action("admin_enqueue_scripts", "array_plugin_scripts");
function array_plugin_scripts() 
{
    wp_enqueue_style("array-styles", plugin_dir_url(__FILE__) . "/admin-style.css", []);
}

add_action("wp_enqueue_scripts", "array_plugin_frontend_scripts", 99);
function array_plugin_frontend_scripts() 
{
    wp_enqueue_style("array-frontend-styles", plugin_dir_url(__FILE__) . "/styles.css", []);
}


/*--- Testimonials custom posts page -----*/

add_action("widgets_init", "create_testimonials_sidebar_widget");
function create_testimonials_sidebar_widget() 
{
    register_sidebar ([
        "name"          => "Testimonials Sidebar",
        "id"            => "testimonials_sidebar",
        'before_widget' => '<div id="%1$s" class="widget %2$s mdc-elevation--z5">',
        'after_widget'  => '</div>',
        'before_title'  => '<h5 class="widgettitle"><i class="mdi widget-icon"></i>',
        'after_title'   => '</h5>',
    ]);
}

add_action("init", "create_testimonials_post_type");
function create_testimonials_post_type()
{
    $args = array(
        'label'              => "Testimonials",
        'public'             => true,
        'capability_type'    => 'post',
        // 'show_in_rest'       => true,
        'menu_position'      => 20,
        'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' ),
    );
    register_post_type("array_testimonials", $args);
}

add_filter("template_include", "load_testimonials_template", 99);
function load_testimonials_template($page_template)
{
    if("array_testimonials" == get_post_type()){
        $page_template = __DIR__ . "/page-testimonials.php";
    }
    return $page_template;
}