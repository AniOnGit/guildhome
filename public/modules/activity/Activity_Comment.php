<?php

class Activity_Comment {
    // start controller
    function initEnv() {
        Toro::addRoute(["/comment/:alpha/:alpha" => "Activity_Comment"]);
        Toro::addRoute(["/comment/:alpha/:alpha/:alpha" => "Activity_Comment"]);
    }
    
    function get($type = 'activity', $action = 'view', $activity_id = '') {       
        $env = Env::getInstance();
        $login = new Login();

        $act = $this;
        $page = Page::getInstance();
        $menu = new Menu();
        $page->setContent('{##main##}', $menu->activityMenu($type));
        switch ($type) {
            case 'activity' :
                switch ($action) {
                    default:
                        $page->addContent('{##main##}', '<h2>Comments</h2>');
                        $act = Activity::getActivityMetaById($activity_id);
                        $hooks = $env::getHooks($act->type_name);
                        if ($hooks!== false) {
                            foreach ($hooks as $hook) {
                                $activity_view = $hook[$act->type_name]($act->id, false);
                            }
                        }
                        
                        $page->addContent('{##main##}', $activity_view);
                        if ($login->isLoggedIn() AND Activity::commentsEnabled($activity_id)) {
                            $page->addContent('{##main##}', $this->getNewCommentForm($activity_id));
                        }
                        if (Activity::commentsEnabled($activity_id)) {
                            $page->addContent('{##main##}', $this->getAllCommentsView($activity_id));
                        }
                        break;
                    case 'update' : /* $activity_id this is now the comment_id, NOT the activity_id, hence the call to getParent */
                        $page->addContent('{##main##}', '<h2>Update comment</h2>');
                        $parent = $this->getParent($activity_id);
                        if ($login->isLoggedIn() AND Activity::commentsEnabled($parent['id'])) {
                            $page->addContent('{##main##}', $this->getEditCommentForm($activity_id));
                        }
                        break;
                    case 'delete' :
                        $page->addContent('{##main##}', '<h2>Delete comment</h2>');
                        $parent = $this->getParent($activity_id);
                        if ($login->isLoggedIn() AND Activity::commentsEnabled($parent['id'])) {
                            $page->addContent('{##main##}', $this->getDeleteCommentForm($activity_id));
                        }
                        break;
                }
                break;
        }
    }
    
    function post($alpha, $action = 'new', $id = NULL) {
        $env = Env::getInstance();
        $login = new Login();
        if (!$login->isLoggedIn()) {
            return false;
        }
        
        switch ($action) {
            case 'new' :
                if ($this->validateComment() === true) {
                    if ($this->saveComment($id) === true) {
                        header("Location: /comment/$alpha/view/" . $id);
                    }
                } else {
                    $this->get('activity', 'view', $id);
                }
                break;
            case 'update' :
                if ($this->validateComment() === true) {
                    if ($this->updateComment($id) === true) {
                        $am = $this->getParent($id);
                        header("Location: /comment/$alpha/view/" . $am['id']);
                    }
                } else {
                    $this->get('activity', 'view', $id);
                }
                break;
            case 'delete' :
                if (isset($env->post('comment')['submit'])) {
                    if ($env->post('comment')['submit'] === 'delete') {
                        $am = $this->getParent($id);
                        if ($this->deleteComment($id) === true) {
                            header("Location: /comment/$alpha/view/" . $am['id']);
                        }
                    }
                    if ($env->post('activity')['submit'] === 'cancel') {
                        $this->get('activity', 'view', $id);
                    }
                }
                break;
        }
        
    }
    // end controller    
    // start model
    function getParent($comment_id) {
        $db = db::getInstance();
        $sql = "SELECT activity_id FROM comment_mapping WHERE comment_id = $comment_id;";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows == 1) {
            $activity_id = $query->fetch_object()->activity_id;
        }
        $sql = "SELECT type FROM activities WHERE id = $activity_id;";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows == 1) {
            $activity_type = $query->fetch_object()->type;
        }
        $sql = "SELECT name FROM activity_types WHERE id = $activity_type;";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows == 1) {
            $activity_type_name = $query->fetch_object()->name;
        }
        
        $activity_meta = [
            'id' => $activity_id,
            'type' => $activity_type,
            'type_name' => $activity_type_name,
            ];
        
        return $activity_meta;
    }
    
    function save() {
        $db = db::getInstance();
        $env = Env::getInstance();
        $login = new Login();
        
        $userid = $login->currentUserID();
        $parent_comment = NULL;
        $uxtime = time();
        $content = $env->post('comment')['content'];
        
        $sql = "INSERT INTO comments (content, userid, create_time, parent_comment) VALUES ('$content', '$userid', '$uxtime', '$parent_comment');";
        $db->query($sql);
    }
    
    function update($id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        $login = new Login();
        
        $userid = $login->currentUserID();

        $content = $env->post('comment')['content'];
        
        $sql = "UPDATE comments SET
                    content = '$content'
                    WHERE id = '$id' AND userid = '$userid';";
        
        $query = $db->query($sql);
        
        if ($query !== false) {
            $env->clearPost('comment');
            return true;
        }
        return false;
    }
        
    function validateComment() {
        $msg = Msg::getInstance();
        $env = Env::getInstance();
       
        $errors = false;
        if (empty($env->post('comment')['content'])) {
            $msg->add('comment_content_validation', 'What, nothing? Then whats the point? Trying to confuse me? You will Fail!');
            $errors = true;
        }

        if ($errors === false) {
            return true;
        }
        return false;
    }

    function updateComment($activity_id) {
        // save activity meta data
        if ($this->update($activity_id) === true) {
            return true;
        }
        return false;
    }
    
    function saveComment($activity_id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        
        // save activity meta data
        $this->save();

        // save 'shout' specific data
        $comment_id = $db->insert_id;

        $sql = "INSERT INTO comment_mapping (activity_id, comment_id) VALUES ('$activity_id', '$comment_id');";
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clearPost('comment');
            $hooks = $env::getHooks('save_comment_hook');
            if ($hooks!== false) {
                foreach ($hooks as $hook) {
                    $hook['save_comment_hook']($activity_id);
                }
            }
            return true;
        }
        return false;
    }

    function deleteComment($id) {
        $db = db::getInstance();
        $env = Env::getInstance();
        $login = new Login();

        $userid = $login->currentUserID();
        $cmtid = $this->getComment($id)->userid;
        if ($userid != $cmtid) {
            return false;
        }
        $sql = "DELETE FROM comments WHERE id = '$id';";
        $query = $db->query($sql);
        $sql = "DELETE FROM comment_mapping WHERE comment_id = '$id';";
        $query = $db->query($sql);
        if ($query !== false) {
            $env->clearPost('comment');
            return true;
        }
        return false;
    }
    
    function getComments($activity_id) {
        $db = db::getInstance();
        $sql = "SELECT comment_mapping.activity_id AS activity_id, comment_mapping.comment_id AS comment_id, comments.userid AS userid, from_unixtime(comments.create_time) AS create_time, comments.content AS content
                    FROM comments
                    INNER JOIN comment_mapping
                    ON comments.id = comment_id
                    WHERE activity_id = $activity_id
                    ORDER BY comments.create_time ASC;";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $activities[] = $result_row;
            }
            return $activities;
        }
        return false;
    }

    function getComment($comment_id) {
        $db = db::getInstance();
        $sql = "SELECT comment_mapping.activity_id AS activity_id, comment_mapping.comment_id AS comment_id, comments.userid AS userid, from_unixtime(comments.create_time) AS create_time, comments.content AS content
                    FROM comments
                    INNER JOIN comment_mapping
                    ON comments.id = comment_id
                    WHERE comment_id = $comment_id
                    LIMIT 1;";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows == 1) {
            return $query->fetch_object();
        }
        return false;
    }
    
    function getCommentCount($activity_id) {
        $db = db::getInstance();
        $sql = "SELECT *
                    FROM comment_mapping
                    WHERE activity_id = $activity_id;";
        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            return $query->num_rows;
        }
        return '0';

    }
    // end model
    // start view
    function getDeleteCommentForm($id) {
        $cmt = $this->getComment($id);
        $content = $cmt->content;
        
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/comment/delete_comment_form.php'), array(
            '{##form_action##}' => '/comment/activity/delete/' . $id,
            '{##comment_content##}' => $content,
            '{##submit_text##}' => "delete",
            '{##cancel_text##}' => "cancel",
        ));
        $view->replaceTags();
        return $view;
    }
    
    /*
     * Being a patchwork funtion at the moment, a lot of stuff is Jerry-Rigged here
     * A lot of stuff has to be worked out to be viable for public release
     * Whats clearly missing:
     *      - automatic detection of activity type and corresponding class
     */
    function getAllCommentsView($activity_id) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/comment/list_all_comments.php'));
        $comments = $this->getComments($activity_id);
        if (false !== $comments) {
            $comment_loop = NULL;
            foreach ($comments as $act) {
                $subView = new View();
                $subView->setTmpl($view->getSubTemplate('{##comment_loop##}'));
                $subView->addContent('{##activity_type##}', 'a comment');
                $subView->addContent('{##comment_published##}', $act->create_time);
                $content = Parsedown::instance()->text($act->content);
                $subView->addContent('{##comment_content##}',  $content);

                $identity = new Identity();
                $subView->addContent('{##comment_identity##}', $identity->getIdentityById($act->userid, 0));
                $subView->addContent('{##avatar##}', $identity->getAvatarByUserId($act->userid));
                
                $login = new Login();
                if ($login->isLoggedIn() AND $login->currentUserID() === $act->userid) {
                    $memberView = new View();
                    $memberView->setTmpl($view->getSubTemplate('{##comment_logged_in##}'));
                    $memberView->addContent('{##edit_link##}', View::linkFab('/comment/activity/update/' . $act->comment_id, 'edit'));
                    $memberView->addContent('{##delete_link##}', View::linkFab('/comment/activity/delete/' . $act->comment_id, 'delete'));
                    $memberView->replaceTags();
                    $subView->addContent('{##comment_logged_in##}',  $memberView);
                }
                $subView->replaceTags();
                $comment_loop .= $subView;
            }
            $view->addContent('{##comment_loop##}',  $comment_loop);
        }
        $view->replaceTags();
        return $view;
    }
    
    function getNewCommentForm($activity_id) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $activity = $this;
        $act = Activity::getActivityMetaById($activity_id);
        $content = (!empty($env->post('comment')['content'])) ? $env->post('comment')['content'] : '';
        $content = str_replace("\n\r", "&#13;", $content);

        $view = new View();
        if ($act->deleted != '1' AND $act->deleted != '1') {
            $view->setTmpl($view->loadFile('/views/activity/comment/comment_form.php'), array(
                '{##form_action##}' => '/comment/activity/new/' . $activity_id,
                '{##comment_content##}' => $content,
                '{##comment_content_validation##}' => $msg->fetch('comment_content_validation'),
                '{##comment_message##}' => "what's on your mind?",
                '{##submit_text##}' => 'Say it loud',
            ));
        } else {
            $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
            $view->setContent('{##data##}', 'No commenting on deleted content :)');
        }
        $view->replaceTags();
        return $view;
    }

    function getEditCommentForm($activity_id) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();
        
        $comment =  $this->getComment($activity_id);
        $activity = $this;
        $act = Activity::getActivityMetaById($comment->activity_id);
        $content = (!empty($env->post('comment')['content'])) ? $env->post('comment')['content'] : $comment->content;
        $content = str_replace("\n\r", "&#13;", $content);

        $view = new View();
        if ($act->deleted != '1' AND $act->deleted != '1') {
            $view->setTmpl($view->loadFile('/views/activity/comment/comment_form.php'), array(
                '{##form_action##}' => '/comment/activity/update/' . $activity_id,
                '{##comment_content##}' => $content,
                '{##comment_content_validation##}' => $msg->fetch('comment_content_validation'),
                '{##comment_message##}' => "make sure you get it right this time",
                '{##submit_text##}' => 'Revised and ready!',
            ));
        } else {
            $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
            $view->setContent('{##data##}', 'No commenting-editing on deleted content :)');
        }
        $view->replaceTags();
        return $view;
    }
    // end view
}
$comment = new Activity_Comment();
$comment->initEnv();
unset($comment);