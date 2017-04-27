<?php

namespace liteAuth;

class User
{
    public function __construct($auth, $id)
    {
        $this->id = $id;
        $info = $auth->db->get('liteauth_users', '*', ['id' => $id]);
        $this->username = $info['user'];
        $this->admin = $info['admin'];
        if(isset($info['first_name']) && isset($info['surname']))
            $this->name = $info['first_name'] . ' ' . $info['surname'];
        elseif(isset($info['first_name']))
            $this->name = $info['first_name'];
        else
            $this->name = $this->username;
        $this->email = $info['email'];
        return $this;
    }
}