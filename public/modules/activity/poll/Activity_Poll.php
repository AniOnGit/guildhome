<?php

class Activity_Poll extends Activity {
    public $model = new PollModel();
    public $view = new PollView();

    // CONTROLLER FUNCTIONS
    // TODO: implement
    function initEnv() {}
    // TODO: implement
    function get($alpha = '', $id = NULL) {}
    // TODO: implement
    function post($alpha, $shout_id = NULL) {}

    // MODEL FUNCTIONS
    function getActivityById($activity_id) {
        return $this->model->getActivityById($activity_id);
    }

    function saveActivityTypeDetails($activity_id) {
        return $this->model->saveActivityTypeDetails($activity_id);
    }

    function updateActivityTypeDetails($activity_id) {
        return $this->model->saveActivityTypeDetails($activity_id);
    }

    // VIEW FUNCTIONS
    // TODO: implement
    protected function getActivityView($activity_id = NULL, $compact = NULL) {}
    // TODO: implement
    protected function validateActivityTypeDetails() {}
}
$init_env = new Activity_Poll();
$init_env->initEnv();
unset($init_env);