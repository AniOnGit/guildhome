<?php

class Database {

    function initEnv() {
        Toro::addRoute(["/dbsetup" => 'database']);
    }

    function get() {
        $login = new Login();
        $admin = $login->isAdmin();

        $page = Page::getInstance();

        if ($admin) {
            $page->setContent('{##main##}', "Great, you are an admin! Let's push a button:\n ");
            $page->addContent('{##main##}', $this->getButton());
        } else {
            $page->setContent('{##main##}', "Try again, guy, you are no admin!");
        }
    }

    function post() {
        var_dump($_SESSION['dbconfig']);
        if (isset($_SESSION['dbconfig'])) {
            foreach ($_SESSION['dbconfig'] as $model) {
                $model->createDatabaseTables((boolean) true);
            }
        }
    }

    function getButton($target_url = '') {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/activity/event/signups/activity_event_signups_button.php'));
        $view->setContent('{##signup##}', '/dbsetup');
        $view->addContent('{##signup_text##}', 'Do it!');
        $view->addContent('{##target_url##}', $target_url);
        $view->replaceTags();
        return $view;
    }
}
$init = new Database();
$init->initEnv();
unset($init);


