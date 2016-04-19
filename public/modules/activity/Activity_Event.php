<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Activity_Shout
 *
 * @author Christian Voigt <chris at notjustfor.me>
 */
class Activity_Event extends Activity {

    function initEnv() {
        Toro::addRoute(["/activities/events" => "Activity_Event"]);
        Toro::addRoute(["/activity/event/:alpha" => "Activity_Event"]);
        Toro::addRoute(["/activity/event/:alpha/:alpha" => "Activity_Event"]);
    }

    function create_tables() {
        $db = db::getInstance();
        $sql = "INSERT INTO activity_types (id, name, description)
                            VALUES('2', 'event', 'an event');";
        $result = $db->query($sql);
    }

    function get($alpha = '', $id = NULL) {
        $login = new Login();
        switch ($alpha) {
            default :
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>All events</h2>');
                $this->activity_menu();
                $page->addContent('{##main##}', $this->getAllActivitiesView('2')); // 2 = event 
                break;
            case 'new' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>New event</h2>');
                $this->activity_menu();
                $page->addContent('{##main##}', $this->getNewActivityForm());
                break;
            case 'update' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>Update shout</h2>');
                $this->activity_menu();
                $page->addContent('{##main##}', $this->getUpdateActivityForm($id));
                break;
            case 'delete' :
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page = Page::getInstance();
                $page->setContent('{##main##}', '<h2>Delete event</h2>');
                $this->activity_menu();
                $page->addContent('{##main##}', $this->getDeleteActivityForm($id));
                break;
        }
    }

    function post($alpha, $id = NULL) {
        $env = Env::getInstance();
        $login = new Login();
        if (!$login->isLoggedIn()) {
            return false;
        }
        switch ($alpha) {
            case 'new' :
                if ($this->validateActivity() === true) {
                    if ($this->saveActivity() === true) {
                        header("Location: /activities/events");
                    }
                } else {
                    $this->get('new', $id);
                }
                break;
            case 'update' :
                if ($this->validateActivity() === true) {
                    if ($this->updateActivity($id) === true) {
                        header("Location: /activities/events");
                    }
                } else {
                    $this->get('update', $id);
                }
                break;
            case 'delete' :
                if (isset($env->post('activity')['submit'])) {
                    if ($env->post('activity')['submit'] === 'delete') {
                        if ($this->deleteActivity($id) === true) {
                            header("Location: /activities/events");
                        }
                    }
                    if ($env->post('activity')['submit'] === 'cancel') {
                        header("Location: /activities/events");
                    }
                }
                break;
        }
    }

    function getActivity($id) {
        $db = db::getInstance();
        $sql = "SELECT activity_events.title AS title, activity_events.description AS description, activity_events.date AS date, activity_events.time AS time, activity_events.comments_activated AS comments_activated, activities.userid AS userid
                    FROM activity_events
                    INNER JOIN activities
                    ON activities.id = activity_events.activity_id
                    WHERE activity_events.activity_id = '$id'
                    LIMIT 1;";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            $activity = $query->fetch_object();
            return $activity;
        }
        return false;
    }

    function getNewActivityForm() {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $comments_checked = (!empty($env->post('activity')['comments']) AND $env->post('activity')['comments'] !== NULL) ? 'checked="checked"' : '';
        $signups_checked = (!empty($env->post('activity')['signups']) AND $env->post('activity')['signups'] !== NULL) ? 'checked="checked"' : '';

        $view = new View();
        $view->setTmpl(file('views/activity/new_activity_event_form.php'), array(
            '{##form_action##}' => '/activity/event/new',
            '{##activity_title##}' => $env->post('activity')['title'],
            '{##activity_title_validation##}' => $msg->fetch('activity_event_title_validation'),
            '{##activity_content##}' => $env->post('activity')['content'],
            '{##activity_content_validation##}' => $msg->fetch('activity_event_content_validation'),
            '{##activity_date##}' => $env->post('activity')['date'],
            '{##activity_date_validation##}' => $msg->fetch('activity_event_date_validation'),
            '{##activity_time##}' => $env->post('activity')['time'],
            '{##activity_time_validation##}' => $msg->fetch('activity_event_time_validation'),
            '{##activity_comments_checked##}' => $comments_checked,
            '{##activity_signups_checked##}' => $signups_checked,
            '{##preview_text##}' => 'Preview',
            '{##draft_text##}' => 'Save as draft',
            '{##submit_text##}' => 'Submit',
        ));
        $view->replaceTags();
        return $view;
    }

    function getUpdateActivityForm($id) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $act = $this->getActivity($id);

        $title = (!empty($env->post('activity')['title'])) ? $env->post('activity')['title'] : $act->title;
        $content = (!empty($env->post('activity')['content'])) ? $env->post('activity')['content'] : $act->description;
        $date = (!empty($env->post('activity')['date'])) ? $env->post('activity')['date'] : $act->date;
        $time = (!empty($env->post('activity')['time'])) ? $env->post('activity')['time'] : $act->time;

        $comments_checked = (is_null($env->post('activity')['comments'])) ? $act->comments_activated : $env->post('activity')['comments'];
        $comments_checked = ($comments_checked === '1') ? 'checked="' . $comments_checked . '"' : '';

        $signups_checked = (is_null($env->post('activity')['comments'])) ? $act->signups_activated : $env->post('activity')['signups'];
        $signups_checked = ($signups_checked === '1') ? 'checked="' . $signups_checked . '"' : '';

        $view = new View();
        $view->setTmpl(file('views/activity/update_activity_event_form.php'), array(
            '{##form_action##}' => '/activity/event/update/' . $id,
            '{##activity_title##}' => $title,
            '{##activity_title_validation##}' => $msg->fetch('activity_event_title_validation'),
            '{##activity_content##}' => $content,
            '{##activity_content_validation##}' => $msg->fetch('activity_event_content_validation'),
            '{##activity_date##}' => $date,
            '{##activity_date_validation##}' => $msg->fetch('activity_event_date_validation'),
            '{##activity_time##}' => $time,
            '{##activity_time_validation##}' => $msg->fetch('activity_event_time_validation'),
            '{##activity_comments_checked##}' => $comments_checked,
            '{##preview_text##}' => 'Preview',
            '{##draft_text##}' => 'Save as draft',
            '{##submit_text##}' => 'Submit',
        ));
        $view->replaceTags();
        return $view;
    }

    function getDeleteActivityForm($id) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $act = $this->getActivity($id);
        $content = $act->title . "<br />" . $act->description;
        
        $view = new View();
        $view->setTmpl(file('views/activity/delete_activity_form.php'), array(
            '{##form_action##}' => '/activity/event/delete/' . $id,
            '{##activity_content##}' => $content,
            '{##submit_text##}' => "delete",
            '{##cancel_text##}' => "cancel",
        ));
        $view->replaceTags();
        return $view;
    }

    function validateActivity() {
        $msg = Msg::getInstance();
        $env = Env::getInstance();

        $errors = false;
        if (empty($env->post('activity')['title'])) {
            $msg->add('activity_event_title_validation', 'Gotta have a name for this baby!');
            $errors = true;
        }
        if (empty($env->post('activity')['content'])) {
            $msg->add('activity_event_content_validation', 'What is this about?');
            $errors = true;
        }
        if (empty($env->post('activity')['date'])) {
            $msg->add('activity_event_date_validation', 'When?');
            $errors = true;
        }
        if (empty($env->post('activity')['time'])) {
            $msg->add('activity_event_time_validation', 'When exactly?');
            $errors = true;
        }

        if ($errors === false) {
            return true;
        }
        return false;
    }

    function saveActivity() {
        $db = db::getInstance();
        $env = Env::getInstance();

        // save activity meta data
        $this->save($type = 2); // 2=event
        // save 'event' specific data
        $activity_id = $db->insert_id;
        $event_type = $env->post('activity')['event_type'];
        $title = $env->post('activity')['title'];
        $description = $env->post('activity')['content'];
        $time = $env->post('activity')['time'];
        $date = $env->post('activity')['date'];
        $allow_comments = $env->post('activity')['comments'];
        $allow_signups = $env->post('activity')['signups'];

        $sql = "INSERT INTO activity_events (activity_id, event_type, title, description, date, time, calendar_activated, schedule_activated, comments_activated, signups_activated, template_activated) VALUES ('$activity_id', '$event_type', '$title', '$description', '$date', '$time', '0', '0', '$allow_comments', '$allow_signups', '0');";

        $query = $db->query($sql);
        if ($query !== false) {
            $env->clear_post('activity');
            return true;
        }
        return false;
    }

    function updateActivity($id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        $login = new Login();

        $userid = $login->currentUserID();
        $actid = $this->getActivity($id)->userid;
        if ($userid != $actid) {
            return false;
        }
        
        $title = $env->post('activity')['title'];
        $event_type = $env->post('activity')['event_type'];
        $description = $env->post('activity')['content'];
        $time = $env->post('activity')['time'];
        $date = $env->post('activity')['date'];
        $allow_comments = isset($env->post('activity')['comments']) ? '1' : '0';
        
        $sql = "UPDATE activity_events SET
                        title = '$title',
                        event_type = '$event_type',
                        description = '$description',
                        time = '$time',
                        date = '$date',
                        comments_activated = '$allow_comments'
                    WHERE activity_id = '$id';";

        $query = $db->query($sql);
        if ($query !== false) {
            $env->clear_post('activity');
            return true;
        }
        return false;
    }
    
    function deleteActivity($id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        $login = new Login();

        $userid = $login->currentUserID();
        $actid = $this->getActivity($id)->userid;
        if ($userid != $actid) {
            return false;
        }
        //$sql = "DELETE FROM activity_events WHERE activity_id = '$id';";
        //$query = $db->query($sql);
        $sql = "DELETE FROM activities 
                    WHERE id = '$id';";
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clear_post('activity');
            return true;
        }
        return false;
    }
}

$activity_event = new Activity_Event();
$activity_event->initEnv();
