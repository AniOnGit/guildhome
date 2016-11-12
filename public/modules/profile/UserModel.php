<?php

class UserModel implements IDatabaseModel {

    public function createDatabaseTables(boolean $overwriteIfExists) {
        $db = db::getInstance();

        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS reset_token,user_ranks,users";
            $db->query($overwriteIfExists);
        }

        $sqlUserTable = "CREATE TABLE `users` (`id` int(6) unsigned NOT NULL AUTO_INCREMENT,`username` varchar(30) NOT NULL,
            `password_hash` varchar(64) NOT NULL,`email` varchar(50) DEFAULT NULL,`rank` int(1) DEFAULT NULL,PRIMARY KEY (`id`))
            ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;";
        $db->query($sqlUserTable);
        $sqlUserRankTable = "CREATE TABLE `user_ranks` (`id` int(6) unsigned NOT NULL,`description` varchar(50) DEFAULT NULL,PRIMARY KEY (`id`))
            ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlUserRankTable);
        $sqlResetTokenTable = "CREATE TABLE `reset_token` (`user_id` int(11) NOT NULL,`token` varchar(32) NOT NULL,
            `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,`email_sent` tinyint(4) DEFAULT '0',
            PRIMARY KEY (`user_id`,`token`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlResetTokenTable);
    }
}