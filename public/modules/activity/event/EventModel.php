<?php
class EventModel implements IDatabaseModel {
	public function createDatabaseTables(boolean $overwriteIfExists) {
		$db = db::getInstance ();

		if ($overwriteIfExists) {
			$sqlDropExistingPollTables = "DROP TABLE IF EXISTS activity_events_signups_user,activity_events_signups,activity_events,activity_events_types";
			$db->query ( $overwriteIfExists );
		}

		$sqlEventTypesTable = "CREATE TABLE `activity_events_types` (`id` int(6) unsigned NOT NULL AUTO_INCREMENT,`name` varchar(100) NOT NULL,
				PRIMARY KEY (`id`)) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;";
		$db->query ( $sqlEventTypesTable );
		$sqlEventTypesTableInsert = "INSERT INTO `activity_events_types`(`id`,`name`) VALUES (1,`MISSIONS`),(2,`RAID`),(3,`FRACTAL`),(4,`PVP`),(5,`FUN`)";
		$db->query ( $sqlEventTypesTableInsert );

		$sqlEventsTable = "CREATE TABLE `activity_events` (`activity_id` int(6) NOT NULL,`event_type` int(6) unsigned NOT NULL,`title` varchar(100) NOT NULL,
				`description` varchar(500) DEFAULT NULL,`date` date NOT NULL,`time` time NOT NULL,`calendar_activated` tinyint(4) NOT NULL DEFAULT '1',
				`schedule_activated` tinyint(4) NOT NULL DEFAULT '0',`comments_activated` tinyint(4) NOT NULL DEFAULT '1',
				`signups_activated` tinyint(4) NOT NULL DEFAULT '0',`template_activated` tinyint(4) NOT NULL DEFAULT '0',PRIMARY KEY (`activity_id`),
				KEY `fk_events_to_eventtype_idx` (`event_type`),CONSTRAINT `fk_events_to_activity`
				FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,CONSTRAINT `fk_events_to_eventtype`
				FOREIGN KEY (`event_type`) REFERENCES `activity_events_types` (`id`) ON UPDATE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		$db->query ( $sqlEventsTable );
		$sqlEventsSignupsTable = "CREATE TABLE `activity_events_signups` (`event_id` int(6) NOT NULL,`minimal_signups_activated` tinyint(1) NOT NULL DEFAULT '0',
				`minimal_signups` int(2) NOT NULL DEFAULT '0',`maximal_signups_activated` tinyint(1) NOT NULL DEFAULT '0',`maximal_signups` int(2) NOT NULL DEFAULT '0',
				`signup_open_beyond_maximal` tinyint(1) NOT NULL DEFAULT '0',`class_registration_enabled` tinyint(1) NOT NULL DEFAULT '0',
				`roles_registration_enabled` tinyint(1) NOT NULL DEFAULT '0',`preference_selection_enabled` tinyint(1) NOT NULL DEFAULT '0',PRIMARY KEY (`event_id`),
				CONSTRAINT `fk_event_signups_to_event` FOREIGN KEY (`event_id`) REFERENCES `activity_events` (`activity_id`) ON DELETE CASCADE ON UPDATE CASCADE)
				ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		$db->query ( $sqlEventsSignupsTable );
		$sqlEventsSignupsUserTable = "CREATE TABLE `activity_events_signups_user` (`event_id` int(6) NOT NULL,`user_id` int(6) unsigned NOT NULL,
				`registration_id` int(6) unsigned NOT NULL,`preferred` tinyint(1) unsigned DEFAULT '0',PRIMARY KEY (`event_id`,`user_id`,`registration_id`),
				KEY `fk_events_signups_user_to_user_idx` (`user_id`),CONSTRAINT `fk_events_signups_user_to_event` FOREIGN KEY (`event_id`)
				REFERENCES `activity_events` (`activity_id`) ON DELETE CASCADE ON UPDATE CASCADE,CONSTRAINT `fk_events_signups_user_to_user`
				FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		$db->query ( $sqlEventsSignupsUserTable );
	}
}