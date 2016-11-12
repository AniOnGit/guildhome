<?php

class Dev {

    function initEnv() {
        Toro::addRoute(["/dev" => 'Dev']);
        Toro::addRoute(["/dev/:alpha" => 'Dev']);
        Toro::addRoute(["/dev/:alpha/:alpha" => 'Dev']);
    }

    function get($alpha = NULL, $beta = NULL) {
        $login = new Login();
        if ($login->isLoggedIn() !== TRUE) {
            header("Location: /activities");
            exit;
        }
        $page = Page::getInstance();
        $page->clearAll();
        $page->setTmpl($page->loadFile('/page.php'));
        $page->setContent('{##main##}', '<h2>Dev</h2>');
        switch ($alpha) {
            default :
                $page->addContent('{##main##}', '<h3>Dev Tools</h3>');
                $page->addContent('{##main##}', "<p>Some tools and pages to help with development.</p>");
                $page->addContent('{##main##}', '<p>');
                $page->addContent('{##main##}', '<a href="/dev/allinonepage">Theme-Page</a>');
                $page->addContent('{##main##}', '</p>');
                break;
            case "allinonepage" :
                $page->addContent('{##header##}', '<a href="/">Test-site for the: ' . constant('theme') . '-theme</a>');

                $menu = new Menu();
                $page->addContent('{##nav##}', $menu->getMenu('site'));
                $page->addContent('{##user_nav##}', $menu->getMenu('user'));
                $page->addContent('{##user_nav##}', $menu->getMenu('operator'));

                $page->addContent('{##main##}', '<h3>Template Mania</h3>');
                $page->addContent('{##main##}', "<p>What we've got her is most template-files available in one place. This should help with css-theme development.</p>");
                $page->addContent('{##main##}', "<p>This page should at least look read<ble if you wanna publish your theme ^^</p>");

                $register = new Register();
                $page->addContent('{##main##}', $register->getRegisterView());

                $login = new Login();
                $page->addContent('{##main##}', $login->getLoginView());
                $page->addContent('{##main##}', $login->getLogoutView());
                
                $activity = new Activity();
                $page->addContent('{##main##}', $activity->activityMenu());
                
                $activity_event = new Activity_Event();
                $page->addContent('{##main##}', $activity_event->getActivityForm());
                $page->addContent('{##main##}', $activity_event->getDeleteActivityForm());

                $activity_shout = new Activity_Shout();
                $page->addContent('{##main##}', $activity_shout->getActivityForm());
                $page->addContent('{##main##}', $activity_shout->getDeleteActivityForm());

                break;
        }

    }
    
    function post($alpha = NULL, $beta = NULL) {
        $login = new Login();
        if ($login->isLoggedIn() !== TRUE) {
            header("Location: /activities");
            exit;
        }

        $env = Env::getInstance();
        switch ($alpha) {
            default :
                break;
        }
    }

}
$dev = new Dev();
$dev->initEnv();
unset($dev);