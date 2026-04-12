<?php
class Home extends Controller {
    public function index() {
        $this->view('home');
    }

    public function login() {
        $this->view('auth/login');
    }

    public function register() {
        $this->view('auth/register');
    }
}


