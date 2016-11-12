<?php

class ActivityModel implements IDatabaseModel {

    function createDatabaseTables(boolean $overwriteIfExists) {
        $db = db::getInstance();

        if ($overwriteIfExists) {
            $sqlDropExistingActivityTables = "DROP TABLE IF EXISTS activities,activity_types";
            $db->query($overwriteIfExists);
        }

        $sqlActivityTypesTable = "CREATE TABLE `activity_types` (`id` int(6) unsigned NOT NULL AUTO_INCREMENT,`name` varchar(50) NOT NULL,
            `description` varchar(100) DEFAULT NULL,`name_plural` varchar(45) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;";
        $db->query($sqlActivityTypesTable);
        $sqlInsertActivityTypesTable = "INSERT INTO `activity_types`(`id`,`name`,`description`,`name_plural`) VALUES
            (1,`shout`,`a shout`,`shouts`),(2,`event`,`an event`,`events`),(3,`poll`,`a poll`,`polls`),(4,`actionmessage`,`an action message`,`actionmessages`)";
        $db->query($sqlInsertActivityTypesTable);
        $sqlActivitiesTable = "CREATE TABLE `activities` (`id` int(6) NOT NULL AUTO_INCREMENT,`userid` int(6) NOT NULL,`create_time` int(11) NOT NULL,
            `type` int(1) DEFAULT NULL,`comments_enabled` tinyint(1) NOT NULL DEFAULT '0',`deleted` int(11) DEFAULT '0',PRIMARY KEY (`id`))
            ENGINE=InnoDB AUTO_INCREMENT=261 DEFAULT CHARSET=latin1;";
        $db->query($sqlActivitiesTable);
    }
}