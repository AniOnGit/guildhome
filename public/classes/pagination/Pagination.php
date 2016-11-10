<?php

class Pagination {

    private $limit = 20;
    function getLimit() {
        return $this->limit;
    }

    private $offset = 0;
    function setOffset($offset) {
        $this->offset = $offset;
    }
   
    function getOffset() {
        return $this->offset;
    }

    private $baseURL = '';
    function setBaseURL($url) {
        $this->baseURL = $url;
    }

    function getBaseURL() {
        return $this->baseURL;
    }
    
    function setPagination($offset, $baseURL) {
        $this->setOffset($offset);
        $this->setBaseURL($baseURL);
        return $this;
    }

    function paginationView() {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/core/one_tag.php'));
        $view->addContent('{##data##}', View::linkFab($this->getBaseURL() . ($this->getOffset() - $this->limit), 'prev'));
        $view->addContent('{##data##}', ' | ');
        $view->addContent('{##data##}', View::linkFab($this->getBaseURL() . ($this->getOffset() + $this->limit), 'next'));
        $view->replaceTags();
        return $view;
    }
}
