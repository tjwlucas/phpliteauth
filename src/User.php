<?php

namespace liteAuth;

class User
{
    private $auth;
    public function __construct($auth, $id)
    {
        $this->id = $id;
        $this->auth = $auth;
        $info = $auth->db->get('liteauth_users', ['user', 'first_name', 'surname', 'email', 'admin'], ['id' => $id]);
        foreach($info as $key => $value)
            $this->$key = $value;
        return $this;
    }

    public function name()
    {
        if(isset($this->first_name) && isset($this->surname))
            return $this->first_name . ' ' . $this->surname;
        elseif(isset($this->first_name))
            return $this->first_name;
        else
            return $this->user;
    }

    public function save()
    {
        $reflection = new \ReflectionObject($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach($properties as $prop)
            $info[$prop->name] = $this->{$prop->name};
        return $this->auth->db->update('liteauth_users', $info, ['id' => $this->id]);
    }
}