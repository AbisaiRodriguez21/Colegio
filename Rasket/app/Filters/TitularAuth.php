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

        // 2. Validar si es Nivel 9 (Titular)
        // NOTA: Asegúrate que tu login guarde 'nivelUsuario'. 
        // Si tu login guarda 'nivel', cambia 'nivelUsuario' por 'nivel' aquí abajo.
        if ($session->get('nivelUsuario') != 9 && $session->get('nivel') != 9) {
            return redirect()->back()->with('error', 'Acceso denegado: No eres titular.');
        }

        // 3. Validar si tiene un grupo asignado
        // --- CORRECCIÓN AQUÍ: Quitamos la validación de 'nombreT' ---
        if (!$session->get('nivelT')) {
             return redirect()->back()->with('error', 'No tienes un grupo asignado como titular.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No hacer nada
    }
}