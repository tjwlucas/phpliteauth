<?php

namespace liteAuth;

class liteAuth
{
    private $db;
    private $dbfile;
    public function __construct($db)
    {   
        $this->dbfile = $db;
        if(!file_exists($this->dbfile))
            $this->setupdb();
        else
            $this->opendb();
    }

    private function setupdb()
    {
        echo 'Setting up db...';
        $this->opendb();
    }

    private function opendb(){
        $this->db = new \Medoo\Medoo([
            'database_type' => 'sqlite',
            'database_file' => $this->dbfile
        ]);
    }
}