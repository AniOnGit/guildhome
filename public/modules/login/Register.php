<?php

class Register {

    public function initEnv() {
        Toro::addRoute(["/register" => 'Register']);
    }

    public function get() {
        $page = Page::getInstance();
        $page->setContent('{##main##}', '<h2>Register</h2>');
        $page->addContent('{##main##}', $this->getRegisterView());
    }
    
    public function post() {
        $env = Env::getInstance();
        
        if (isset($env->post('register')['submit'])) {
            if ($this->registerNewUser() !== false) {
                header("Location: /activities");
            } else {
                header("Location: /register");
            }
        }
    }

    private function registerNewUser() {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $error = 0;
        if (empty($env->post('register')['voucher'])) {
            $msg->add('register_voucher_validation', 'Sorry, VIP only; please obtain your personal voucher from any guild officer!');
            $error = 1;
        } elseif ($env->post('register')['voucher'] !== 'LederhosenBier') {
            $msg->add('register_voucher_validation', 'Unknown voucher. Make sure you typed it in exactly right!');
            $error = 1;
        }
        
        if (empty($env->post('register')['username'])) {
            $msg->add('register_username_validation', 'Username is empty');
            $error = 1;
        } elseif (strlen($env->post('register')['username']) > 32) {
            $msg->add('register_username_validation', 'Username is too long (max 32 characters)');
            $error = 1;
        } elseif (strlen($env->post('register')['username']) < 2) {
            $msg->add('register_username_validation', 'Username is too short (min 2 characters)');
            $error = 1;
        } elseif (!ctype_alnum($env->post('register')['username'])) {
            $msg->add('register_username_validation', 'Username contains shitty characters');
            $error = 1;
        }

        if (empty($env->post('register')['email'])) {
            $msg->add('register_email_validation', "No eMail address given. Several site functions will not be available to you if you omit your eMail address.");
        } elseif (!filter_var($env->post('register')['email'], FILTER_VALIDATE_EMAIL)) {
            $msg->add('register_email_validation', "please use a valid eMail Adress!");
            $error = 1;
        }

        if (empty($env->post('register')['password_new']) AND empty($env->post('register')['password_repeat'])) {
            $msg->add('register_password_new_validation', "No password. Good plan! NOT!!");
            $msg->add('register_password_repeat_validation', "Hey, this one matches the empty one! That's something, isn't it?");
            $error = 1;
        } elseif ($env->post('register')['password_new'] !== $env->post('register')['password_repeat']) {
            $msg->add('register_password_repeat_validation', "Variation is nice. you get a richer life and everything. Not with passwords though, make sure that they match ^^");
            $error = 1;
        } elseif (strlen($env->post('register')['password_new']) < 6) {
            $msg->add('register_password_new_validation', "You were asked to provide a password, not an abbreviation of one! Use at least six characters!");
            $error = 1;
        }

        if ($error == 0) {
            $db = db::getInstance();
            // No validation errors
            $username = $db->real_escape_string(strip_tags($env->post('register')['username'], ENT_QUOTES));
            $email = $db->real_escape_string(strip_tags($env->post('register')['email'], ENT_QUOTES));

            $password = $env->post('register')['password_new'];

            // crypt the user's password with PHP 5.5's password_hash() function, results in a 60 character
            // hash string. the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using
            // PHP 5.3/5.4, by the password hashing compatibility library
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // check if user or email address already exists
            $sql = "SELECT * FROM users WHERE username = '" . $username . "' OR email = '" . $email . "';";
            $result = $db->query($sql);

            if ($result->num_rows >= 1) {
                $msg->add('register_general_validation', "This username and or eMail is already taken. Sorry!");
            } else {
                $sql = "INSERT INTO users (username, password_hash, email, rank)
                            VALUES('" . $username . "', '" . $password_hash . "', '" . $email . "', '2');";
                $result = $db->query($sql);

                if ($result) {
                    $msg->add('register_general_validation', "User " . $username . " has been created.");
                    $env->clearPost('register');

                    $hooks = $env::getHooks('save_new_user_hook');
                    if ($hooks!== false) {
                        foreach ($hooks as $hook) {
                            $hook['save_new_user_hook']($db->insert_id);
                        }
                    }
                    return $db->insert_id; // user creation complete
                } else {
                    $msg->add('register_general_validation', "Something unexpected happened during Database operations. No user has been created.");
                    return false;
                }
            }
        } else {
            return false;
        }
    }
   
    /*
     * Views -> These have to be public and may not echo or print ANY data.
     * Use the Msg class to display debug stuff if you have to.
     * Functions may only return 'text' data or 'false'
     */
    
    public function getRegisterView() {
        $env = Env::getInstance();
        $msg = Msg::getInstance();

        $view = new View;
        $view->setTmpl($view->loadFile('/views/core/login/register_form.php'), array(
            '{##form_action##}' => '/register',
            '{##register_username##}' => $env->post('register')['username'],
            '{##register_username_text##}' => 'Username',
            '{##register_username_validation##}' => $msg->fetch('register_username_validation'),
            '{##register_email##}' => $env->post('register')['email'],
            '{##register_email_text##}' => 'eMail',
            '{##register_email_validation##}' => $msg->fetch('register_email_validation'),
            '{##register_voucher##}' => $env->post('register')['voucher'],
            '{##register_voucher_text##}' => 'Voucher',
            '{##register_voucher_validation##}' => $msg->fetch('register_voucher_validation'),
            '{##register_password_new##}' => '',
            '{##register_password_new_text##}' => 'Password',
            '{##register_password_new_validation##}' => $msg->fetch('register_password_new_validation'),
            '{##register_password_repeat##}' => '',
            '{##register_password_repeat_text##}' => 'Password (repeat)',
            '{##register_password_repeat_validation##}' => $msg->fetch('register_password_repeat_validation'),
            '{##register_submit_text##}' => 'Register',
            '{##register_cancel_text##}' => 'Clear',
        ));
        $view->replaceTags();
        return $view;
    }
}
$register = new Register();
$register->initEnv();
unset($register);