<?php

/*
Plugin Name: Array
Plugin URI: https://arrayschool.com/
Version: 1.0.0
Author: Alicia Wilson
License: GPLv2 or later
Text Domain: arrayschool
*/

$plugin_init = require_once __DIR__ . '/includes/class-array-membership-types.php';

register_activation_hook(__FILE__, array(get_class($plugin_init), 'init'));
register_deactivation_hook(__FILE__, array(get_class($plugin_init), 'deinit'));
/* --- Front page update based on date  --- */

add_filter("materialis_header_title", function ($title){ 
    if(is_front_page() && (is_spring_application_period() || is_fall_application_period())) {
        return "Apply for our Scholarship!";
    }
    return $title;
} );

add_filter("materialis_print_buttons_list_button", "show_one_button", 10, 3);
function show_one_button($button, $setting, $index)
{
    //EARLY OUT: only change button if setting is header buttons
    if (!$setting === 'header_content_buttons'){
        return $button;
    }

    if($index == 1){
        if(is_front_page() && (!is_spring_application_period() && !is_fall_application_period())) {
            $button['class'] = $button['class'] . ' array-hidden';
        }
    }

    return $button;
}

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
        "Scholarship Settings", 
        "Scholarship Settings", 
        "administrator",
        "array-settings",
        "array_admin_page_output",
        "dashicons-welcome-learn-more"
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

add_shortcode('members', 'restrict_content_shortcode');
function restrict_content_shortcode($atts, $content = '')
{
    extract(shortcode_atts(
        array('type' => 'all'),
        $atts
    ));

    $user = wp_get_current_user();
    error_log(serialize($user->roles));

    $login_form = '<h3>Please login to view content</h3>' . wp_login_form(array('echo' => false));

    switch(strtolower($type)){
        case 'applicant':
            if (!current_user_can('applicant') && !current_user_can('administrator')){
                $content = $login_form;
            }
            break;
        case 'recipient':
            if (!current_user_can('recipient') && !current_user_can('administrator')){
                $content = $login_form;
            }
            break;
        case 'parter':
            if (!current_user_can('partner') && !current_user_can('administrator')){
                $content = $login_form;
            }
            break;
        case 'all':
            if (!current_user_can('administrator')){
                $content = $login_form;
            }
            break;
        default:
            $content = $login_form;
            break;
    }

    return do_shortcode($content);

}

/* ------------- GRAVITY FORMS MODS -------------*/
function gravity_forms_selects()
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
            jQuery('#user_creation_form').change(function(){
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
    <h2>Create New Applicant Settings</h2>
    <h4>Select the form, username and email fields that will be used to create a new user when the form is submitted</h4>
    <p><label for="user_creation_form">Form</label></p>
    <select name="user_creation_form" id="user_creation_form">
        <option value=""></option>
        <?php foreach($forms as $form) : ?>
        <option value="<?php echo $form['id']; ?>" <?php selected(get_option('user_creation_form'), $form['id']); ?>>
            <?php echo $form['title']; ?>
        </option> 
        <?php endforeach; ?>
    </select>
    <div class="<?php echo $selected_username ? '' : 'hidden'; ?>">
        <p><label for="user_creation_form_username">Username Form Field</label></p>
        <select name="user_creation_form_username" id="user_creation_form_username" class="gf-form-field-select">
        <?php foreach($form_fields as $field) : ?>
        <option value="<?php echo $field['id']; ?>" <?php selected(get_option('user_creation_form_username'), $field['id']); ?>>
            <?php echo $field['label']; ?>
        </option> 
        <?php endforeach; ?>
        </select>
    </div>
    <div class="<?php echo $selected_email ? '' : 'hidden'; ?>">
        <p><label for="user_creation_form_email">Email Form Field</label></p>
        <select name="user_creation_form_email" id="user_creation_form_email" class="gf-form-field-select">
        <?php foreach($form_fields as $field) : ?>
        <option value="<?php echo $field['id']; ?>" <?php selected(get_option('user_creation_form_email'), $field['id']); ?>>
            <?php echo $field['label']; ?>
        </option> 
        <?php endforeach; ?>
        </select>
    </div>
    <div>
        <h2>How To Prefill Form Fields</h2>
        <ol>
            <li>In Gravity Forms plugin, select the form you wish to have prefilled.</li>
            <li>Select the field you wish to prefill by clicking on the small triangle next to the field name.</li>
            <li>Select the advanced tab.</li>
            <li>Check the box labeled 'Allow field to be populated dynamically'.</li>
            <li>
                In the resulting dropdown, add one of the following identifiers:
                <ol>
                    <li>first_name (to prefill field with current user first name)</li>
                    <li>last_name (to prefill field with current user last name)</li>
                    <li>user_email (to prefill field with current user email)</li>
                </ol>
            </li>
        </ol>  
    </div>
    <?php
}

add_action('admin_post_gf_field_select', 'gravity_form_field_select');
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
    $first_name         = trim(rgar($entry, $selected_username . '.3'));
    $last_name          = trim(rgar($entry, $selected_username . '.6'));
    $username           = $first_name . '_' . $last_name;
    $email              = rgar($entry, $selected_email);

    $user = new Array_User('applicant');
    try{
        $created_user = $user->create($username, $email);
        update_user_meta($created_user, 'first_name', $first_name);
        update_user_meta($created_user, 'last_name', $last_name);
    }
    catch(Exception $e)
    {
        //send email to site admin
        wp_mail(bloginfo('admin_email'), "Application Error. Form ID: {$form['id']}", $e->getMessage());
    }
    
}

add_filter('gform_field_value_first_name', 'prefill_supplemental_form', 10, 3);
add_filter('gform_field_value_last_name', 'prefill_supplemental_form', 10, 3);
add_filter('gform_field_value_user_email', 'prefill_supplemental_form', 10, 3);
function prefill_supplemental_form($value, $field, $name)
{
    $user = wp_get_current_user();
    $value = '';
    switch($name){
        case 'first_name':
            $value = get_user_meta($user->ID, 'first_name', true);
            break;
        case 'last_name';
            $value = get_user_meta($user->ID, 'last_name', true);
            break;
        case 'user_email';
            $value = $user->user_email;
            break;
        default:
            break;
    }

    return $value;
}

/*---------- USER MANAGEMENT ----------*/
add_filter('bulk_actions-users', 'email_selected_users');
function email_selected_users($actions){
    $actions['email-all'] = 'Email All';
    return $actions;
}

add_filter('handle_bulk_actions-users', 'handle_email_selected_users', 10, 3);
function handle_email_selected_users($redirect_to, $action, $user_ids)
{
    //EARLY OUT: make sure we are handling the correct action
    if ($action !== 'email-all'){
        return $redirect_to;
    }

    $mailto = 'mailto:';
    $user_emails = [];

    foreach($user_ids as $user_id){
        $user = get_userdata($user_id);
        $user_emails[] = $user->user_email;
    }

    $mailto .= implode(',', $user_emails);

    header("location: $mailto");

    die();
}

add_action('set_user_role', 'add_date_scholarship_awarded', 10, 3);
function add_date_scholarship_awarded($user_id, $role, $old_role)
{
    //EARLY OUT: make sure we are acting when new role is recipient
    if($role !== 'recipient'){
        return;
    }

    update_user_meta($user_id, 'date_scholarship_awarded', current_time('mysql'));
}

add_filter('manage_users_columns', 'date_scholarhip_awarded_column');
function date_scholarhip_awarded_column($columns){

    $role = $_REQUEST['role'] ?? false;

    //EARLY OUT: only add column if role is filtered
    if ($role !== 'recipient') {
        return $columns;
    }

    if ($role != false) {
        unset($columns['posts']);
    }

    $columns['date_scholarship_awarded'] = 'Scholarship Date';
    return $columns; 
}

add_filter('manage_users_custom_column', 'populate_scholarship_date_column', 10, 3);
function populate_scholarship_date_column($output, $column_name, $user_id)
{
    if($column_name === 'date_scholarship_awarded'){
        $date_string = get_user_meta($user_id, 'date_scholarship_awarded', true);
        $date = new DateTime($date_string);
        $output = $date->format('Y-m-d');
    }

    return $output;
}