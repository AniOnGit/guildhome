<?php

abstract class Activity extends Pagination {
    // start model (i suppose)
    protected abstract function getActivityById($activity_id);

    static function getActivityMetaById($id = NULL) {
        if ($id === NULL) {
            return false;
        }
        $db = db::getInstance();
        $sql = "SELECT
                    activities.id,
                    activities.userid,
                    activities.create_time AS timestamp,
                    activities.comments_enabled AS comments_enabled,
                    activities.deleted as deleted,
                    activities.type AS type,
                    activity_types.name AS type_name,
                    activity_types.description AS type_description,
                    from_unixtime(activities.create_time) AS create_time,
                    (SELECT concat(ae.date, ' ', ae.time) AS timestamp
                        FROM activity_events ae
                        WHERE ae.activity_id = activities.id
                        HAVING DATE_ADD(timestamp,INTERVAL 2 HOUR) >= NOW() AND timestamp <= DATE_ADD(NOW(),INTERVAL 48 HOUR)
                    ) AS event_date,
                    DAY (from_unixtime(activities.create_time)) as event_day
                    FROM activities
                    INNER JOIN activity_types
                        ON activities.type = activity_types.id
                    WHERE activities.id = $id
                    LIMIT 1;";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows == 1) {
            $result = $query->fetch_object();
            return $result;
        }
        return false;
    }

    public static function getActivityCountByType($type = 0) {
        $db = db::getInstance();
        $sql = "SELECT
                    (SELECT
                        count(*) AS count
                        FROM activities
                        WHERE
                            type = $type
                        AND
                            deleted = 0
                    ) AS count_all,
                    count(*) AS count
                    FROM activities
                    WHERE
                        deleted = 0 AND type = $type
                    AND
                        from_unixtime(create_time) >= DATE_SUB(CURDATE(), INTERVAL 10 DAY);";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows == 1) {
            $count = $query->fetch_object();
            return $count;
        }
        return false;
    }

    public static function getActivityTypeIDByName($activity_type_name = null) {
        if ($activity_type_name === NULL) {
            return false;
        }
        $db = db::getInstance();
        $type_name = $db->real_escape_string(strip_tags($activity_type_name, ENT_QUOTES));

        $sql = "SELECT
                    activities.type AS type
                    FROM activities
                    INNER JOIN activity_types
                        ON activities.type = activity_types.id
                    WHERE
                        activity_types.name = '$type_name'
                    OR
                        activity_types.name_plural = '$type_name'
                    LIMIT 1;";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows == 1) {
            $result = $query->fetch_object();
            return $result->type;
        }
        return false;
    }

    public function saveActivity($type_name) {
        if (! $this->validateActivity()) {
            return false;
        }
        $env = Env::getInstance();
        $db = db::getInstance();
        $login = new Login();
        $type_id = $this->getActivityTypeIDByName($type_name);

        $userid = $login->currentUserID();
        $uxtime = time();
        $allow_comments = isset($env->post('activity')['comments']) ? '1' : '0';

        $sql = "INSERT INTO activities (id, userid, create_time, type, comments_enabled) VALUES ('NULL', '$userid', '$uxtime', '$type_id', '$allow_comments');";
        $query = $db->query($sql);

        if ($query !== false) {
            $activity_id = $db->insert_id;
            $this->saveActivityTypeDetails($activity_id);
            return $activity_id;
        }
        return false;
    }

    protected abstract function saveActivityTypeDetails($activity_id);

    public function updateActivity(int $activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        $allow_comments = isset($env->post('activity')['comments']) ? '1' : '0';

        $sql = "UPDATE activities SET
                    comments_enabled= '$allow_comments'
                WHERE id = '$activity_id';";
        $query = $db->query($sql);
        if ($query !== false) {
            $this->updateActivityTypeDetails($activity_id);
            return $activity_id;
        }
        return false;
    }

    public function deleteActivity($activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        $login = new Login();

        $userid = $login->currentUserID();
        $actid = $this->getActivity($activity_id)->userid;
        if ($userid != $actid) {
            return false;
        }
        $sql = "UPDATE activities SET deleted = '1' WHERE id = '$activity_id';";
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clearPost('activity');
            $hooks = $env::getHooks('delete_activity_hook');
            if ($hooks !== false) {
                foreach ($hooks as $hook) {
                    $hook['delete_activity_hook']($activity_id);
                }
            }
            return true;
        }
        return false;
    }

    protected abstract function updateActivityTypeDetails($activity_id);

    public static function commentsEnabled($activity_id) {
        $db = db::getInstance();
        $sql = "SELECT comments_enabled FROM activities WHERE id = '$activity_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            $result_row = $query->fetch_object();
            $result = ($result_row->comments_enabled == '1') ? true : false;
            return $result;
        }
        return false;
    }
    // end model
    // start view (i'd say)
    /*
     * this is my attempt of making this modular. every activity-submodule can
     * register it's output-view in the env-class and we will look for that here.
     * feel free to come up with something nicer
     */
    protected abstract function getActivityView($activity_id = NULL, $compact = NULL);

    public function validateActivity() {
        return $this->validateActivityTypeDetails();
    }

    protected abstract function validateActivityTypeDetails();
    // end view
}
