<?php

class Msg {
    private static $instance;
    private $messages = [];

    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public static function getInstance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }
    
    public static function add($key, $msg) {
        $inst = self::getInstance();
        $inst->messages[$key][] = $msg;
        $_SESSION['evo']['flash_messages'] = $inst->messages;
    }
    
    public static function fetch($key, $type = 'validation') {
        if (isset($_SESSION['evo']['flash_messages'])) {
            $inst = self::getInstance();
            $inst->messages = $_SESSION['evo']['flash_messages'];

            if (isset($inst->messages[$key][0])) {
                $output = $inst->messages[$key][0];
            }
            unset($inst->messages[$key]);
            unset($_SESSION['evo']['flash_messages'][$key]);

            if (!empty($output)) {
                return '<span type="' . $type . '">'.$output.'</span>';
            }
        }
        return false;
    }
    
}
