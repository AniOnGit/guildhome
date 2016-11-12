<?php

class Profile_Settings extends Profile {
    
    function initEnv() {
        Toro::addRoute(["/profile/:alpha/settings" => 'Profile_Settings']);
        
        Validation::registerValidation('birthday', array(new Profile_Settings(), 'validateBirthday'));
    }

    function get($user_name = NULL, $action = NULL) {
        $user_id = $this->getUserIDByUsername($user_name);
        $login = new Login();
        if ($user_id == $login->currentUserID()) {
            $page = Page::getInstance();
            $page->setContent('{##main##}', '<h2>Profile</h2>');
            $page->addContent('{##main##}', $this->getProfileSettingsView());
        }

     }

    function validateBirthday($date = '') {
        $msg = Msg::getInstance();
        $error = false;
        if (!preg_match("/([0-9]{4})\-([0-9]{2})\-([0-9]{2})/", $date, $matches)) {
            $error = true;
            $msg->add('birthday_pattern_validation', 'This does not seem to be a valid birthday-date (dd/mm/yyyy)');
        } else {
            if (!checkdate($matches[2], $matches[3], $matches[1])) {
                $error = true;
                $msg->add('birthday_date_validation', 'This is not a valid date!');
            }
        }
        
        if ($error === true) {
            return false;
        }
        return true;
    }
    
    function getProfileSettingsView() {
        $db = db::getInstance();
        $login = new Login();
        $user = $this->getUserByID($login->currentUserID());
        if (is_object($user)) {
            $view = new View();
            $view->setTmpl($view->loadFile('/views/profile/profile_settings.php'));

            $settings = new Settings();
            $view->addContent('{##main##}', '<h4>Desired Displayname</h4>');
            $view->addContent('{##main##}', $settings->getUpdateSettingForm('display_name', '/profile/' . $user->username . '/settings'));
            $view->addContent('{##main##}', "<h4>When's your birthday?</h4>");
            $view->addContent('{##main##}', $settings->getUpdateDateForm('birthday', '/profile/' . $user->username . '/settings'));

            $login_password = new Login_Password();
            $view->addContent('{##main##}', '<h4>Change Password</h4>');
            $view->addContent('{##main##}', $login_password->getChangePasswordView());
            $view->addContent('{##main##}', '<p>you will be redirected after pressing the button!</p>');
            $view->addContent('{##main##}', '<h4>Avatar</h4>');
            $view->addContent('{##main##}', '<p>use any image url, only direct links will work...</p>');
            $view->addContent('{##main##}', $settings->getUpdateSettingForm('avatar', '/profile/' . $user->username . '/settings'));
            $view->addContent('{##main##}', '<hr />');
            $view->addContent('{##main##}', '<h3>Development stuff</h3>');
            $view->addContent('{##main##}', '<hr />');
            $view->addContent('{##main##}', '<p>Stuff in this section is likely to have a lot of errors, use with caution.</p>');
            $view->addContent('{##main##}', '<h4>Api</h4>');
            $view->addContent('{##main##}', '<p>just copy and paste from your guild wars account page. Only account and guilds are required, characters would be nice.</p>');
            $view->addContent('{##main##}', $settings->getUpdateSettingForm('api_key', '/profile/' . $user->username . '/settings'));

            if (gw2api::hasApiData()) {
                $gw2api = new gw2api();
                $view->addContent('{##main##}', $gw2api->getApiKeyScopeView());
                $gw2api_widgets = new gw2api_Widgets();
                $view->addContent('{##main##}', $gw2api_widgets->getNextBirthdaysView());
            }
            $view->addContent('{##main##}', '<h4>Timezone</h4>');
            $view->addContent('{##main##}', $settings->getTimezonePickerForm('/profile/' . $user->username . '/settings'));

            $view->addContent('{##main##}', '<h4>Theme</h4>');
            $view->addContent('{##main##}', $settings->getUpdateSettingForm('theme_name', '/profile/' . $user->username . '/settings'));
            $view->replaceTags();
            return $view;
        }
        return false;
    }
   
}
$init_env = new Profile_Settings();
$init_env->initEnv();
unset($init_env);
