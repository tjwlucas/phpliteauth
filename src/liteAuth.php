<?php

namespace liteAuth;

class liteAuth
{
    public $db;
    private $dbfile;
    public $user;
    private $authtoken;
    public function __construct($db, $prefix = 'liteauth_', $preMigrationBackup = False)
    {   
        session_start();
        $this->dbfile = $db;
        $this->prefix = $prefix;
        $this->opendb($preMigrationBackup);
        $this->authtoken = $_SESSION['liteauth']['token'];
        $this->resumeSession($this->authtoken);
    }

    private function opendb($preMigrationBackup){
        $this->db = new \Medoo\Medoo([
            'database_type' => 'sqlite',
            'database_file' => $this->dbfile
        ]);
        $this->runmigrations($preMigrationBackup);
    }

    private function runmigrations($preMigrationBackup) {
        $next = $this->db->get($this->prefix.'migrations', 'id', [ "ORDER" => ['id' => 'DESC']]) + 1;
        while(file_exists(__DIR__.'/db/'.$next.'.sql'))
        {
            if($preMigrationBackup == True)
            {
                $backupdir = dirname($this->dbfile).'/premigrationbackups';
                if(!file_exists($backupdir))
                {
                    mkdir($backupdir);
                }
                copy($this->dbfile, $backupdir.'/pre-'.$next.'-'.basename($this->dbfile));
            }
            $sql = file_get_contents(__DIR__.'/db/'.$next.'.sql');
            $runsql = str_replace('__TABLE_PREF__', $this->prefix , $sql);
            $sqlarray = explode(';', $runsql);
            foreach($sqlarray as $stmt)
            {
                $this->db->query($stmt);
            }
            $this->db->insert($this->prefix.'migrations' , ['id' => $next] );
            $next++;
        }
    }

    public function newUser($user, $pass, $email = '', $fname = '', $sname = '' , $admin = False){
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        return $this->db->insert($this->prefix.'users', ['user' => $user, 'pass' => $hash, 'admin' => $admin, 'email' => $email, 'first_name' => $fname, 'surname' => $sname]) ? $this->db->id() : False;
    }

    public function authUser($user, $pass){
        $record = $this->db->get($this->prefix.'users', ['user', 'pass', 'id'], ['user' => $user]);
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
            $this->db->insert($this->prefix.'authtokens', ['user_id'=>$id, 'token'=>$newtoken]);
            $_SESSION['liteauth']['token'] = $newtoken;
            $this->authtoken = $newtoken;
        }
        else
            return False;
    }

    public function resumeSession($authtoken)
    {
        if( $id = $this->db->get($this->prefix.'authtokens', 'user_id', ['token'=>$authtoken]) )
            $this->user = new User($this, $id);
        else
            return False;
    }

    public function logout($everywhere = False)
    {
        if($everywhere)
            $this->db->delete($this->prefix.'authtokens', ['user_id' => $this->user->id]);
        else
            $this->db->delete($this->prefix.'authtokens', ['token' => $this->authtoken]);
        $this->user = '';
    }

    public function loginFromPost($user = 'user', $pass='pass')
    {
        if(isset($_POST[$user]) && isset($_POST[$pass]))
            return $this->login($_POST[$user], $_POST[$pass]);
    }

    public function countUsers()
    {
        return $this->db->count($this->prefix.'users');
    }

    public function existUsers()
    {
        return $this->countUsers() > 0 ? True : False;
    }

    public function registerFromPost()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if(!isset($_POST['user']))
            {
                throw new \Exception('Must set a username.');
            }
            if(!isset($_POST['pass']))
            {
                throw new \Exception('Must set a password.');
            }
            if($_POST['pass']!=$_POST['pass2'])
            {
                throw new \Exception('passwords don\'t match.');
            }
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $fname = isset($_POST['fname']) ? $_POST['fname'] : '';
            $sname = isset($_POST['sname']) ? $_POST['sname'] : '';
            $admin = isset($_POST['admin']) ? $_POST['admin'] : False;
            return $this->newUser($_POST['user'], $_POST['pass'], $email, $fname, $sname , $admin);
        }
    }
}