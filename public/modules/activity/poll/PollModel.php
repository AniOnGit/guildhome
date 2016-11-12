<?php

/**
 * Model class for polls, which communicates with poll related database tables.

 * @author Ani
 */
class PollModel implements IDatabaseModel {

    function getActivityById(int $activity_id) {
        $db = db::getInstance();
        $sql = "SELECT `title`, `description`, `expire_date` FROM `activity_polls` WHERE `activity_id`=$activity_id";
        $query = $db->query($sql);

        if ($query !== false and $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $activity = $result_row;
            }
            return $activity;
        }
        return false;
    }

    protected function saveActivityTypeDetails(int $activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();

        // save activity meta data
        $this->save($type = 3); // 3=poll

        // save 'poll' specific data
        $activity_id = $db->insert_id;
        $poll_title = $env->post('activity')['title'];
        $poll_description = $env->post('activity')['description'];
        $expire_date = $env->post('activity')['expire_date'];

        $sql = "INSERT INTO `activity_polls` (`activity_id`, `title`, `description`, `expire_date`) VALUES ($id, $poll_title, $poll_description, $expire_date);";
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clearPost('activity');
            return true;
        }
        return false;
    }

    public function registerDatabaseModel() {
        echo 'PollModel registered!';
        $_SESSION['dbconfig']['PollModel'] = $this;
    }

    public function createDatabaseTables($overwriteIfExists) {
        echo 'PollModel.createDatabaseTables() called!';

        $db = db::getInstance();

        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS activity_polls_votes,activity_polls_options,activity_polls";
            $db->query($overwriteIfExists);
        }

        $sqlPollsTable = "CREATE TABLE `activity_polls` (`activity_id` INT(6) NOT NULL,`title` VARCHAR(100) NOT NULL,
				`description` VARCHAR(500) NULL DEFAULT NULL, `expire_date` DATETIME NULL DEFAULT NULL,PRIMARY KEY (`activity_id`));";
        $db->query($sqlPollsTable);
        $sqlOptionsTable = "CREATE TABLE `activity_polls_options` (`poll_id` INT(6) NOT NULL, `option_id` INT(6) NOT NULL,
				`option_text` VARCHAR(100) NOT NULL, PRIMARY KEY (`option_id`), INDEX `fk_pollOption2Poll_idx` (`poll_id` ASC),
				CONSTRAINT `fk_pollOption2Poll` FOREIGN KEY (`poll_id`) REFERENCES `guildportal`.`activity_polls` (`activity_id`)
				ON DELETE CASCADE ON UPDATE CASCADE);";
        $db->query($sqlOptionsTable);
        $sqlVotesTable = "CREATE TABLE `activity_polls_votes` (`option_id` INT(6) NOT NULL, `user_id` INT(6) UNSIGNED NOT NULL,
				PRIMARY KEY (`option_id`, `user_id`), INDEX `fk_pollOptionVotes2User_idx` (`user_id` ASC), CONSTRAINT `fk_pollOptionVotes2Option`
				FOREIGN KEY (`option_id`) REFERENCES `guildportal`.`activity_polls_options` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE,
				CONSTRAINT `fk_pollOptionVotes2User` FOREIGN KEY (`user_id`) REFERENCES `guildportal`.`users` (`id`) ON DELETE NO ACTION ON UPDATE
				NO ACTION);";
        $db->query($sqlVotesTable);
    }
}
$init = new PollModel();
$init->registerDataBaseModel();

