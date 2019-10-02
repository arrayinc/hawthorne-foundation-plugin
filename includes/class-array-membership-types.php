<?php

final class Array_Membership_Types
{

    /**
     * Custom User Types
     *
     * Key: Name used in UI
     * Value: Slug used for role look up
     * 
     * @var array
     */
    private static $types = array(
        'Applicant' => 'applicant',
        'Recipient' => 'recipient',
        'Partner'   => 'parter'
    );

    public static function get_instance()
    {
        static $instance = null;
        if (is_null($instance)){
            $instance = new self;
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

    public static function init()
    {
        foreach(static::$types as $name => $slug){
            if(is_null(get_role($slug))){
                $role = add_role($slug, $name);
                $role->add_cap('read');
            }
        }
    }

    public static function deinit()
    {
        static::reassign_users();
        static::remove_roles();
    }

    private static function reassign_users()
    {
        $query = new WP_User_Query(
            array(
                'role__in' => array_values(static::$types)
            )
        );

        $users = $query->get_results();

        //EARLY OUT: No users match criteria
        if (empty($users)){
            return;
        }

        foreach($users as $user){
            $user->set_role('subscriber');
        }
    }

    private static function remove_roles()
    {
        foreach(static::$types as $type){
            $role = get_role($type);
            remove_role($type);
        }
    }
}

return Array_Membership_Types::get_instance();