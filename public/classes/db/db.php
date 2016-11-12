<?php

class db {
    private static $dbInstance = NULL;
    
    public static $host;
    public static $user;
    public static $pass;
    public static $dbname;

    public function __construct() {}
    public function __clone() {}

    public static function getInstance() {
        if (self::$dbInstance === NULL) {
            self::$dbInstance = new self;
            self::connect();
        }
        
        return self::$dbInstance;
    }

    private static function connect() {
        self::$dbInstance = new mysqli(self::$host, self::$user, self::$pass, self::$dbname);
        self::$dbInstance->set_charset("utf8");
    }

}
include_once 'config/db.php';
//config/db.php:
//<?php
//db::$host = "";
//db::$dbname = "";
//db::$user = "";
//db::$pass = "";

