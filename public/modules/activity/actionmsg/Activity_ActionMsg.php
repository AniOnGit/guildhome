<?php

class Activity_ActionMsg extends Activity {

    function initEnv() {
        // hooks for the various places where messages can be implemented
        Env::registerHook('save_comment_hook', array(new Activity_ActionMsg(), 'saveCommentAction'));
        Env::registerHook('save_new_user_hook', array(new Activity_ActionMsg(), 'saveNewUserAction'));
        Env::registerHook('toggle_event_signup_hook', array(new Activity_ActionMsg(), 'toggleEventSignupAction'));
        Env::registerHook('delete_activity_hook', array(new Activity_ActionMsg(), 'deleteEventAction'));
        
        // hook for the activity module
        Env::registerHook('actionmessage', array(new Activity_ActionMsg(), 'getActivityView'));
    }
    
    function getActivityById($id = NULL) {
        $db = db::getInstance();
        $sql = "SELECT activity_actionmsg.*, from_unixtime(activities.create_time, '%Y-%m-%d') AS create_date,
                    from_unixtime(activities.create_time, '%H:%i') AS create_time
                    FROM activity_actionmsg
                    INNER JOIN activities
                        ON activities.id = activity_actionmsg.activity_id
                    WHERE activity_actionmsg.activity_id = $id
                    LIMIT 1;";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $activity = $result_row;
            }
            return $activity;
        }
        return false;
        
    }
    
    public function getActivityView($id = NULL, $compact = NULL) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/actionmsg/activity_actionmsg_view.php'));

        $actionmsg = $this->getActivityById($id);

        $message = 'at ' . $actionmsg->create_time . ', ';
        $message .= $actionmsg->message;
        if (isset($actionmsg->related_activity_id)) {
            $message .= ' (<a href="/comment/activity/view/'.$actionmsg->related_activity_id.'">view</a>)';
        }
        $view->setContent('{##action_message##}', $message);
        $view->replaceTags();

        return $view;    
    }
    private $message = '';
    function setMessage($message) {
        $this->message = $message;
        return $this;
    }
    function getMessage() {
        return $this->message;
    }
    private $related_activity = null;
    function setRelatedActivityID($activity_id) {
        $this->related_activity = $activity_id;
        return $this;
    }
    function getRelatedActivityID() {
        return $this->related_activity;
    }

    function saveCommentAction($activity_id = NULL) {
        $login = new Login();
        $user_id = $login->currentUserID();
        $identity = new Identity();
        $profile = new Profile();

        $activity = parent::getActivityMetaById($activity_id);

        $message  = '<a href="' . $profile->getProfileUrlById($user_id) . '">' . $identity->getIdentityById($user_id) . '</a>';
        $message .= ' commented on ' . $activity->type_description;
        $this->setMessage($message)->setRelatedActivityID($activity_id)->saveActivity('actionmessage'); // save metadata as action messages are activities
    }

    function deleteEventAction($activity_id = NULL) {
        $login = new Login();
        $user_id = $login->currentUserID();
        $identity = new Identity();
        $profile = new Profile();

        $activity = parent::getActivityMetaById($activity_id);

        $message  = '<a href="' . $profile->getProfileUrlById($user_id) . '">' . $identity->getIdentityById($user_id) . '</a>';
        $message .= ' deleted ' . $activity->type_description;
    
        $this->setMessage($message)->setRelatedActivityID($activity_id)->saveActivity('actionmessage'); // save metadata as action messages are activities
    }

    function toggleEventSignupAction($activity_id = NULL, $signup = FALSE) {
        $login = new Login();
        $user_id = $login->currentUserID();
        $identity = new Identity();
        $profile = new Profile();

        $activity = parent::getActivityMetaById($activity_id);

        $message  = '<a href="' . $profile->getProfileUrlById($user_id) . '">' . $identity->getIdentityById($user_id) . '</a>';
        if ($signup === TRUE) {
            $message .= ' signed up for ' . $activity->type_description;
        } else {
            $message .= ' signed out from ' . $activity->type_description;
        }
        $this->setMessage($message)->setRelatedActivityID($activity_id)->saveActivity('actionmessage'); // save metadata as action messages are activities
    }

    function saveNewUserAction($user_id = NULL) {
        $identity = new Identity();
        $profile = new Profile();
        
        $message = '<a href="' . $profile->getProfileUrlById($user_id) . '">' . $identity->getIdentityById($user_id, 0) . '</a>' . ' created an account';
        $this->setMessage($message)->saveActivity('actionmessage'); // save metadata as action messages are activities
    }
    
    function saveActivityTypeDetails($activity_id) {
        $db = db::getInstance();
        $related_activity_id = $this->getRelatedActivityID();
        $message = $this->getMessage();
        $sql = "INSERT INTO activity_actionmsg (activity_id, message, related_activity_id) VALUES ($activity_id, '$message', $related_activity_id);";
        $db->query($sql);        

        return $this;
    }
    
    function updateActivityTypeDetails($activity_id) {
        return $this;
    }

    function validateActivityTypeDetails() {
        return true;
    }

    function createActivityTypeDatabaseTables($overwriteIfExists = false) {
        return true;
    }
}
$init_env = new Activity_ActionMsg();
$init_env->initEnv();
unset($init_env);
