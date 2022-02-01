<?php

require_once("../config.php");

class DiscordUserService {
    private $con;
    public function __construct($con) {
        $this->con = $con;
    }

    public function createUser($id, $name, $discriminator, $avatar, $identity) {
        $create = $this->con->prepare("INSERT into discord__user (id, name, discriminator, avatar, identity_id) values (?, ?, ?, ?, ?);");

        $create->execute(
            array(
                $id
                , $name
                , $discriminator
                , $avatar
                , $identity
            )
        );
    }

    public function getUser($id) {
        $getU = $this->con->prepare("SELECT id, name, discriminator, identity_id from discord__user where id = ?;");
        $getU->execute(array($id));

        if ($getU->rowCount() > 0) {
            return $getU->fetch(PDO::FETCH_ASSOC);
        } else {
            return null;
        }
    }

    public function updateIdentity($id, $identity) {
        $this->con->prepare("UPDATE discord__user set identity_id = ? where id = ?;")->execute(
            array(
                $identity
                , $id
            )
        );
    }
}

class TwitchUserService {
    private $con;
    public function __construct($con) {
        $this->con = $con;
    }

    public function createUser($id, $display_name, $identity_id, $email, $profile_image_url, $offline_image_url, $description, $view_count, $follower_count, $affiliation) {
        $cUser = $this->con->prepare("INSERT into twitch__user (id, display_name, identity_id, email, profile_image_url, offline_image_url, description, view_count, follower_count, affiliation) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?);");
        $cUser->execute(
            array(
                $id
                , $display_name
                , $identity_id
                , $email
                , $profile_image_url
                , $offline_image_url
                , $description
                , $view_count
                , $follower_count
                , $affiliation
            )
        );
    }

    public function getUser($id) {
        $getU = $this->con->prepare("SELECT id, display_name, identity_id, email, profile_image_url, offline_image_url, description, view_count, follower_count, affiliation from twitch__user where id = ?;");
        $getU->execute(array($id));

        if ($getU->rowCount() > 0) {
            return $getU->fetch(PDO::FETCH_ASSOC);
        } else {
            return null;
        }
    }

    public function updateIdentity($id, $identity) {
        $this->con->prepare("UPDATE twitch__user set identity_id = ? where id = ?;")->execute(
            array(
                $identity
                , $id
            )
        );
    }

    public function updateEmail($id, $email) {
        $this->con->prepare("UPDATE twitch__user set email = ? where id = ?;")->execute(
            array(
                $email
                , $id
            )
        );
    }
}

class IdentityService {
    private $con;

    public function __construct($con) {
        $this->con = $con;
    }

    public function createIdentity($name) {
        $cIdentity = $this->con->prepare("INSERT into identity (name) values (?);");
        $cIdentity->execute(
            array(
                $name
            )
        );

        return $this->con->lastInsertId();
    }
}

class SessionService {
    private $con;
    private $session = null;

    private function generateRandomString($n = 32) { 
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        $randomString = ''; 
    
        for ($i = 0; $i < $n; $i++) { 
            $index = rand(0, strlen($characters) - 1); 
            $randomString .= $characters[$index]; 
        } 
    
        return $randomString; 
    }

    public function __construct($con) {
        $this->con = $con;
    }

    public function getSession() {
        if ($this->session !== null) return $this->session;

        if (!isset($_COOKIE["session"])) return null;

        $getSess = $this->con->prepare("SELECT id, identity_id, created from session where id = ?;");
        $getSess->execute(array($_COOKIE["session"]));

        if ($getSess->rowCount() > 0) {
            $this->session = $getSess->fetch(PDO::FETCH_ASSOC);
            return $this->session;
        } else {
            return null;
        }
    }

    public function createSession($identity) {
        require_once("../config.php")
        $session = $this->generateRandomString();

        $csession = $this->con->prepare("INSERT into session (id, identity_id) values (?, ?);");

        $csession->execute(array($session, $identity));

        setcookie("session", $session, time() + 60*60*24, "/", $config["uris"]["cookie"], true, false);
    }
}

try {
    $host = $config["database"]["host"];
    $database = $config["database"]["database"];
    $username = $config["database"]["username"];
    $password = $config["database"]["password"];
    $con = new PDO("mysql:host=$host;dbname=$database", $username, $password);

    $dus = new DiscordUserService($con);
    $tus = new TwitchUserService($con);
    $is = new IdentityService($con);
    $ss = new SessionService($con);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}
