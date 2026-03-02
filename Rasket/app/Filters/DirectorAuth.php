<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DirectorAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }
 
        $nivelUsuario = session()->get('nivel');

        // Niveles permitidos  
        $nivelesPermitidos = ['2'];

        if (!in_array($nivelUsuario, $nivelesPermitidos)) {
            return redirect()->to(base_url('dashboard'))->with('error', '⛔ ACCESO DENEGADO: Área exclusiva de Dirección.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No hacer nada después
    }
}