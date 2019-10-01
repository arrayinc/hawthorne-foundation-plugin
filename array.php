<?php

/*
Plugin Name: Array
Plugin URI: https://arrayschool.com/
Version: 1.0.0
Author: Alicia Wilson
License: GPLv2 or later
Text Domain: arrayschool
*/

require_once __DIR__ . '/includes/class-array-membership-types.php';
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
    register_setting("array-settings", "user_creation_form");
    register_setting("array-settings", "user_creation_form_username");
    register_setting("array-settings", "user_creation_form_email");
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

function gravity_forms_select(string $id)
{
    //EARLY OUT: Check to make sure Gravity Forms is installed
    if(!class_exists('GFAPI')){
        echo "Gravity Forms Is Not Installed";
        return;
    }

    $forms              = GFAPI::get_forms();
    $selected_form_id   = get_option('user_creation_form');
    $selected_username  = get_option('user_creation_form_username');
    $selected_email     = get_option('user_creation_form_email');
    $selected_form      = $selected_form_id ? GFAPI::get_form($selected_form_id) : false;
    $form_fields        = $selected_form ? GFAPI::get_fields_by_type($selected_form, array('name', 'email')) : []; 
    ?>
    <script>
        jQuery(document).ready(function(){
            jQuery('#<?php echo $id; ?>').change(function(){
                //EARLY OUT: make sure that value is present
                if (jQuery(this).val() === ''){
                    return;
                }
                jQuery.ajax(
                    {
                        method: 'GET',
                        url: '<?php echo get_admin_url(null, 'admin-post.php?action=gf_field_select&form_id='); ?>' + jQuery(this).val(),
                    })
                    .done(function(response, statusText, jqxhr){
                        if (response.status = 200) {
                            var json = jQuery.parseJSON(response);
                            buildSelects(json.fields);
                        } else {
                            alert("Something went wrong. Please contact your developer. FILE: <?php echo __FILE__; ?>. LINE: <?php echo __LINE__; ?>");
                        }
                    })
                    .fail(function(){
                        console.log('fail');
                    });
            });

        });

        function buildSelects(fields){
            jQuery('.gf-form-field-select').each(function(){
                var select = this;

                while(this.hasChildNodes()){
                    this.removeChild(this.lastChild);
                }

                fields.forEach(function(field){
                    var option = document.createElement('option');
                    option.value = field.id;
                    option.innerText = field.label;
                    select.appendChild(option);
                });
                select.parentNode.classList.remove('hidden');
            });
        }
    </script>
    <p><label for="<?php echo $id; ?>">Form</label></p>
    <select name="<?php echo $id; ?>" id="<?php echo $id; ?>">
        <option value=""></option>
    <?php foreach($forms as $form) : ?>
        <option value="<?php echo $form['id']; ?>" <?php selected(get_option('user_creation_form'), $form['id']); ?>>
            <?php echo $form['title']; ?>
        </option> 
    <?php endforeach; ?>
    </select>
    <div class="<?php echo $selected_username ? '' : 'hidden'; ?>">
        <p><label for="<?php echo $id . '_username'; ?>">Username Form Field</label></p>
        <select name="<?php echo $id . '_username'; ?>" id="<?php echo $id . '_username'; ?>" class="gf-form-field-select">
        <?php foreach($form_fields as $field) : ?>
        <option value="<?php echo $field['id']; ?>" <?php selected(get_option('user_creation_form_username'), $field['id']); ?>>
            <?php echo $field['label']; ?>
        </option> 
        <?php endforeach; ?>
        </select>
    </div>
    <div class="<?php echo $selected_email ? '' : 'hidden'; ?>">
        <p><label for="<?php echo $id . '_email'; ?>">Email Form Field</label></p>
        <select name="<?php echo $id . '_email'; ?>" id="<?php echo $id . '_email'; ?>" class="gf-form-field-select">
        <?php foreach($form_fields as $field) : ?>
        <option value="<?php echo $field['id']; ?>" <?php selected(get_option('user_creation_form_email'), $field['id']); ?>>
            <?php echo $field['label']; ?>
        </option> 
        <?php endforeach; ?>
        </select>
    </div>
    <?php
}

add_action('admin_post_gf_field_select', 'gravity_form_field_select');
add_action('admin_post_nopriv_gf_field_select', 'gravity_form_field_select');
function gravity_form_field_select()
{
    $status = 200;
    $status_message = 'Request successful';

    //EARLY OUT: Check to make sure Gravity Forms is installed
    if(!class_exists('GFAPI')){
        $status = 400;
        $status_message = 'Gravity Forms is not installed';
        die(json_encode(array('status' => $status, 'status message' => $status_message)));
    }

    $form_id = $_REQUEST['form_id'] ?? false;

    //EARLY OUT: Check if form id was passed in the request
    if(!$form_id){
        $status = 400;
        $status_message = 'Form ID required';
        die(json_encode(array('status' => $status, 'status message' => $status_message)));
    }

    $form = GFAPI::get_form($form_id);

    //EARLY OUT: Check if form requested exists
    if(!$form) {
        $status = 400;
        $status_message = "Form ID {$form_id} does not exist";
        die(json_encode(array('status' => $status, 'status message' => $status_message)));
    }

    $form_fields = GFAPI::get_fields_by_type($form, array('name', 'email'));
    die(json_encode(array('status' => $status, 'status message' => $status_message, 'fields' => $form_fields)));

}

add_action('gform_after_submission', 'try_create_user', 10, 2);
function try_create_user($entry, $form)
{
    $selected_form_id   = get_option('user_creation_form');
    //EARLY OUT: not the form we are looking for
    if (intval($selected_form_id) !== $form['id']) {
        return;
    }

    require_once __DIR__ . '/includes/class-array-user.php';
    $selected_username  = get_option('user_creation_form_username');
    $selected_email     = get_option('user_creation_form_email');
    $username           = rgar($entry, $selected_username . '.3') . '_' .rgar($entry, $selected_username . '.6');
    $email              = rgar($entry, $selected_email);

    $user = new Array_User('applicant');
    try{
        $user->create($username, $email);
    }
    catch(Exception $e)
    {
        //send email to site admin
        wp_mail(bloginfo('admin_email'), "Application Error. Form ID: {$form['id']}", $e->getMessage());
    }
    
}