<?php

namespace liteAuth;

class liteAuth
{
    public $db;
    private $dbfile;
    public $user;
    private $authtoken;
    public function __construct($db)
    {   
        session_start();
        $this->dbfile = $db;
        $this->opendb();
        $this->authtoken = $_SESSION['liteauth']['token'];
        $this->resumeSession($this->authtoken);
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
            $this->db->insert('liteauth_migrations' , ['id' => $next] );
            $next++;
        }
    }

    public function newUser($user, $pass, $admin = False){
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        return $this->db->insert('liteauth_users', ['user' => $user, 'pass' => $hash, 'admin' => $admin]) ? $this->db->id() : False;
    }

    public function authUser($user, $pass){
        $record = $this->db->get('liteauth_users', ['user', 'pass', 'id'], ['user' => $user]);
        if(password_verify($pass, $record['pass']))
        {
            return $record['id'];
        }
        else
        {
            return False;
        }
    }

    public function login($user, $pass)
    {
        if( $id = $this->authUser($user, $pass))
        {
            $this->user = new User($this, $id);
            $newtoken = bin2hex(random_bytes(16));
            $this->db->insert('liteauth_authtokens', ['user_id'=>$id, 'token'=>$newtoken]);
            $_SESSION['liteauth']['token'] = $newtoken;
            $this->authtoken = $newtoken;
        }
        else
            return False;
    }

    public function resumeSession($authtoken)
    {
        if( $id = $this->db->get('liteauth_authtokens', 'user_id', ['token'=>$authtoken]) )
            $this->user = new User($this, $id);
        else
            return False;
    }

    public function logout($everywhere = False)
    {
        if($everywhere)
            $this->db->delete('liteauth_authtokens', ['user_id' => $this->user->id]);
        else
            $this->db->delete('liteauth_authtokens', ['token' => $this->authtoken]);
        $this->user = '';
    }

    public function loginFromPost($user = 'user', $pass='pass')
    {
        if(isset($_POST[$user]) && isset($_POST[$pass]))
            return $this->login($_POST[$user], $_POST[$pass]);
    }
}