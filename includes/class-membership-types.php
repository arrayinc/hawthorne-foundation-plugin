<?php

final class Array_Membership_Types
{

    private $restricted_message = "Sorry, you are not allowed to view this content.";

    public static function get_instance()
    {
        static $instance = null;
        if (is_null($instance)){
            $instance = new self;
            $instance->setup();
        }

        return $instance;
    }

    private function __construct() {}

    private function __clone()
    {
        __doing_it_wrong(__FUNCTION__ , esc_html__("That's not how you do it"));
    }

    private function __wakeup()
    {
        __doing_it_wrong(__FUNCTION__ , esc_html__("That's not how you do it"));
    }

    private function setup()
    {
        if(is_null(get_role('applicant'))){
            $role = add_role('applicant', 'Applicant');
            $role->add_cap('read');
            $role->add_cap('level_0');
        }

        if(is_null(get_role('recipient'))){
            $role = add_role('recipient', 'Recipient');
            $role->add_cap('read');
            $role->add_cap('level_0');
        }

        if(is_null(get_role('partner'))){
            $role = add_role('partner', 'Partner');
            $role->add_cap('read');
            $role->add_cap('level_0');
        }

        add_shortcode('members', array($this, 'restrict_content_shortcode'));
    }

    public function restrict_content_shortcode($atts, $content = null)
    {
        extract(shortcode_atts(
            array('type' => 'all'),
            $atts
        ));

        switch(strtolower($type)){
            case 'applicant':
                if (!current_user_can('applicant') || !current_user_can('administrator')){
                    $content = $this->restricted_message;
                }
                break;
            case 'recipient':
                if (!curent_user_can('recipient') || !current_user_can('administrator')){
                    $content = $this->restricted_message;
                }
                break;
            case 'parter':
                if (!current_user_can('partner') || !current_user_can('administrator')){
                    $content = $this->restricted_message;
                }
                break;
            case 'all':
                if (!current_user_can('administrator')){
                    $content = $this->restricted_message;
                }
                break;
            default:
                $content = $this->restricted_message;
                break;
        }

        return $content;

    }
}

Array_Membership_Types::get_instance();