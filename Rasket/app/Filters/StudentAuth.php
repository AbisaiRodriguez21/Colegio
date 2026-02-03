<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class StudentAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Si no está logueado -> Login
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        // 2. Si ESTÁ logueado pero NO es alumno (Nivel 7)
        if ($session->get('nivel') != 7) {
            // Lo mandamos a su dashboard correspondiente (o al home)
            return redirect()->to(base_url('dashboard')); 
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No hacer nada después
    }
}