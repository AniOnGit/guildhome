<?php

class Pages {

    function initEnv() {
        Toro::addRoute(["/home" => 'Pages']);
        Toro::addRoute(["/pages/:alpha" => 'Pages']);
        Toro::addRoute(["/pages/:alpha/:alpha" => 'Pages']);
    }

    private $registered_pages = ['home', 'about', '7dtd', 'imprint', 'impressum', 'privacy', 'datenschutz'];

    function get($action = 'view', $slug = 'home') {
        $env = Env::getInstance();
        $login = new Login();
        $page = Page::getInstance();
        
        switch ($action) {
            default :
            case "view" :
                $env->clearPost('page');
                $page->setContent('{##main##}', $this->getPageBySlugView($slug));
                break;
            case "update" :
                if (!$login->isOperator()) {
                    break;
                }
                $page->setContent('{##main##}', '<h2>Update Page</h2>');
                $page->addContent('{##main##}', $this->getPageBySlugFormView($slug));
                if (isset($env->post('page')['preview'])) {
                    $page->addContent('{##main##}', $this->getPagePreview());
                }
                break;
        }
    }
    
    function post($action, $slug = NULL) {
        $env = Env::getInstance();
        $login = new Login();
        if (!$login->isOperator()) {
            return false;
        }
        switch ($action) {
            case 'update' :
                if ($this->validatePage() === true AND !isset($env->post('page')['preview'])) {
                    if ($this->savePage($slug) === true) {
                        header("Location: /pages/view/$slug");
                        exit;
                    }
                }
                $this->get('update', $slug);
                break;
        }
    }
    
    function getPageBySlug($slug) {
        $db = DB::getInstance();
        $sql = "SELECT * FROM pages_slugs ps LEFT JOIN pages p ON ps.pages_id = p.id WHERE ps.slug = '$slug'";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows == 1) {
            $result = $query->fetch_object();
            return $result;
        }
        return false;
    }

    function getPageIdBySlug($slug) {
        $db = DB::getInstance();
        $sql = "SELECT ps.pages_id AS page_id FROM pages_slugs ps LEFT JOIN pages p ON ps.pages_id = p.id WHERE ps.slug = '$slug'";
        $query = $db->query($sql);

        if ($query !== false AND $query->num_rows == 1) {
            $result = $query->fetch_object();
            return $result->page_id;
        }
        return false;
    }

    function savePage($slug) {
        $db = DB::getInstance();
        $env = Env::getInstance();
        
        $page_content = $env->post('page')['content'];
        $page_id = $this->getPageIdBySlug($slug);
        
        $sql = "UPDATE pages SET content = '$page_content' WHERE id = '$page_id';";
        $query = $db->query($sql);

        if ($db->affected_rows > 0 OR $query !== false) {
            return true;
        }
        return false;
    }

    function getPageBySlugView($slug) {
        $page_data = $this->getPageBySlug($slug);

        $view = new View();
        $view->setTmpl($view->loadFile('/views/page/page_view.php'));
        $view->addContent('{##page_content##}', Parsedown::instance()->setBreaksEnabled(true)->text($page_data->content));

        $login = new Login();
        if ($login->isOperator()) {
            $memberView = new View();
            $memberView->setTmpl($view->getSubTemplate('{##user_logged_in##}'));
            $memberView->addContent('{##edit_link##}', View::linkFab("/pages/update/$slug", 'update'));
            $memberView->replaceTags();
            $view->addContent('{##user_logged_in##}',  $memberView);
        }
        $view->replaceTags();
        
        return $view;
    }
    
    function getPagePreview() {
        $env = Env::getInstance();
        $page_content = $env->post('page')['content'];

        $view = new View();
        $view->setTmpl($view->loadFile('/views/page/page_view.php'));
        $view->addContent('{##page_content##}', Parsedown::instance()->setBreaksEnabled(true)->text($page_content));
        $view->replaceTags();

        return $view;
    }
    
    function getPageBySlugFormView($slug) {
        $page_data = $this->getPageBySlug($slug);
        $page_content = str_replace("\n\r", "&#13;", $page_data->content);

        $msg = Msg::getInstance();
        
        $view = new View();
        $view->setTmpl($view->loadFile('/views/page/page_form.php'));
        $view->addContent('{##form_action##}', "/pages/update/$slug");
        $view->addContent('{##page_slug##}', $slug);
        $view->addContent('{##page_content_text##}', 'Your Page-content goes here!');
        $view->addContent('{##page_content##}', $page_content);

        $view->addContent('{##page_content_validation##}', $msg->fetch('page_content_validation')) ;
        $view->addContent('{##page_general_validation##}', '');

        $view->addContent('{##preview_text##}', 'Preview');
        $view->addContent('{##submit_text##}', 'Make it so!');
    
        $view->replaceTags();

        return $view;
    }
    
    function validatePage() {
        $msg = Msg::getInstance();
        $env = Env::getInstance();

        $errors = false;
        if (empty($env->post('page')['content'])) {
            $msg->add('page_content_validation', 'Blank Page. BLANK PAGE! Sorry, NO BLANK PAGES ALLOWED!');
            $errors = true;
        }

        if ($errors === false) {
            return true;
        }
        return false;
    }
}
$pages = new Pages();
$pages->initEnv();
unset($pages);