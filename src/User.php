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
        return $this;
    }
}