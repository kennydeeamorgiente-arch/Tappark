<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function __construct()
    {
        helper('form');
    }
    
    public function index()
    {
        // Check if user is logged in
        $session = \Config\Services::session();
        if (!$session->get('user_id')) {
            // User is not logged in, redirect to login page
            return redirect()->to('/login');
        }
        
        // User is logged in, show main layout
        return view('main_layout');
    }
}
