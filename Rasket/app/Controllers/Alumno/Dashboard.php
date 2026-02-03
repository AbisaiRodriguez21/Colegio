<?php

namespace App\Controllers\Alumno;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $session = session();

        // 1. Seguridad
        if (!$session->has('id') || $session->get('nivel') != 7) {
            return redirect()->to('/login');
        }

        // 2. Obtener datos
        $userModel = new UsuarioModel();
        
        $usuario = $userModel->find($session->get('id'));

        if (!$usuario) return redirect()->to('/login');

        $data = [
            'nombre'    => $session->get('nombre'),
            'apellidos' => $session->get('apellidos'),
            'matricula' => $session->get('matricula'),
            'passwordActual' => $usuario['pass'] ?? '' 
        ];

        // 3. Lógica de Fecha
        $fechaLiberacion = '2025-10-15'; 
        $hoy = date('Y-m-d');
        $data['mostrarBoleta'] = ($hoy >= $fechaLiberacion);
        $data['fechaLiberacionTexto'] = date('d/m/Y', strtotime($fechaLiberacion)); 

        return view('VistadelAlumno/dashboard', $data);
    }

    // --- FUNCIÓN AJAX PARA GUARDAR ---
    public function actualizarPassword()
    {
        $session = session();
        $request = \Config\Services::request();

        if (!$request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody("Acceso denegado");
        }

        $nuevoPass = trim($request->getPost('new_password')); // trim quita espacios al inicio/final
        $idUsuario = $session->get('id');

        // VALIDACIÓN 1: Vacío
        if (empty($nuevoPass)) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'La contraseña no puede estar vacía.']);
        }

        // VALIDACIÓN 2: Sin Espacios (Seguridad del lado del servidor)
        if (strpos($nuevoPass, ' ') !== false) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No se permiten espacios en la contraseña.']);
        }

        $userModel = new UsuarioModel();
        
        $update = $userModel->update($idUsuario, ['pass' => $nuevoPass]);

        if ($update) {
            return $this->response->setJSON(['status' => 'success', 'msg' => '¡Guardado! Cerrando ventana...']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al guardar en la base de datos.']);
        }
    }
}