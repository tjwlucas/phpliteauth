<?php

namespace liteAuth;

class liteAuth
{
    private $db;
    private $dbfile;
    public function __construct($db)
    {   
        $this->dbfile = $db;
        $this->opendb();
    }

    private function opendb(){
        $this->db = new \Medoo\Medoo([
            'database_type' => 'sqlite',
            'database_file' => $this->dbfile
        ]);
        $this->runmigrations();
    }

    private function runmigrations() {
        $next = $this->db->get('liteauth_migrations', 'id', [ "ORDER" => ['id' => 'DESC']]) + 1;
        while(file_exists(__DIR__.'/db/'.$next.'.sql'))
        {
            $sql = file_get_contents(__DIR__.'/db/'.$next.'.sql');
            $this->db->query($sql); 
            $this->db->query("insert into liteauth_migrations (id, run) values ( $next , CURRENT_TIMESTAMP );");
            $next++;
        }
    }

    public function newUser($user, $pass){
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        if($this->db->get('liteauth', 'user', ['user' => $user]))
            throw new \Exception('User already exists');
        $this->db->insert('liteauth', ['user' => $user, 'pass' => $hash]);
    }

    public function authUser($user, $pass){
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $record = $this->db->get('liteauth', ['user', 'pass'], ['user' => $user]);
        if(!$record)
            throw new \Exception('User does not exist');
        return password_verify($pass, $record['pass']);
    }
}