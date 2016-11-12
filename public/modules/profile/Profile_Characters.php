<?php

class Profile_Characters {
    
    function initEnv() {
        Toro::addRoute(["/profile/:alpha/characters" => 'Profile_Characters']);
    }

    function get($user_name = NULL) {
        $page = Page::getInstance();
        $login = new Login();
        
        if ($user_name == $login->currentUsername()) {
            $page->setContent('{##main##}', '<h2>Profile</h2>');
            $gw2api = new gw2api();
            if ($gw2api->hasApiData('characters')) {
                $page->addContent('{##main##}', $gw2api->getAccountCharactersView());
            }
        }
    }   
}
$init_env = new Profile_Characters();
$init_env->initEnv();
unset($init_env);
