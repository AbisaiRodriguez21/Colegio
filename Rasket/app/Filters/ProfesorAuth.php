<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ProfesorAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Validar si está logueado
        if (!$session->has('id')) {
            return redirect()->to('/login');
        }

        // 2. Validar si es Nivel 5 (Profesor)
        if ($session->get('nivel') != 5) {
            // Si tiene otro nivel, lo mandamos a la ruta base para que su propio filtro lo redirija
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado: No tienes permisos de Profesor.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No hacer nada
    }
}