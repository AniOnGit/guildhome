<?php

class PagesModel implements IDatabaseModel {

    public function createDatabaseTables(boolean $overwriteIfExists) {
        $db = db::getInstance();

        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS pages_slugs,pages";
            $db->query($overwriteIfExists);
        }

        $sqlPagesSlugsTable = "CREATE TABLE `pages_slugs` (`pages_id` int(11) NOT NULL,`slug` varchar(45) DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlPagesSlugsTable);
        $sqlPagesTable = "CREATE TABLE `pages` (`id` int(11) NOT NULL,`content` text,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlPagesTable);
    }
}