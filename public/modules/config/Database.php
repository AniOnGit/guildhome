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
            $page->setContent('{##main##}', "");
            $page->addContent('{##main##}', View::createPrettyButtonForm("/dbsetup", null, "Start DB setup!"));
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
}
$init = new Database();
$init->initEnv();
unset($init);


