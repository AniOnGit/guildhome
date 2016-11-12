<?php
class Login {
    // start controller
    function initEnv() {
        Toro::addRoute(["/login" => 'Login']);
        Toro::addRoute(["/logout" => 'Login']);
    }
    
    public function post() {
        $env = Env::getInstance();
        if (isset($env->post('login')['submit'])) {
            if ($this->dologinWithPostData() !== false) {
                $setting = new Settings();
                $date = new DateTime();
                if ($setting->getSettingByKey('last_login', $this->currentUserID())) {
                    $setting->updateSetting('last_login', $date->getTimestamp());
                    if (null !== $env->post('target_url')) {
                        header("Location: " .  $env->post('target_url'));
                        exit;
                    }
                    header("Location: /activities");
                } else { // first_time log-in
                    $profile = new Profile();
                    $settings_url = $profile->getProfileUrlById($this->currentUserID()) . '/settings';
                    $setting->updateSetting('last_login', $date->getTimestamp());
                    header("Location: $settings_url");
                }
                exit;
            } else {
                header("Location: /login");
                exit;
            }
        }
        
        if (isset($env->post('logout')['submit'])) {
            $this->doLogout();
            if (null !== $env->post('target_url')) {
                header("Location: " .  $env->post('target_url'));
                exit;
            }
            header("Location: /");
            exit;
        }
    }
    // end controller
    // start model
    private function dologinWithPostData() {
        $msg = Msg::getInstance();
        $env = Env::getInstance();

        $error = 0;
        if (empty($env->post('login')['username'])) {
            $msg->add('login_username_validation', 'Please type in your username');
            $error = 1;
        }

        if (empty($env->post('login')['password'])) {
            $msg->add('login_password_validation', 'Please provide a password. Preferably yours :)');
            $error = 1;
        }

        if ($error == 1) {
            return false;
        }

        $db = db::getInstance();
        $username = $db->real_escape_string($env->post('login')['username']);
        $sql = "SELECT users.id, users.username, users.email, user_ranks.id AS rank, users.password_hash, user_ranks.description AS rank_description
                    FROM users
                    INNER JOIN user_ranks
                    ON users.rank = user_ranks.id
                    WHERE users.username = '" . $username . "' OR users.email = '" . $username . "';"; // login via email address or username
        $result = $db->query($sql);

        if ($result->num_rows >= 1) {
            // user or email exists
            $result_row = $result->fetch_object();
            if (password_verify($env->post('login')['password'], $result_row->password_hash)) {
                $this->setSessionData($result_row);
                return true;
            } else {
                $msg->add('login_password_validation', 'Password is wrong. Concentrate!!');
            }
        } else {
            $msg->add('login_username_validation', 'Unknown Username/email. Think Hard!!');
        }
        return false;
    }
    
    function setSessionData($result_row) {
        $_SESSION['evo']['user_id'] = $result_row->id;
        $_SESSION['evo']['username'] = $result_row->username;
        $_SESSION['evo']['email'] = $result_row->email;
        switch ($result_row->rank) {
            default:
            case '2' :
                $_SESSION['evo']['user_is_admin'] = null;
                $_SESSION['evo']['user_is_operator'] = null;
                break;
            case '1' :
                $_SESSION['evo']['user_is_operator'] = true;
                break;
            case '0' :
                $_SESSION['evo']['user_is_admin'] = true;
                break;
        }

        $_SESSION['evo']['user_login_status'] = 1;
    }
    
    public function doLogout() {
        if ($this->isLoggedIn()) {
            // delete the session of the user
            $_SESSION['evo'] = null;
        }
    }

    public function isLoggedIn() {
        if (isset($_SESSION['evo']['user_login_status']) AND $_SESSION['evo']['user_login_status'] == 1) {
            return true;
        }
        return false;
    }

    public function currentUserID() {
        if ($this->isLoggedIn()) {
            return $_SESSION['evo']['user_id'];
        }
        return false;
    }
    
    public function currentUsername() {
        if ($this->isLoggedIn()) {
            return $_SESSION['evo']['username'];
        }
        return 'Guest';
    }
    
    public function isOperator() {
        if (isset($_SESSION['evo']['user_is_operator']) AND $_SESSION['evo']['user_is_operator'] == 1
            OR $this->isAdmin()) {
            return true;
        }
        return false;
    }
    
    public function isAdmin() {
        if (isset($_SESSION['evo']['user_is_admin']) AND $_SESSION['evo']['user_is_admin'] == 1) {
            return true;
        }
        return false;
    }
    // end model
    // view controller
    public function getLoginView($target_url = null) {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/login/login_form.php'), array(
            '{##form_action##}' => '/login',
            '{##login_username##}' => $env->post('login')['username'],
            '{##login_username_text##}' => 'Username',
            '{##login_username_validation##}' => $msg->fetch('login_username_validation'),
            '{##login_password##}' => '',
            '{##login_password_text##}' => 'Password',
            '{##login_password_validation##}' => $msg->fetch('login_password_validation'),
            '{##login_submit_text##}' => 'Log in',
            '{##login_forgot_text##}' => 'Forgot Password',
            '{##register_link##}' => '/register/',
            '{##register_link_text##}' => 'register new user',
            '{##reset_password_link##}' => '/login/reset_password',
            '{##reset_password_link_text##}' => 'lost your password?',
        ));
        if ($target_url !== null) {
            $view->addContent('{##target_url##}', $target_url);
        }
        $view->replaceTags();
        return $view;
    }
    
    public function getLogoutView($target_url = null) {
        $logout_link_text = ($this->isLoggedIn()) ? $_SESSION['evo']['username'] : 'Guest';
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/login/logout_form.php'), array(
            '{##form_action##}' => '/logout',
            '{##logout_link##}' => '/logout',
            '{##logout_link_text##}' => 'Logout ' . $logout_link_text,
        ));
        if ($target_url !== null) {
            $view->addContent('{##target_url##}', $target_url);
        }
        $view->replaceTags();
        return $view;
        
    }

    public function getCombinedLoginView($target_url = null) {
        if ($this->isLoggedIn() == true) {
            $view = $this->getLogoutView($target_url);
        } else {
            $view = $this->getLoginView($target_url);
        }
        return $view;
    }
    // end view
}
$login = new Login();
$login->initEnv();
unset($login);