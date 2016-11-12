<?php
require_once('config/Initialize.php');

/*
 * I lied, this is no autoload. NOT YET! Don't push me D:
 * 
 * Core modules. I call them classes. Because I can!
 * These I consider Core. There will be no EoL php-Project without these.
 * You may not use routing in any of the core classes, there should be zero
 * user interaction here. Just don't touch them is all I am saying, mmmkay?
 */
require_once ('classes/autoload.php');

$logpath = dirname(getcwd()) . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;
$log = Logger::getInstance();
$log::lfile($logpath . 'eol_log.txt');
 
/*
 * Optional modules
 * 
 * Most websites will be useless without these and most Modules will need
 *  them as well.
 * Modules are instantiated inside the {Classname}.php file and a call to
 *  the initEnv() function is made, if available. I marked those classes
 *  with (*) in this file.
 * I chose the initEnv approach to have on-Load functionality in classes in
 *  addition to the on-Instantiate ones. Feel free to optimize :)
 * Modules may register hooks through the Env class.
 */
require_once ('modules/autoload.php');

/*
 * Get some settings from the db
 */
$settings = new Settings();
$theme_name = filter_var($settings->getSettingByKey('theme_name'), FILTER_SANITIZE_STRING);
if ($theme_name !== false AND !empty($theme_name) AND in_array($theme_name, ['eol', 'boilerplate', 'evolution'])) {
    define('theme', $theme_name);
} else {
    define('theme', 'boilerplate');
}
$timezone = filter_var($settings->getSettingByKey('timezone'), FILTER_SANITIZE_STRING);
if ($timezone !== false AND !empty($timezone) AND in_array($timezone, timezone_identifiers_list())) {
    date_default_timezone_set($timezone);
} else {
    date_default_timezone_set('UTC');
}

/*
 * Here starts the actual page building and content gathering
 */
$page = Page::getInstance();
$page->setTmpl($page->loadFile('/page.php'));

/*
 * now comes a lot of quickly hacked together stuff. I needed some results way
 * before I could lay a proper foundation. This IS a work in progress and
 * there's lots of stuff to do. Wanna help? Feel free ^^
 * These menus will be created and managed by a central module soon(ish)
 */
$page->addContent('{##header##}', '<a href="/">Evolution of Loneliness</a>');

$menu = new Menu();
$page->addContent('{##nav##}', $menu->getMenu('site'));
$page->addContent('{##user_nav##}', $menu->getMenu('user'));
$page->addContent('{##user_nav##}', $menu->getMenu('operator'));

$activity_event_widgets = new Activity_Event_Widgets();
$page->addContent('{##widgets##}', '<hr />');
$page->addContent('{##widgets##}', $activity_event_widgets->getUpcomingActivitiesView());

$login  = new Login();
if ($login->isLoggedIn()) {
    $page->addContent('{##widgets##}', '<hr />');
    $page->addContent('{##widgets##}', '<a href="ts3server://ts3.notjustfor.me?port=9987&password=LederhosenBier">Awesome EoL TS3 Server</a>');
}

$page->addContent('{##footer##}', '<p>created by the community: for the community</p>');
$page->addContent('{##footer##}', '<p>');
$page->addContent('{##footer##}', View::linkFab('/pages/view/impressum', 'imprint (german)'));
$page->addContent('{##footer##}', ' | ');
$page->addContent('{##footer##}', View::linkFab('/pages/view/datenschutz', 'online-privacy (german)'));
$page->addContent('{##footer##}', '</p>');

/*
 * Do the routing as per modules instructions!!
 * Can be overridden here
 */
Toro::addRoute(["/" => "Pages"]);
ToroHook::add('404', function() {
    header('HTTP/1.0 404 Not Found');
    $page = Page::getInstance();
    $page->setTmpl($page->loadFile('/views/core/404.php'));
    $page->replaceTags();
    echo $page;
    exit;
});
Toro::serve();

/*
 * You did it! This website is being displayed now ;)
 */
$page->replaceTags();
echo $page;
