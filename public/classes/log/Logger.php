<?php

class Logger {
    private static $instance;
    private static $log_file, $fp;
    
    protected function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    public static function getInstance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public static function lfile($path) {
        self::$log_file = $path;
    }
    
    public static function lwrite($message) {
        if (!is_resource(self::$fp)) {
            self::lopen();
        }
        $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
        $time = @date('[D M d G:i:s Y]');
        fwrite(self::$fp, "$time ($script_name) $message" . PHP_EOL);
    }
    
    public static function lclose() {
        fclose(self::$fp);
    }
    
    private static function lopen() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $log_file_default = 'c:/php/logfile.txt';
        } else {
            $log_file_default = '/tmp/logfile.txt';
        }
        $lfile = self::$log_file ? self::$log_file : $log_file_default;
        self::$fp = fopen($lfile, 'a') or exit("Can't open $lfile!");
    }
}