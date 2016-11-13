<?php

class Activity_Shout extends Activity implements IDatabaseModel {
    // start controller
    function initEnv() {
        Toro::addRoute(["/activity/shout/:alpha" => "Activity_Shout"]);
        Toro::addRoute(["/activity/shout/:alpha/:number" => "Activity_Shout"]);
        
        Env::registerHook('shout', array(new Activity_Shout(),'getActivityView'));
    }

    function get($alpha = '', $id = NULL) {
        $env = Env::getInstance();
        $login = new Login();
        $page = Page::getInstance();
        $menu = new Menu();
        $page->addContent('{##main##}', $menu->activityMenu('shout', $compact = true));
        switch ($alpha) {
            case 'new':
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page->addContent('{##main##}', '<h2>New shout</h2>');
                $page->addContent('{##main##}', $this->getActivityForm());
                if (isset($env->post('activity')['preview'])) {
                    $page->addContent('{##main##}', $this->getActivityPreview());
                }
                break;
            case 'update':
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page->addContent('{##main##}', '<h2>Update shout</h2>');
                $page->addContent('{##main##}', $this->getActivityForm($id));
                if (isset($env->post('activity')['preview'])) {
                    $page->addContent('{##main##}', $this->getActivityPreview());
                }
                break;
            case 'delete':
                if (!$login->isLoggedIn()) {
                    return false;
                }
                $page->addContent('{##main##}', '<h2>Delete shout</h2>');
                $page->addContent('{##main##}', $this->getDeleteActivityForm($id));
                break;
        }
    }

    function post($alpha, $shout_id = NULL) {
        $env = Env::getInstance();
        $login = new Login();
        if (!$login->isLoggedIn()) {
            return false;
        }
        switch ($alpha) {
            case 'new':
                if (isset($env->post('activity')['preview'])) {
                    unset($env->post('activity')['preview']);
                } elseif (($shout_id = $this->saveActivity('shout')) !== false) {
                    $this->get('update', $shout_id);
                    break;
                }
                $this->get('new', $shout_id);
                break;
            case 'update':
                if (isset($env->post('activity')['preview'])) {
                    unset($env->post('activity')['preview']);
                } else {
                    $this->updateActivity($shout_id);
                }
                $this->get('update', $shout_id);
                break;
            case 'delete':
                if (isset($env->post('activity')['submit'])) {
                    if ($env->post('activity')['submit'] === 'delete') {
                        if ($this->deleteActivity($shout_id) === true) {
                            header("Location: /activities/shouts");
                        }
                    }
                    if ($env->post('activity')['submit'] === 'cancel') {
                        header("Location: /activities/shouts");
                    }
                }
                break;
        }
    }
    // end controller
    // start model
    function getActivityById($id) {
        $db = db::getInstance();
        $sql = "SELECT a.comments_enabled AS comments_enabled, ash.content AS content, a.userid AS userid
                    FROM activity_shouts ash
                    INNER JOIN activities a ON a.id = ash.activity_id
                    WHERE ash.activity_id = '$id'
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

    function saveActivityTypeDetails($activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        
        // save activity meta data
        $content = $env->post('activity')['content'];
        
        $sql = "INSERT INTO activity_shouts (activity_id, content) VALUES ('$activity_id', '$content');";
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clearPost('activity');
            $msg = Msg::getInstance();
            $msg->add('activity_shout_content_saved', 'Activity saved!');
            return $activity_id;
        }
        return false;
    }

    function updateActivityTypeDetails($shout_id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        $login = new Login();
        
        $userid = $login->currentUserID();
        $act = $this->getActivityById($shout_id);
        if ($userid != $act->userid) {
            return false;
        }
        
        $content = $env->post('activity')['content'];
        $allow_comments = isset($env->post('activity')['comments']) ? '1' : '0';
        $sql = "UPDATE activities SET
                            comments_enabled= '$allow_comments'
                        WHERE id = '$shout_id';";
        $query = $db->query($sql);
        
        $sql = "UPDATE activity_shouts SET
                        content = '$content'
                    WHERE activity_id = '$shout_id';";
        
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clearPost('activity');
            $msg = Msg::getInstance();
            $msg->add('activity_shout_content_saved', 'Activity updated!');
            return $shout_id;
        }
        return false;
    }

    public function createDatabaseTables($overwriteIfExists) {
        $db = db::getInstance();
        
        if ($overwriteIfExists) {
            $sqlDropExistingPollTables = "DROP TABLE IF EXISTS activity_shouts";
            $db->query($overwriteIfExists);
        }
        
        $sqlShoutsTable = "CREATE TABLE `activity_shouts` (`content` text,`activity_id` int(6) DEFAULT NULL,`comments_activated` tinyint(4) NOT NULL,
				UNIQUE KEY `activity_id` (`activity_id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        $db->query($sqlShoutsTable);
    }
    // end model
    // start view
    function getActivityPreview() {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/shout/activity_shout_view.php'));
        $view->setContent('{##activity_message##}', '<p>This is how your Shout will look:</p>');
        
        $subView = new View();
        $subView->setTmpl($view->getSubTemplate('{##activity_loop##}'));
        $subView->addContent('{##activity_published##}', date('Y-m-d H:i:s'));
        $subView->addContent('{##activity_type##}', '<strong>a shout</strong>');
        
        $env = Env::getInstance();
        $content = Parsedown::instance()->text($env->post('activity')['content']);
        $subView->addContent('{##css##}', ' preview');
        $subView->addContent('{##activity_content##}', $content);
        $login = new Login();
        $identity = new Identity();
        $subView->addContent('{##activity_identity##}', $identity->getIdentityById($login->currentUserID(), 0));
        $subView->addContent('{##avatar##}', $identity->getAvatarByUserId($login->currentUserID()));
        $subView->replaceTags();
        
        $view->addContent('{##activity_loop##}', $subView);
        $view->replaceTags();
        
        return $view;
    }

    public function getActivityView($activity_id = NULL, $compact = NULL) {
        $act = parent::getActivityMetaById($activity_id);
        
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/shout/activity_shout_view.php'));
        
        $subView = new View();
        $subView->setTmpl($view->getSubTemplate('{##activity_loop##}'));
        if (isset($act->create_time)) {
            $subView->addContent('{##activity_published##}', $act->create_time);
        }
        if (isset($act->type_description)) {
            $subView->addContent('{##activity_type##}', $act->type_description);
        }
        if ($act->deleted == '1') {
            $subView->addContent('{##css##}', ' deleted');
        }
        
        $activity_event = $this->getActivityById($act->id);
        $content = Parsedown::instance()->text($activity_event->content);
        
        $delete_link = '/activity/shout/delete/' . $act->id;
        $update_link = '/activity/shout/update/' . $act->id;
        $comment_link = '/comment/activity/view/' . $act->id;
        
        $subView->addContent('{##activity_content##}', $content);
        
        if (isset($act->userid)) {
            $identity = new Identity();
            $subView->addContent('{##activity_identity##}', $identity->getIdentityById($act->userid, 0));
            $subView->addContent('{##avatar##}', $identity->getAvatarByUserId($act->userid));
        }
        
        if (isset($activity_event->comments_enabled) AND $activity_event->comments_enabled == '1') {
            $comment = new Activity_Comment();
            $comment_count = $comment->getCommentCount($act->id);
            
            $visitorView = new View();
            $visitorView->setTmpl($view->getSubTemplate('{##activity_not_logged_in##}'));
            $visitorView->addContent('{##comment_link##}', View::linkFab($comment_link, "comments ($comment_count)"));
            $visitorView->replaceTags();
            $subView->addContent('{##activity_not_logged_in##}', $visitorView);
        }
        
        $login = new Login();
        if ($login->isLoggedIn() AND isset($act->userid) AND $login->currentUserID() === $act->userid) {
            $memberView = new View();
            $memberView->setTmpl($view->getSubTemplate('{##activity_logged_in##}'));
            $memberView->addContent('{##delete_link##}', View::linkFab($delete_link, 'delete'));
            $memberView->addContent('{##edit_link##}', View::linkFab($update_link, 'update'));
            $memberView->replaceTags();
            $subView->addContent('{##activity_logged_in##}', $memberView);
        }
        $subView->replaceTags();
        
        $view->addContent('{##activity_loop##}', $subView);
        $view->replaceTags();
        
        return $view;
    }

    function getActivityForm($id = NULL) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();
        
        if ($id === NULL) {
            if ($env->post('activity') === FALSE) { // check comments by default
                $comments_checked = 'checked="checked"';
            } else {
                if (!empty($env->post('activity')['comments']) AND is_string($env->post('activity')['comments']) === TRUE) {
                    $comments_checked = 'checked="checked"';
                } else {
                    $comments_checked = '';
                }
            }
            
            $content = $env->post('activity')['content'];
            $content = str_replace("\n\r", "&#13;", $content);
            
            $view = new View();
            $view->setTmpl($view->loadFile('/views/activity/shout/activity_shout_form.php'), array('{##form_action##}' => '/activity/shout/new','{##activity_content##}' => $content,'{##activity_content_validation##}' => $msg->fetch('activity_shout_content_validation'),'{##activity_shout_content_saved##}' => $msg->fetch('activity_shout_content_saved', 'success'),'{##activity_comments_checked##}' => $comments_checked,'{##preview_text##}' => 'Preview','{##submit_text##}' => 'Say it loud'));
        } else {
            $act = $this->getActivityById($id);
            $content = (isset($env->post('activity')['content'])) ? $env->post('activity')['content'] : $act->content;
            $content = str_replace("\n\r", "&#13;", $content);
            
            $comments_checked = (isset($env->post('activity')['comments'])) ? $env->post('activity')['comments'] : $act->comments_enabled;
            $comments_checked = ($comments_checked == '1') ? 'checked="' . $comments_checked . '"' : '';
            
            $view = new View();
            $view->setTmpl($view->loadFile('/views/activity/shout/activity_shout_form.php'), array('{##form_action##}' => '/activity/shout/update/' . $id,'{##activity_content##}' => $content,'{##activity_content_validation##}' => $msg->fetch('activity_shout_content_validation'),'{##activity_shout_content_saved##}' => $msg->fetch('activity_shout_content_saved', 'success'),'{##activity_comments_checked##}' => $comments_checked,'{##preview_text##}' => 'Preview','{##draft_text##}' => 'Save as draft','{##submit_text##}' => "i'm sure now!"));
        }
        $view->replaceTags();
        return $view;
    }

    function getDeleteActivityForm($id = NULL) {
        if ($id !== NULL) {
            $act = $this->getActivityById($id);
            $content = $act->content;
        } else {
            $content = '';
        }
        
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/delete_activity_form.php'), array('{##form_action##}' => '/activity/shout/delete/' . $id,'{##activity_content##}' => $content,'{##submit_text##}' => "delete",'{##cancel_text##}' => "cancel"));
        $view->replaceTags();
        return $view;
    }

    function validateActivityTypeDetails() {
        $msg = Msg::getInstance();
        $env = Env::getInstance();
        
        $errors = false;
        if (empty($env->post('activity')['content'])) {
            $msg->add('activity_shout_content_validation', 'Say something!! Please :)');
            $errors = true;
        }
        
        if ($errors === false) {
            return true;
        }
        return false;
    }

    function createActivityTypeDatabaseTables($overwriteIfExists = false) {
        return true;
    }
    // end view
}
$init_env = new Activity_Shout();
$init_env->initEnv();
unset($init_env);