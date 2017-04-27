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
            $backupdir = dirname($this->dbfile).'/premigrationbackups';
            if(!file_exists($backupdir))
            {
                mkdir($backupdir);
            }
            copy($this->dbfile, $backupdir.'/pre-'.$next.'-'.basename($this->dbfile));
            $sql = file_get_contents(__DIR__.'/db/'.$next.'.sql');
            $sqlarray = explode(';', $sql);
            foreach($sqlarray as $stmt)
            {
                $this->db->query($stmt);
            }
            $this->db->query("insert into liteauth_migrations (id, run) values ( $next , CURRENT_TIMESTAMP );");
            $next++;
        }
    }

    public function newUser($user, $pass, $admin = False){
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        return $this->db->insert('liteauth', ['user' => $user, 'pass' => $hash, 'admin' => $admin]) ? $this->db->id() : False;
    }

    public function authUser($user, $pass){
        $record = $this->db->get('liteauth', ['user', 'pass'], ['user' => $user]);
        return password_verify($pass, $record['pass']);
    }
}