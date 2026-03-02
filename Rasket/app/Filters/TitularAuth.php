<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TitularAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Validar si está logueado
        if (!$session->has('id')) {
            return redirect()->to('/login');
        }

        // 2. Validar si es Nivel 9  
        if ($session->get('nivel') != 9) {
            return redirect()->back()->with('error', 'Acceso denegado: No eres titular.');
        }

        // 3. Validar si tiene un grupo asignado 
        if (!$session->get('nivelT')) {
             return redirect()->back()->with('error', 'No tienes un grupo asignado como titular.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No hacer nada
    }
}