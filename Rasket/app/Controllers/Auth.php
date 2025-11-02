<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class Auth extends BaseController
{
    public function index()
    {
        // Carga la vista de login
        return view('auth/login');
    }

    public function attemptLogin()
    {
        $usuario = trim($this->request->getPost('usuario'));
        $password = trim($this->request->getPost('pass'));

        // Llama al modelo para verificar usuario
        $model = new UsuarioModel();
        $user = $model->verificarLogin($usuario, $password);

        if ($user) {
            session()->set([
                'isLoggedIn' => true,
                'usuario'    => $user['email'],
                'nombre'     => $user['Nombre'] ?? '',
                'nivel'      => $user['nivel'] ?? '',
            ]);

            return redirect()->to(base_url('dashboard'));
        } else {
            return redirect()->back()->with('error', 'Usuario o contraseña incorrectos.');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }
}
