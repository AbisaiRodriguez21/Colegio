<?php namespace App\Controllers;

use App\Models\BoletaModel;

class Boleta extends BaseController
{
    public function ver($id_grado)
    {
        $model = new BoletaModel();
        
        // A. OBTENER CONFIGURACIÓN INICIAL
        $cicloInfo = $model->getCicloActivo();
        
        // Validación por si la base de datos de ciclos está vacía
        if (!$cicloInfo) {
            echo "Error: No se encontró configuración en la tabla 'mesycicloactivo'. Revisa tu BD.";
            die();
        }
        
        $id_ciclo = $cicloInfo['id_ciclo']; 

        // B. DEFINIR ALUMNO (Hardcodeado temporalmente para pruebas)
        // Elías Alonso Álvarez García (Según tu imagen)
        $id_alumno = 1597; 

        // C. OBTENER DATOS DE LA BD
        $alumno = $model->getDatosAlumno($id_alumno);
        $materias = $model->getMaterias($id_grado);
        $calificaciones = $model->getCalificaciones($id_alumno, $id_grado, $id_ciclo);

        // D. CÁLCULO DE PROMEDIOS (Lógica de Negocio)
        $boleta_lista = [];

        foreach ($materias as $mat) {
            $id_mat = $mat['Id_materia'];
            // Obtenemos las notas de esta materia o un array vacío si no tiene
            $notas = $calificaciones[$id_mat] ?? []; 

            /* -----------------------------------------------------------
               LÓGICA DE TRIMESTRES (Basada en tu JS antiguo)
               Asumiendo IDs de meses: 
               1=Sep, 2=Oct, 3=Nov (T1)
               4=Dic, 5=Ene, 6=Feb (T2)
               7=Mar, 8=Abr, 9=May, 10=Jun (T3)
            ----------------------------------------------------------- */

            // --- TRIMESTRE 1 ---
            $t1_suma = ($notas[1]??0) + ($notas[2]??0) + ($notas[3]??0);
            // Dividimos entre 3 solo si hay calificaciones, o ajusta la lógica según reglamento
            $t1_prom = ($t1_suma > 0) ? round($t1_suma / 3, 1) : 0;

            // --- TRIMESTRE 2 ---
            $t2_suma = ($notas[4]??0) + ($notas[5]??0) + ($notas[6]??0);
            $t2_prom = ($t2_suma > 0) ? round($t2_suma / 3, 1) : 0;

            // --- TRIMESTRE 3 ---
            $t3_suma = ($notas[7]??0) + ($notas[8]??0) + ($notas[9]??0) + ($notas[10]??0);
            $t3_div = 4; // Son 4 meses
            $t3_prom = ($t3_suma > 0) ? round($t3_suma / $t3_div, 1) : 0;

            // --- PROMEDIO FINAL ---
            // Suma de los 3 promedios trimestrales / 3
            $final = 0;
            if ($t1_prom > 0 && $t2_prom > 0 && $t3_prom > 0) {
                $final = round(($t1_prom + $t2_prom + $t3_prom) / 3, 1);
            }

            // Guardamos la fila procesada lista para la vista
            $boleta_lista[] = [
                'nombre' => $mat['nombre_materia'],
                'notas'  => $notas, // Array crudo para pintar celdas individuales
                'p_t1'   => $t1_prom,
                'p_t2'   => $t2_prom,
                'p_t3'   => $t3_prom,
                'final'  => $final
            ];
        }

        // E. PREPARAR DATOS PARA LA VISTA
        $data = [
            'alumno' => $alumno,
            'ciclo'  => $cicloInfo,
            'boleta' => $boleta_lista
        ];

        return view('boletas/ver_boleta_primaria', $data);
    }
    
    // Aquí irán después las funciones calificar() y calificar_todo()
    public function calificar($id) { echo "En construcción..."; }
    public function calificar_todo($id) { echo "En construcción..."; }
}