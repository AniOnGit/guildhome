<?php

class SettingsModel implements IDatabaseModel {

    public function createDatabaseTables(boolean $overwriteIfExists) {
        $db = db::getInstance();

        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS settings";
            $db->query($overwriteIfExists);
        }

        $sqlShoutsTable = "CREATE TABLE `settings` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`userid` int(11) NOT NULL,
            `setting` varchar(128) DEFAULT NULL,`setting_value` text,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=latin1;";
        $db->query($sqlShoutsTable);
    }
}