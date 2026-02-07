<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Verifica si está logueado (Doble seguridad)
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        // 2. Verificar el NIVEL
        // Si el usuario es Nivel 7 (Alumno) o cualquier otro, se expulsa.
        $nivelUsuario = session()->get('nivel');

        // Niveles permitidos
        $nivelesPermitidos = ['1', '2']; 

        if (!in_array($nivelUsuario, $nivelesPermitidos)) {
            // Se manda al dashboard con un error
            return redirect()->to(base_url('dashboard'))->with('error', '⛔ ACCESO DENEGADO: No tienes permisos administrativos.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No hacer nada después
    }
}