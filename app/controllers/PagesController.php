<?php
class PagesController extends Controller {
    
    public function index() {
        $this->view('home');
    }
    
    public function home() {
        $this->view('home');
    }
}