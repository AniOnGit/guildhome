<?php

class Menu {
    function getMenu($name = 'site') {
        $login = new Login();
        switch ($name) {
            default: 
            case 'site':
                    $menu  = '<ul class="site-menu">';
                    $menu .= '  <li><a href="/">Home</a></li>';
                    $menu .= '  <li><a href="/activities">Activities</a></li>';
                    $menu .= '  <li><a href="/profiles">Members</a></li>';
                    $menu .= '  <li><a href="/pages/view/about">About EoL</a></li>';
                    $menu .= '</ul>';
                break;
            case 'user':
                    $menu  = '<ul class="user-menu">';
                    $menu .= '  <li>' . $login->getCombinedLoginView(Env::getCurrentURL()) . '</li>';
                    if ($login->isLoggedIn()) {
                        $menu .= '<hr />';
                        $menu .= '  <li><a href="/profile/' . $login->currentUsername() . '">Profile</a>';
                        $menu .= '  <ul>';
                        $menu .= '      <li><a href="/profile/' . $login->currentUsername() . '/settings">Settings</a></li>';
                        $gw2api = new gw2api();
                        if ($gw2api->hasApiData('characters')) {
                            $menu .= '      <li><a href="/profile/' . $login->currentUsername() . '/characters">Characters</a></li>';
                        }
                        $menu .= '  </ul>';
                        $menu .= '  </li>';

                    }
                    $menu .= '</ul>';
                break;
            case 'operator':
                    if ($login->isLoggedIn()) {
                        $menu  = '<hr />';
                        $menu .= '<ul class="operator-menu">';
                        $menu .= '  <li><a href="/gw2api">gw2api (test)</a></li>';
                        $menu .= '</ul>';
                    } else {
                        $menu = '';
                    }
                break;
        }
        return $menu;
    }
    /*
     * just a mockup, this should one day be converted to a real menu-handling
     * class thingy
     */
    function activityMenu($active = NULL, $compact = false) {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));

        $login = new Login();
        $view->addContent('{##data##}', '<nav class="activities clearfix">');
        if ($active == NULL) {
            $view->addContent('{##data##}', '<div class="header active">');
        } else {
            $view->addContent('{##data##}', '<div class="header">');
        }
        $view->addContent('{##data##}', '<h2><a href="/activities">Activity Stream (last 10 days)</a></h2>');
        $view->addContent('{##data##}', '</div>');
        if ($compact === true) {
            $view->addContent('{##data##}', '<ul class="activity_menu compact">');
        } else {
            $view->addContent('{##data##}', '<ul class="activity_menu">');
        }

        if ($active == 'shouts' OR $active == 'shout') {
            $view->addContent('{##data##}', '<li class="active">');
        } else {
            $view->addContent('{##data##}', '<li>');
        }
        $view->addContent('{##data##}', '<div class="count">');
        $count = Activity::getActivityCountByType('1');
        $view->addContent('{##data##}', '<a href="/activities/shouts">' . $count->count . '</a>');
        $view->addContent('{##data##}', '</div>');
        $view->addContent('{##data##}', '<div class="title"><a href="/activities/shouts">Shouts</a> (' . $count->count_all . ')</div>');
        if ($login->isLoggedIn()) {
            $view->addContent('{##data##}', '<div class="action"><a href="/activity/shout/new">+</a></div>');
        }
        $view->addContent('{##data##}', '</li>');
        
        if ($active == 'events' OR $active == 'event') {
            $view->addContent('{##data##}', '<li class="active">');
        } else {
            $view->addContent('{##data##}', '<li>');
        }
        $view->addContent('{##data##}', '<div class="count">');
        $count = Activity::getActivityCountByType('2');
        $view->addContent('{##data##}', '<a href="/activities/events">' . $count->count . '</a>');
        $view->addContent('{##data##}', '</div>');
        $view->addContent('{##data##}', '<div class="title"><a href="/activities/events">Events</a> (' . $count->count_all . ')</div>');
        if ($login->isLoggedIn()) {
            $view->addContent('{##data##}', '<div class="action"><a href="/activity/event/new">+</a></div>');
        }
        $view->addContent('{##data##}', '</li>');

//        if ($active == 'polls' OR $active == 'poll') {
//            $view->addContent('{##data##}', '<li class="active">');
//        } else {
//            $view->addContent('{##data##}', '<li>');
//        }
//        $view->addContent('{##data##}', '<div class="count">');
//        $count = $this->getActivityCountByType('3');
//        $view->addContent('{##data##}', '<a href="/activities/polls">' . $count->count . '</a>');
//        $view->addContent('{##data##}', '</div>');
//        $view->addContent('{##data##}', '<div class="title"><a href="/activities/polls">Polls</a> (' . $count->count_all . ')</div>');
//        if ($login->isLoggedIn() AND $login->isOperator()) {
//            $view->addContent('{##data##}', '<div class="action"><a href="/activity/poll/new">+</a></div>');
//        }
//        $view->addContent('{##data##}', '</li>');
        $view->addContent('{##data##}', '</ul>');
        $view->addContent('{##data##}', '</nav>');
        $view->replaceTags();
        return $view;
    }

}
