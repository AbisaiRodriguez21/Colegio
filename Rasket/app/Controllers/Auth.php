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
                'id'         => $user['id'],                        
                'usuario'    => $user['email'],                    
                'nombre'     => $user['Nombre'] ?? '',          
                'ap'         => $user['ap_Alumno'] ?? '',         
                'am'         => $user['am_Alumno'] ?? '',        
                'nivel'      => $user['nivel'] ?? '',             
                'foto'       => $user['foto'] ?? '',    
                'nivelT'     => $user['nivelT'] ?? 0,
            ]);
// === ENRUTADOR INTELIGENTE DE NIVELES ===
            
            $nivelUsuario = $user['nivel'];

            // CASO 1: ALUMNOS (Nivel 7)
            if ($nivelUsuario == 7) {
                return redirect()->to(base_url('alumno/dashboard'));
            }
            
            // CASO 2: ADMINISTRADORES (Nivel 1 y 2)
            elseif ($nivelUsuario == 1 || $nivelUsuario == 2) {
                // Ellos van al dashboard general (admin)
                return redirect()->to(base_url('dashboard'));
            }

            // CASO 3: DOCENTES (Nivel 9 - Ejemplo)
            elseif ($nivelUsuario == 9) {
                
                // CAMBIAR LA RUTA CUANDO YA SE TENGA LA VISTA CORRESPONDIENTE
                return redirect()->to(base_url('dashboard'));
            }

            // CAMBIAR LA RUTA CUANDO YA SE TENGA LA VISTA CORRESPONDIENTE
            else {
                return redirect()->to(base_url('dashboard'));
            }
            // ==========================================

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
