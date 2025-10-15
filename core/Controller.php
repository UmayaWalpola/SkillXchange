<?php
// Show all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
// core/Controller.php
class Controller {

    // Load model
    public function model($model) {
        $modelPath = '../app/models/' . $model . '.php';
        if(file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        } else {
            die("Model $model not found.");
        }
    }

    // Load view
   public function view($view, $data = []) {
    $viewPath = '../app/views/' . $view . '.php';
    if(file_exists($viewPath)) {
        extract($data); // <-- this makes keys of $data available as variables in the view
        require_once $viewPath;
    } else {
        die("View $view not found at: " . $viewPath);
    }
}
}

