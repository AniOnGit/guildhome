<?php

class Activity_Event_Signups_Widgets {
    function getSignupsByUserId($user_id) {
        $db = db::getInstance();
        $sql = "SELECT * FROM activity_events_signups_user WHERE user_id = '$user_id';";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            $activity_event = new Activity_Event();
            while ($result_row = $query->fetch_object()) {
                $signups[] = $activity_event->getActivityByID($result_row->event_id);
            }
            return $signups;
        }
        return false;
    }
    
    function getSignupsByUserIdView($user_id) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/signups/widgets/activity_event_signups_widgets_signup_history_view.php'));
        $subView = new View();
        $subView->setTmpl($view->getSubTemplate('{##widget_title##}'));
        $subView->addContent('{##widget_title_text##}', 'Signup Schedule/History');
        $subView->replaceTags();
        $view->addContent('{##widget_title##}', $subView);

        $signups = $this->getSignupsByUserId($user_id);
        if (false !== $signups && is_array($signups)) {
            $signup_loop = NULL;
            foreach ($signups as $signup) {
                $subView = new View();
                $subView->setTmpl($view->getSubTemplate('{##signup_loop##}'));
                $subView->addContent('{##activity_title##}', $signup->title);
                $subView->addContent('{##activity_details_link##}', View::linkFab('/activity/event/details/' . $signup->activity_id, 'Event details'));
                $subView->addContent('{##activity_event_date##}', $signup->date);
                $subView->addContent('{##activity_event_time##}', $signup->time);
                $subView->replaceTags();
                $signup_loop .= $subView;
            }
            $view->addContent('{##signup_loop##}', $signup_loop);
        }
        $view->replaceTags();
        return $view;
    }

}
