<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        $data = [
            'title' => 'Dashboard',
            'subTitle' => 'Inicio'
        ];

        return view('layouts-dark-topnav.php', $data);
    }
}
