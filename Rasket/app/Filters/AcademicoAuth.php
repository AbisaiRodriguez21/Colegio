<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AcademicoAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // Admin y Director
        $nivelesPermitidos = ['1', '2'];

        if (!$session->has('id') || !in_array($session->get('nivel'), $nivelesPermitidos)) {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tienes permisos académicos.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita acción después
    }
}