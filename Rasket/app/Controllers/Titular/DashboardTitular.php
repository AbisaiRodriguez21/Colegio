<?php

namespace App\Controllers\Titular;

use App\Controllers\BaseController;
use App\Models\CalificacionesModel;

class DashboardTitular extends BaseController
{
    public function index()
    {
        $session = session();
        
        if (!$session->has('id') || $session->get('nivel') != 9) {
            return redirect()->to(base_url('login'));
        }

        $db = \Config\Database::connect();
        $idUsuario = $session->get('id');

        // 1. Obtener el grupo asignado
        $idGradoTitular = $session->get('nivelT');
        $nombreGrupo = 'Grupo no asignado';

        if (!empty($idGradoTitular)) {
            $gradoRow = $db->table('grados')
                           ->where('Id_grado', $idGradoTitular)
                           ->get()
                           ->getRowArray();
                           
            if ($gradoRow) {
                $nombreGrupo = $gradoRow['nombreGrado'];
            }
        }

        // 2. Obtener el ciclo escolar activo
        $califModel = new CalificacionesModel();
        $config = $califModel->getConfiguracionActiva($session->get('nivel')); 
        $nombreCiclo = 'No definido';

        if (!empty($config['id_ciclo'])) {
            $cicloRow = $db->table('cicloEscolar')
                           ->where('Id_cicloEscolar', $config['id_ciclo'])
                           ->get()
                           ->getRowArray();
                           
            if ($cicloRow) {
                $nombreCiclo = $cicloRow['nombreCicloEscolar'];
            }
        }

        // 3. Obtener la contraseña actual 
        $usuario = $db->table('usr')
                      ->select('pass')
                      ->where('id', $idUsuario)
                      ->get()
                      ->getRowArray();
                      
        $passwordActual = $usuario ? $usuario['pass'] : '';

        $data = [
            'nombre'         => $session->get('nombre'),
            'apellidos'      => $session->get('apellidos'),
            'cicloEscolar'   => $nombreCiclo,
            'grupoAsignado'  => $nombreGrupo,
            'passwordActual' => $passwordActual
        ];

        return view('titulares/dashboard', $data); 
    }
}