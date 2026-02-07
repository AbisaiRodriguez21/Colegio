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
        $idGrado = $session->get('nivelT'); // ID del grado (ej: 21)
        
        // 2. Consultar qué NIVEL EDUCATIVO es ese grado
        // (Esto es vital para saber si mostramos Meses, Bimestres o Evaluaciones)
        $gradoInfo = $db->table('grados')
                        ->select('nivel_grado, nombreGrado')
                        ->where('id_grado', $idGrado)
                        ->get()->getRow();

        if (!$gradoInfo) {
            return redirect()->back()->with('error', 'No se encontró información del grado.');
        }

        $nivel = $gradoInfo->nivel_grado; // 1=Kinder, 2=Primaria, 5=Bachiller
        
        // 3. Lógica "Inteligente" para definir los periodos
        $periodos = [];
        $tituloPeriodo = "";

        switch ($nivel) {
            case 1: // KINDER (Según tu imagen: 1°, 2°, 3°)
            case 20: // (A veces Kinder tiene otro ID de nivel, ajusta si es necesario)
                $tituloPeriodo = "Evaluaciones";
                
                // Si tienes tabla 'momentos', úsala. Si no, usamos este arreglo fijo:
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

            default: // PRIMARIA (2) Y SECUNDARIA (3) -> (Tabla 'mes')
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