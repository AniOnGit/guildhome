<?php
/*
 * absolute bare activity-submodule
 * all essential parts are used here to integrate stuff into the website
 * i didn't use templates here (except the global one called as static) to
 * keep it simple and obvious
 * we are using
 *  - routes, to integrate the module into the website
 *  - get() function to handle requests
 * we re not using the update and other functions for now,
 *
 *              this is just a hello world
 */
class Activity_Example extends Activity implements IDatabaseModel {
    function initEnv() {
        Toro::addRoute(["/activity/example" => "Activity_Example"]);
        Toro::addRoute(["/activity/example/:string" => "Activity_Example"]);
    }

    function get($string = '') {
        $login = new Login();
        $username = $login->currentUsername();

        $page = Page::getInstance();
        $page->addContent('{##main##}', "<p>Hello $username!</p>");
        if ($string != '') {
            $sanitized_string = filter_var($string, FILTER_SANITIZE_STRING);
            $page->addContent('{##main##}', "<p>You have added a string to the URL :)</p>");
            $page->addContent('{##main##}', "<p><strong>$sanitized_string</strong></p>");
        }
    }

    function post() {
        /* nothing here in this excample */
    }

    public function getActivityById($activity_id) {
        /* nothing here in this excample */
    }

    public function getActivityView($activity_id = NULL, $compact = NULL) {
        /* nothing here in this excample */
    }

    public function saveActivityTypeDetails($activity_id) {
        /* nothing here in this excample */
    }

    public function updateActivityTypeDetails($activity_id) {
        /* nothing here in this excample */
    }

    public function validateActivityTypeDetails() {
        /* nothing here in this excample */
    }

    function createDatabaseTables($overwriteIfExists) {
        /* nothing here in this example */
    }
}
$init_env = new Activity_Example();
$init_env->initEnv();
unset($init_env);