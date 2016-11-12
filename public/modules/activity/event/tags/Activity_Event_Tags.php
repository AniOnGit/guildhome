<?php

class Activity_Event_Tags {
    // start controller
    function initEnv() {
        Toro::addRoute(["/activity/event/tags/:alpha/:number" => "Activity_Event_Tags"]);
        
        Env::registerHook('activity_event_form_hook', array(new Activity_Event_Tags(), 'getTagsFormView'));
        Env::registerHook('activity_event_view_hook', array(new Activity_Event_Tags(), 'activityEventTagsViewHook'));
    }

    function post($alpha, $id = NULL) {
        $env = Env::getInstance();
        $login = new Login();
        if (!$login->isLoggedIn()) {
            return false;
        }
        switch ($alpha) {
            case 'update' :
                if (isset($env->post('activity')['submit']['tags'])) {
                    $this->saveTags($id);
                    header("Location: /activity/event/update/" . $id);
                    exit;
                }
                break;
        }
    }

    function getActivityTagsView($event_id) {
        $activity_event = new Activity_Event();
        $act = $activity_event->getActivityByID($event_id);

        $tags_checked = $act->tags_activated;
        if ($tags_checked == '1') {
            $view = new View();
            $view->setTmpl($view->loadFile('/views/activity/event/tags/activity_event_tags_view.php'), array(
                '{##tags##}' => $tags_checked,
            ));

            $view->replaceTags();
            return $view;
        }
        return false;
    }
    
    function getTagsFormView($id, $target_url = '') {
        $msg = Msg::getInstance();
        $env = Env::getInstance();
        
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/tags/activity_event_tags_form.php'));
        
        $view->addContent('{##form_action##}', '/activity/event/tags/update/' . $id);
        $activity_event = new Activity_Event();
        $act = $activity_event->getActivityByID($id);
        $signups_checked = (!empty($env->post('activity')['tags'])) ? $env->post('activity')['tags'] : $act->tags_activated;

        $view->addContent('{##target_url##}',$target_url);
        $view->addContent('{##activity_tags_checked##}', ($signups_checked === '1') ? 'checked="checked"' : '');
        $view->addContent('{##submit_text##}', 'Update');
        $view->replaceTags();
        
        return $view;        
    }

    function saveTags($activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();

        $allow_tags = isset($env->post('activity')['tags']) ? '1' : '0';

        $sql = "UPDATE activity_events SET
                tags_activated = '$allow_tags'
            WHERE activity_id = '$activity_id';";
        $query = $db->query($sql);

        if ($db->query($sql)) {
            $msg = Msg::getInstance();
            $msg->add('activity_event_content_saved', 'Tags updated!');
            return true;
        }
        return false;
    }

    function activityEventTagsViewHook(&$loopView, $act, $event_id, $compact = false) {
        $tags = '';
        if ($act->tags_activated == '1') {
            $tags = "Tags active";
        }
        if (is_null($compact)) {
            $loopView->addContent('{##activity_tags##}', $tags);
        }
    }
}
$init_env = new Activity_Event_Tags();
$init_env->initEnv();
unset($init_env);