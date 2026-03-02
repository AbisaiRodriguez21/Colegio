<?php

namespace App\Controllers\Titular;

use App\Controllers\BaseController;

class SabanaController extends BaseController
{
    public function index()
    {
        $session = session();
        $db = \Config\Database::connect();

        // 1. Obtener datos del grupo titular desde la sesión
        $idGrado = $session->get('nivelT'); 
        
        // 2. Consultar qué NIVEL EDUCATIVO es ese grado
        $gradoInfo = $db->table('grados')
                        ->select('nivel_grado, nombreGrado')
                        ->where('id_grado', $idGrado)
                        ->get()->getRow();

        if (!$gradoInfo) {
            return redirect()->back()->with('error', 'No se encontró información del grado.');
        }

        $nivel = $gradoInfo->nivel_grado; 
        
        $periodos = [];
        $tituloPeriodo = "";

        switch ($nivel) {
            case 2:  
                $tituloPeriodo = "Evaluaciones";
                
                $periodos = [
                    ['id' => 1, 'nombre' => '1° Evaluación'],
                    ['id' => 2, 'nombre' => '2° Evaluación'],
                    ['id' => 3, 'nombre' => '3° Evaluación']
                ];
                break;

            case 5: // BACHILLERATO (Tabla 'bimestres')
                $tituloPeriodo = "Bimestres";
                $periodos = $db->table('bimestres')
                               ->select('id, nombre')
                               ->get()->getResultArray();
                break;

            default: // PRIMARIA (3) Y SECUNDARIA (4) -> (Tabla 'mes')
                $tituloPeriodo = "Meses";
                $periodos = $db->table('mes') 
                               ->select('id, nombre') 
                               ->orderBy('id', 'ASC')
                               ->get()->getResultArray();
                break;
        }

        // 4. Cargar la vista en la carpeta 'titulares'
        return view('titulares/selector_periodo', [
            'periodos' => $periodos,
            'titulo'   => $tituloPeriodo,
            'grado'    => $gradoInfo->nombreGrado
        ]);
    }

    // Recibe la selección y manda a la boleta vieja
    public function cargarSabana()
    {
        $idPeriodoSeleccionado = $this->request->getPost('id_periodo');
        
        if (!$idPeriodoSeleccionado) {
            return redirect()->back()->with('error', 'Selecciona un periodo.');
        }

        // Redirigimos al controlador de Boleta existente
        // Pasamos el periodo como parámetro GET (?mes_custom=X)
        return redirect()->to(base_url("titular/hoja-evaluacion?mes_custom=$idPeriodoSeleccionado"));
    }
}