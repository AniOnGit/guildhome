<?php

class Activity_Event_Widgets {

    function getUpcomingActivities() {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events ae LEFT JOIN activities a ON ae.activity_id = a.id WHERE a.deleted = 0 AND date >= DATE(NOW()) ORDER BY date;";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            $activity_event = new Activity_Event();
            while ($result_row = $query->fetch_object()) {
                $signups[] = $activity_event->getActivityByID($result_row->activity_id);
            }

            return $signups;
        }
        return false;
    }
    
    function getUpcomingActivitiesView() {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/widgets/activity_event_scheduled_events.php'));
        $subView = new View();
        $subView->setTmpl($view->getSubTemplate('{##widget_title##}'));
        $subView->addContent('{##widget_title_text##}', 'Upcoming Events');
        $subView->replaceTags();
        $view->addContent('{##widget_title##}', $subView);

        $act = $this->getUpcomingActivities();
        if (false !== $act && is_array($act)) {
            $activity_loop = NULL;
            foreach ($act as $activity) {
                $subView = new View();
                $subView->setTmpl($view->getSubTemplate('{##event_loop##}'));
                $subView->addContent('{##activity_title##}', $activity->title);
                $subView->addContent('{##activity_details_link##}', '/activity/event/details/' . $activity->activity_id);
                $subView->addContent('{##activity_details_link_text##}', 'Event details');
                $date = new DateTime($activity->date . ' ' . $activity->time);
                $subView->addContent('{##activity_event_date##}', $date->format('Y-m-d'));
                $subView->addContent('{##activity_event_time##}', $date->format('H:i'));
                $subView->addContent('{##activity_event_datetime##}', $date->format('Y-m-d H:i'));

                $subView->replaceTags();
                $activity_loop .= $subView;
            }
            $view->addContent('{##event_loop##}',  $activity_loop);
        }
        $view->replaceTags();
        return $view;
    }
    
}
