<?php
namespace App\controllers;

use App\auth\Login as AuthLogin;

class Login
{
    public function showForm()
    {
        $authLogin = new AuthLogin();
        $authLogin->showForm();
    }

    public function handleLogin()
    {
        $authLogin = new AuthLogin();
        $authLogin->handleLogin();
    }

    
     public function setRememberMeCookie($userId)
     {
         $authLogin = new AuthLogin();
        $authLogin->setRememberMeCookie($userId);
     }
}