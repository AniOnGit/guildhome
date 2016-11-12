<?php

class Page extends View {
    private static $instance;
    
    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public static function getInstance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }
    
}
