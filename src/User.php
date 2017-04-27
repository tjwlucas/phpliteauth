<?php

namespace liteAuth;

class User
{
    public function __construct($auth, $id)
    {
        $this->id = $id;
        $info = $auth->db->get('liteauth_users', ['user', 'first_name', 'surname', 'email', 'admin'], ['id' => $id]);
        if(isset($info['first_name']) && isset($info['surname']))
            $this->name = $info['first_name'] . ' ' . $info['surname'];
        elseif(isset($info['first_name']))
            $this->name = $info['first_name'];
        else
            $this->name = $this->username;
        foreach($info as $key => $value)
            $this->$key = $value;
        return $this;
    }
}