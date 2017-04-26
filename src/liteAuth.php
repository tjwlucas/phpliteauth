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
        $next = $this->db->get('liteauth_migrations', 'id', ['run'=>1, "ORDER" => ['id' => 'DESC']]) + 1;
        while(file_exists(__DIR__.'/db/'.$next.'.sql'))
        {
            $sql = file_get_contents(__DIR__.'/db/'.$next.'.sql');
            $this->db->query($sql); 
            $this->db->insert('liteauth_migrations', ['id' => $next, 'run' => 1]);
            $next++;
        }
    }
}