<?php

/*
Plugin Name: Array
Plugin URI: https://arrayschool.com/
Version: 1.0.0
Author: Alicia Wilson
License: GPLv2 or later
Text Domain: arrayschool
*/

// add_filter("materialis_header_title", function ($title){
//     return "Apply for our Scholarship!";
// } );


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
function array_admin_page_output()
{
    ?>
    <div class="wrap">
        <h1>Array Settings</h1>

        <form method="post" action="options.php">
            <?php settings_fields('admin-setting-group');?>
            <label>Application Start Date<br><input type="date" name="application_date_start" value="<?php echo get_option("application_date_start"); ?>"></label><br>
            <label>Application End Date<br><input type="date" name="application_date_end" value="<?php echo get_option("application_date_end"); ?>"></label><br>
            <label>Message <br>
                <textarea name="message" rows="10" cols="80"></textarea>
            </label>
            <?php submit_button();?>
        </form>
    </div>
    <?php 
}

add_action("admin_init", "register_array_admin_page_settings");
function register_array_admin_page_settings()
{
    $application_group_name = "application_info";
    register_setting("admin_settings_group", "application_date_start");
    register_setting("admin-settings-group", "application_date_end");
    register_setting("admin-settings-group", "message");

}

add_action("admin_enqueue_scripts", "array_plugin_scripts");
function array_plugin_scripts() 
{
    wp_enqueue_style("array-styles", plugin_dir_url(__FILE__) . "/admin-style.css", []);
}

add_action("wp_enqueue_scripts", "array_plugin_frontend_scripts");
function array_plugin_frontend_scripts() 
{
    wp_enqueue_style("array-frontend-styles", plugin_dir_url(__FILE__) . "/styles.css", []);
}

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