<?php

final class Array_User
{
    private $role;

    public function __construct($role)
    {
        $this->role = $role;
    }

    public function create(string $username, string $email) : int
    {
        $password = wp_generate_password(12, true);
        $new_user = wp_create_user($username, $password, $email);

        if(is_wp_error($new_user)){
            throw new Exception($new_user->get_error_message());
        }

        $user = new WP_User($new_user);
        $user->add_role($this->role);

        wp_new_user_notification($new_user, null, 'both');

        return $new_user;
    }

    public function get_by_id(int $id) : WP_User
    {
        $user = new WP_User($id);
        $has_role = in_array($this->role, $user->roles);

        if(!$applicant || !$has_role)
        {
            throw new Exception("User ID {$id} either does not exist or is not a {$this->role}");
        }

        return $user;
    }
}