<?php

namespace App\Controllers\Director;

use App\Controllers\BaseController;
use App\Models\CalificacionesModel;

class DashboardDirector extends BaseController
{
    public function index()
    {
        $session = session();
        $idUsuario = $session->get('id');
        
        $db = \Config\Database::connect();
        
        $califModel = new CalificacionesModel();
        $config = $califModel->getConfiguracionActiva(1); 
        $nombreCiclo = 'No definido';
        if (!empty($config['id_ciclo'])) {
            $ciclo = $db->table('cicloescolar')->where('Id_cicloEscolar', $config['id_ciclo'])->get()->getRow();
            $nombreCiclo = $ciclo ? $ciclo->nombreCicloEscolar : 'No definido';
        }

        $usuario = $db->table('usr')->select('pass')->where('id', $idUsuario)->get()->getRowArray();
        $passwordReal = $usuario ? $usuario['pass'] : '';

        $grados = $db->table('grados')->orderBy('nivel_grado', 'ASC')->get()->getResultArray();
        
        $data = [
            'nombre'         => $session->get('nombre'),
            'apellidos'      => $session->get('apellidos'),
            'cicloEscolar'   => $nombreCiclo,
            'passwordActual' => $passwordReal,
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

        return view('director/dashboard', $data);
    }

    // =======================================================
    // SELECTOR DE PERIODOS
    // =======================================================
    
    public function seleccionarPeriodo($id_grado)
    {
        $db = \Config\Database::connect();

        // 1. Consultar qué NIVEL EDUCATIVO es ese grado
        $gradoInfo = $db->table('grados')
                        ->select('nivel_grado, nombreGrado')
                        ->where('id_grado', $id_grado)
                        ->get()->getRow();

        if (!$gradoInfo) {
            return redirect()->back()->with('error', 'No se encontró información del grado.');
        }

        $nivel = $gradoInfo->nivel_grado; 
        
        $periodos = [];
        $tituloPeriodo = "";

        switch ($nivel) {
            case 2: // KINDER
                $tituloPeriodo = "Evaluaciones";
                $periodos = [
                    ['id' => 1, 'nombre' => '1° Evaluación'],
                    ['id' => 2, 'nombre' => '2° Evaluación'],
                    ['id' => 3, 'nombre' => '3° Evaluación']
                ];
                break;

            case 5: // BACHILLERATO
                $tituloPeriodo = "Bimestres";
                $periodos = $db->table('bimestres')->select('id, nombre')->get()->getResultArray();
                break;

            default: // PRIMARIA Y SECUNDARIA
                $tituloPeriodo = "Meses";
                $periodos = $db->table('mes')->select('id, nombre')->orderBy('id', 'ASC')->get()->getResultArray();
                break;
        }

        return view('titulares/selector_periodo', [
            'periodos'   => $periodos,
            'titulo'     => $tituloPeriodo,
            'grado'      => $gradoInfo->nombreGrado,
            
            'id_grado'   => $id_grado, 
            'action_url' => base_url('director/abrir-sabana'),
            'cancel_url' => base_url('director/dashboard')
        ]);
    }

    public function abrirSabana()
    {
        $id_periodo = $this->request->getPost('id_periodo');
        $id_grado   = $this->request->getPost('id_grado');

        if (!$id_periodo || !$id_grado) {
            return redirect()->back()->with('error', 'Por favor selecciona un periodo.');
        }

        // Redirigimos a la ruta compartida pasando AMBOS parámetros
        return redirect()->to(base_url("boleta/calificar/{$id_grado}/{$id_periodo}"));
    }
}