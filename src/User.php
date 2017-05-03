<?php

namespace liteAuth;

class User
{
    private $auth;
    public function __construct($auth, $id)
    {
        $this->id = $id;
        $this->auth = $auth;
        $info = $auth->db->get($this->auth->prefix.'users', ['user', 'first_name', 'surname', 'email', 'admin'], ['id' => $id]);
        foreach($info as $key => $value)
            $this->$key = $value;
        return $this;
    }

    /** Outputs a human readable name, base on firstname and surname, if they exist for the user,
    * but falling back on username, otherwise
    */
    public function name()
    {
        if(isset($this->first_name) && isset($this->surname))
            return $this->first_name . ' ' . $this->surname;
        elseif(isset($this->first_name))
            return $this->first_name;
        else
            return $this->user;
    }

    /** Save any changes to user properties that may have been made to the database
    */
    public function save()
    {
        $reflection = new \ReflectionObject($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach($properties as $prop)
            $info[$prop->name] = $this->{$prop->name};
        return $this->auth->db->update($this->auth->prefix.'users', $info, ['id' => $this->id]);
    }

    /** Changes the password for the current user
    * takes current password, then the new password twice as inputs
    */
    public function changePass($old, $new1, $new2)
    {
        if(!$this->auth->authUser($this->user, $old))
            return False;
        elseif($new1 != $new2)
            return False;
        else
            $hash = password_hash($new1, PASSWORD_BCRYPT);
            return $this->auth->db->update($this->auth->prefix.'users', ['pass' => $hash] , ['id' => $this->id]);
    }
}