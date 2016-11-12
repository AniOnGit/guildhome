<?php

class Validation {
    private static $instance;

    public static $validation_rules = [];
    
    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public static function getInstance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    static function registerValidation($key, callable $callback) {
        self::$validation_rules[$key] = $callback;
        return true;
    }
}
