<?php

class CommentModel implements IDatabaseModel {

    function createDatabaseTables(boolean $overwriteIfExists) {
        $db = db::getInstance();

        if ($overwriteIfExists) {
            $sqlDropExistingActivityTables = "DROP TABLE IF EXISTS comment_mapping,comments";
            $db->query($overwriteIfExists);
        }

        $sqlCommentMappingTable = "CREATE TABLE `comment_mapping` (`activity_id` int(11) DEFAULT NULL,`comment_id` int(11) DEFAULT NULL)
            ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlCommentMappingTable);
        $sqlCommentTable = "CREATE TABLE `comments` (`id` int(11) NOT NULL AUTO_INCREMENT,`content` varchar(500) DEFAULT NULL,`create_time` int(11) NOT NULL,
            `parent_comment` int(11) DEFAULT NULL,`userid` int(11) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=210 DEFAULT CHARSET=latin1;";
        $db->query($sqlCommentTable);
    }
}