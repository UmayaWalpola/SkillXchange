<<?php
class Core {
    protected $currentController = 'PagesController';
    protected $currentMethod = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->getUrl();

        // Controller - check if it exists with "Controller" suffix
        if (isset($url[0])) {
            $controllerName = ucwords($url[0]) . 'Controller';
            $controllerFile = '../app/controllers/' . $controllerName . '.php';
            
            if (file_exists($controllerFile)) {
                $this->currentController = $controllerName;
                unset($url[0]);
            }
        }

        // Require the controller file
        require_once '../app/controllers/' . $this->currentController . '.php';
        
        // Instantiate controller
        $this->currentController = new $this->currentController;

        // Method - check if method exists in controller
        if (isset($url[1]) && method_exists($this->currentController, $url[1])) {
            $this->currentMethod = $url[1];
            unset($url[1]);
        }

        // Params - get remaining URL segments as parameters
        $this->params = $url ? array_values($url) : [];

        // Call controller method with parameters
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }

    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }
}


