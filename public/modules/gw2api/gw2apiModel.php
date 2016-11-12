<?php

class gw2apiModel implements IDatabaseModel {

    public function createDatabaseTables(boolean $overwriteIfExists) {
        $db = db::getInstance();

        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS api_roster";
            $db->query($overwriteIfExists);
        }

        $sqlShoutsTable = "CREATE TABLE `api_roster` (`account_name` varchar(64) NOT NULL,`guild_rank` varchar(16) NOT NULL,PRIMARY KEY (`account_name`))
            ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlShoutsTable);
    }
}