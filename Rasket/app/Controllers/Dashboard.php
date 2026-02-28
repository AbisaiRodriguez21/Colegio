<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $session = session();
        
        if (!$session->get('isLoggedIn') && !$session->has('id')) {
            return redirect()->to(base_url('login'));
        }

        $nivel = $session->get('nivel');

        switch ($nivel) {
            case 7: 
                return redirect()->to(base_url('alumno/dashboard'));
                break;
                
            case 9:
                return redirect()->to(base_url('titular/dashboard'));
                break;
                
            case 1:
                return redirect()->to(base_url('admin/dashboard'));
                break;
                
            default:
                return redirect()->back()->with('error', 'No tienes permitido el acceso');

        }
    }
}