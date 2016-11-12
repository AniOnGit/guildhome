<?php
require_once ('log/Logger.php'); // logging stuff
require_once ('toro/Toro.php'); // url requests
require_once ('parsedown/Parsedown.php'); // format that text!
require_once ('view/View.php'); // template based output-containers
require_once ('pagination/Pagination.php');
// static container for the page-template
require_once ('view/Page.php'); // Page::getInstance();
require_once ('view/Menu.php'); // temporary menu-class
                                // your get and post data, sanitized!
require_once ('env/Env.php'); // Env::getInstance();
require_once ('env/Validation.php'); // provides hook for validation
require_once ('db/db.php'); // db::getInstance();
require_once ('msg/Msg.php'); // feedback for user input & actions
require_once ('mail/class.phpmailer.php');
require_once ('mail/class.smtp.php'); //
