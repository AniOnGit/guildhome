<?php

class Activity_Stream extends Pagination {
    // start controller (i guess)
    function initEnv() {
        Toro::addRoute(["/activities" => "Activity_Stream"]);
        Toro::addRoute(["/activities/:number" => "Activity_Stream"]);
        Toro::addRoute(["/activities/:string" => "Activity_Stream"]);
        Toro::addRoute(["/activities/:string/:number" => "Activity_Stream"]);
    }
    
    function get($alpha = 0, $offset = 0) {
        $env = Env::getInstance();
        $env->clearPost('activity');
        $menu = new Menu();
        if (is_numeric($alpha)) {
            $offset = $alpha;
            $page = Page::getInstance();
            $page->setContent('{##main##}', $menu->activityMenu('activity'));
            $page->addContent('{##main##}', $this->setOffset($offset)->getAllActivitiesView());
            $page->addContent('{##main##}', $this->setPagination($offset, "/activities/")->paginationView());
        } elseif (is_string($alpha)) {
            $page = Page::getInstance();
            $page->setContent('{##main##}', $menu->activityMenu($alpha));
            $type_id = Activity::getActivityTypeIDByName($alpha);
            $page->addContent('{##main##}', $this->setOffset($offset)->getAllActivitiesView($type_id));
            $page->addContent('{##main##}', $this->setPagination($offset, "/activities/$alpha/")->paginationView());
        }
        return false;
    }
    // end controller    
    // start model (i suppose)
    function getActivities($interval = NULL, $type = NULL) {
        $db = db::getInstance();
        
        $interval_sql = (is_numeric($interval)) ? "HAVING create_time >= DATE_SUB(CURDATE(), INTERVAL $interval DAY)" : "";
        $type_sql = (is_numeric($type)) ? " AND activities.type = $type" : "";
        $limit = $this->getLimit();
        $offset = $this->getOffset();
        $offset_sql = (is_numeric($offset) AND $offset != 0)
            ? "LIMIT $limit OFFSET $offset"
            : "LIMIT $limit";
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
                    WHERE activities.deleted = 0
                    $type_sql
                    $interval_sql
                    ORDER BY event_date IS NULL, event_date ASC, activities.create_time DESC
                    $offset_sql
                ";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $activities[] = $result_row;
            }
            return $activities;
        }
        return false;
    }
    // end model
    // start view (i'd say)
    /*
     * being a patchwork funtion at the moment, a lot of stuff is jerry-rigged here
     * to-do: make this template based, make it human readable again ^^
     */   
    function getAllActivitiesView($type = NULL) {
        $env = Env::getInstance();
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        
        $activities = ($type === NULL) ? $this->getActivities(10) : $this->getActivities(NULL, $type);
        if (false !== $activities) {
            $d_var = getdate($activities[0]->timestamp);
            $activity_loop = '';
            if ($activities[0]->event_date == NULL) {
                $activity_loop .= '<header class="day_header"><h3>' . $d_var["weekday"] . '</h3>, <time datetime="'.date('c', $activities[0]->timestamp).'">' . $d_var["month"] . ' '. $d_var["mday"] . '</time></header>';
            }
            $activity_loop .= '<ul class="day_wrapper">';
            $activity_loop .= '<li><ul class="activity_wrapper ' . $activities[0]->type_name . '">';
            $last_type = $activities[0]->type;
            $last_day = $activities[0]->event_day;
            foreach ($activities as $act) {
                if ($last_day != $act->event_day) {
                    $activity_loop .= '</ul></li></ul>';
                    $d_var = getdate($act->timestamp);
                    if ($act->event_date == NULL) {
                        $activity_loop .= '<header class="day_header"><h3>' . $d_var["weekday"] . '</h3>, <time datetime="'.date('c', $act->timestamp).'">' . $d_var["month"] . ' '. $d_var["mday"] . '</time></header>';
                    }
                    $activity_loop .= '<ul class="day_wrapper">';
                    $activity_loop .= '<li><ul class="activity_wrapper ' . $act->type_name . ' ' . $act->id . '">';
                    $last_day = $act->event_day;
                }

                if ($last_type != $act->type) {
                    $activity_loop .= '</ul>';
                    $activity_loop .= '<ul class="activity_wrapper ' . $act->type_name . '">';
                    $last_type = $act->type;
                }

                $hooks = $env::getHooks($act->type_name);
                if ($hooks!== false) {
                    foreach ($hooks as $hook) {
                        $subView = $hook[$act->type_name]($act->id, true);
                        $activity_loop .= '<li>' . $subView . '</li>';
                    }
                }

            }

            $activity_loop .= '</ul></li>';
            $activity_loop .= '</ul>';
            $view->addContent('{##data##}',  $activity_loop);
        }
        $view->replaceTags();
        return $view;
    }
    // end view
}
$init_env = new Activity_Stream();
$init_env->initEnv();
unset($init_env);
