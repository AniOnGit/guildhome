<?php

class Profile {
    
    function initEnv() {
        Toro::addRoute(["/profiles" => 'Profile']);
        Toro::addRoute(["/profile/:alpha" => 'Profile']);
    }
    
    function get($user_name = NULL) {
        $page = Page::getInstance();
        $login = new Login();
        $user_id = $this->getUserIDByUsername($user_name);

        if (is_null($user_id)) {
            $page->setContent('{##main##}', '<h2>Registered Members</h2>');
            $page->addContent('{##main##}', $this->getUsersView());
            $gw2api = new gw2api();
            $page->addContent('{##main##}', $gw2api->getRankUsageFromRosterView());
        } else {
            $page->setContent('{##main##}', '<h2>Profile</h2>');
            $page->addContent('{##main##}', $this->getProfileView($user_id));
        }
    }
    
    function getUserIDByUsername($user_name) {
        if ($user_name === NULL) {
            return $user_name;
        }
        
        $db = db::getInstance();
        $sql = "SELECT users.id FROM users WHERE users.username = '$user_name';";
        
        $result = $db->query($sql);

        if ($result->num_rows == 1) {
            $result_row = $result->fetch_object();
            return $result_row->id;
        }
        return false;
    }

    function getProfileView($user_id) {
        $login = new Login();
        $view = new View();
        $view->setTmpl($view->loadFile('/views/profile/profile_main.php'));

        $user = $this->getUsers($user_id)[0];
        if (is_object($user)) {
            $subView = new View();
            $subView->setTmpl($view->getSubTemplate('{##profile_badge##}'));
            $identity = new Identity();
            $subView->addContent('{##rank##}', $user->rank_description);
            $subView->addContent('{##display_name##}', $user->username);
            $subView->addContent('{##avatar##}', $identity->getAvatarByUserId($user->id));

            if ($user->id == $login->currentUserID()) { // it's-a-me!
                $subView->addContent('{##email##}', $user->email);

                $gw2api_widgets = new gw2api_Widgets();
                if (gw2api::hasApiData('characters')) {
                    $view->addContent('{##main##}', $gw2api_widgets->getNextBirthdaysView());
                }

                $activity_event_widgets = new Activity_Event_Signups_Widgets();
                $view->addContent('{##main##}', $activity_event_widgets->getSignupsByUserIdView($login->currentUserID()));
            }
            $subView->replaceTags();
            $view->addContent('{##profile_badge##}', $subView);
        } else {
            $view->addContent('{##main##}', 'unknown user');
        }
        $view->replaceTags();
        return $view;
    }
    
    public function getUserByID($user_id = null) {
        return $this->getUsers($user_id)[0];
    }
    
    public function getUsers($user = null) {
        $db = db::getInstance();
        if (is_null($user)) {
            $sql = "SELECT users.id, users.username, users.email, user_ranks.id AS rank, user_ranks.description AS rank_description
                        FROM users
                        LEFT JOIN user_ranks
                        ON users.rank = user_ranks.id
                        WHERE users.rank != '0'
                        ORDER BY users.id ASC;";
        } else {
            $sql = "SELECT users.id, users.username, users.email, user_ranks.id AS rank, user_ranks.description AS rank_description
                        FROM users
                        LEFT JOIN user_ranks
                        ON users.rank = user_ranks.id
                        WHERE users.id = '" . $user . "'
                        OR users.username = '" . $user . "';";
        }
        
        $result = $db->query($sql);

        if ($result->num_rows >= 1) {
            while ($result_row = $result->fetch_object()) {
                $accounts[] = $result_row;
            }
            return $accounts;
        } else {
            return false;
        }
    }

    function getProfileUrlById($user_id) {
        $user = $this->getUserById($user_id);
        return '/profile/' . $user->username;
    }
    
    public function getUsersView() {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/profile/all_users_view.php'));

        $all_users = null;
        $users = $this->getUsers();
        if (is_array($users)) {
            foreach ($users as $user) {
                $option = new View();
                $option->setTmpl($view->getSubTemplate('{##users##}'));
                $option->addContent('{##user_id##}', $user->id);
                $option->addContent('{##username##}', $user->username);
                $option->addContent('{##rank##}', $user->rank);
                $option->addContent('{##rank_description##}', $user->rank_description);
                $option->replaceTags();
                $all_users .= $option;
            }
        }
        if (is_null($all_users)) {
            $view->addContent('{##users##}', 'no users so far');
        } else {
            $view->addContent('{##users##}', $all_users);
        }

        $view->replaceTags();
        return $view;
    }

}
$init_env = new Profile();
$init_env->initEnv();
unset($init_env);
