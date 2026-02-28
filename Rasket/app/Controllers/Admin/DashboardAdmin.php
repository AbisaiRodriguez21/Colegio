<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CalificacionesModel;

class DashboardAdmin extends BaseController
{
    public function index()
    {
        $session = session();
        $nivel = $session->get('nivel');

        if (!$session->has('id') || $nivel != 1) {
            return redirect()->to(base_url('dashboard'));
        }

        $db = \Config\Database::connect();
        
        $califModel = new CalificacionesModel();
        $config = $califModel->getConfiguracionActiva(1); 
        $nombreCiclo = 'No definido';
        if (!empty($config['id_ciclo'])) {
            $ciclo = $db->table('cicloEscolar')->where('Id_cicloEscolar', $config['id_ciclo'])->get()->getRow();
            $nombreCiclo = $ciclo ? $ciclo->nombreCicloEscolar : 'No definido';
        }

        $grados = $db->table('grados')->orderBy('nivel_grado', 'ASC')->get()->getResultArray();
        
        $data = [
            'nombre'         => $session->get('nombre'),
            'apellidos'      => $session->get('apellidos'),
            'cicloEscolar'   => $nombreCiclo,
            'passwordActual' => $session->get('pass'),
            'kinder'         => [],
            'primaria'       => [],
            'secundaria'     => [],
            'bachillerato'   => []
        ];

        foreach ($grados as $g) {
            $nombre = strtolower($g['nombreGrado']);
            if (strpos($nombre, 'kinder') !== false) {
                $data['kinder'][] = $g;
            } elseif (strpos($nombre, 'primaria') !== false) {
                $data['primaria'][] = $g;
            } elseif (strpos($nombre, 'secundaria') !== false) {
                $data['secundaria'][] = $g;
            } elseif (strpos($nombre, 'bachiller') !== false || strpos($nombre, 'prepa') !== false) {
                $data['bachillerato'][] = $g;
            }
        }

        return view('admin/dashboard', $data);
    }
}