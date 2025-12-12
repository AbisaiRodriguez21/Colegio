<?php namespace App\Controllers;

use App\Models\BoletaModel;

class Boleta extends BaseController
{
    // =========================================================================
    // 1. ACCESOS PÚBLICOS Y ENRUTAMIENTO (GENERAL)
    // =========================================================================

    // Pantalla 1: Lista de Alumnos por Grado
    public function lista_alumnos($id_grado)
    {
        $model = new BoletaModel();
        $data['alumnos'] = $model->getAlumnosPorGrado($id_grado);
        $data['grado']   = $model->getInfoGrado($id_grado);
        $data['id_grado'] = $id_grado;
        return view('boletas/lista_alumnos', $data);
    }

    // Pantalla 2: Ver Boleta (Switch Maestro)
    public function ver($id_grado, $id_alumno)
    {
        $model = new BoletaModel();
        
        // Datos básicos generales
        $alumno = $model->getDatosAlumno($id_alumno);
        $cicloInfo = $model->getCicloActivo();
        
        if (!$cicloInfo) { die("Error: No hay ciclo escolar activo configurado."); }

        $nombreGrado = strtolower($alumno['nombreGrado']);

        // --- ENRUTAMIENTO SEGÚN NIVEL EDUCATIVO ---
        
        // A) Secundaria
        if (strpos($nombreGrado, 'secundaria') !== false) {
            return $this->_procesarSecundaria($model, $id_grado, $id_alumno, $alumno, $cicloInfo);
        }
        // B) Bachillerato
        elseif (strpos($nombreGrado, 'bachiller') !== false || strpos($nombreGrado, 'prepa') !== false) {
            return $this->_procesarBachiller($model, $id_grado, $id_alumno, $alumno, $cicloInfo);
        }
        // C) Kinder (Pendiente)
        elseif (strpos($nombreGrado, 'kinder') !== false || strpos($nombreGrado, 'maternal') !== false) {
            die("Módulo de Kinder en construcción");
        }
        // D) Primaria (Default)
        else {
            return $this->_procesarPrimaria($model, $id_grado, $id_alumno, $alumno, $cicloInfo);
        }
    }

    // =========================================================================
    // 2. MÓDULO PRIMARIA
    // =========================================================================

    private function _procesarPrimaria($model, $id_grado, $id_alumno, $alumno, $cicloInfo)
    {
        $id_ciclo = $cicloInfo['id_ciclo'];
        $materias = $model->getMaterias($id_grado); 
        $calificaciones = $model->getCalificaciones($id_alumno, $id_grado, $id_ciclo);

        // Navegación
        $listaAlumnos = $model->getAlumnosPorGrado($id_grado);
        $ids = array_column($listaAlumnos, 'id'); 
        $posicion = array_search($id_alumno, $ids);
        $id_anterior = ($posicion !== false && $posicion > 0) ? $ids[$posicion - 1] : null;
        $id_siguiente = ($posicion !== false && $posicion < count($ids) - 1) ? $ids[$posicion + 1] : null;

        // A. Procesar Materias (Horizontal)
        $materias_procesadas = [];
        foreach ($materias as $mat) {
            $id_mat = $mat['id_materia'];
            $notas = $calificaciones[$id_mat] ?? []; 

            $t1_suma = ($notas[1]??0) + ($notas[2]??0) + ($notas[3]??0);
            $t1_prom = ($t1_suma > 0) ? round($t1_suma / 3, 1) : null;

            $t2_suma = ($notas[4]??0) + ($notas[5]??0) + ($notas[6]??0) + ($notas[7]??0);
            $t2_prom = ($t2_suma > 0) ? round($t2_suma / 4, 1) : null;

            $t3_suma = ($notas[8]??0) + ($notas[9]??0) + ($notas[10]??0);
            $t3_prom = ($t3_suma > 0) ? round($t3_suma / 3, 1) : null;

            $final = null;
            $divisor = 0; $acumulado = 0;
            if($t1_prom > 0) { $acumulado += $t1_prom; $divisor++; }
            if($t2_prom > 0) { $acumulado += $t2_prom; $divisor++; }
            if($t3_prom > 0) { $acumulado += $t3_prom; $divisor++; }
            if ($divisor > 0) { $final = round($acumulado / $divisor, 1); }

            // Corrección clave para la vista
            $mat['nombre'] = html_entity_decode($mat['nombre_materia'] ?? ''); 
            $mat['notas'] = $notas;
            $mat['p_t1'] = $t1_prom;
            $mat['p_t2'] = $t2_prom;
            $mat['p_t3'] = $t3_prom;
            $mat['final'] = $final;
            
            $materias_procesadas[] = $mat;
        }

        // B. Clasificar (Buckets)
        $buckets = [
            'LC' => [], 'PM' => [], 'ENS' => [], 'DP' => [], 'HP' => [],
            'ENG' => [], 'TF' => [], 'OTRO' => []
        ];

        foreach ($materias_procesadas as $row) {
            $grupo = strtoupper(trim($row['GrupoMat'] ?? 'OTRO'));
            if (in_array($grupo, ['LC'])) $buckets['LC'][] = $row;
            elseif (in_array($grupo, ['PM'])) $buckets['PM'][] = $row;
            elseif (in_array($grupo, ['ENS'])) $buckets['ENS'][] = $row;
            elseif (in_array($grupo, ['DP'])) $buckets['DP'][] = $row;
            elseif (in_array($grupo, ['HP'])) $buckets['HP'][] = $row;
            elseif (in_array($grupo, ['ENG', 'ENG2'])) $buckets['ENG'][] = $row;
            elseif (in_array($grupo, ['TF', 'AC'])) $buckets['TF'][] = $row;
            else $buckets['OTRO'][] = $row;
        }

        // C. Construir Secciones
        $secciones = [];
        $addSection = function($titulo, $rows) use (&$secciones) {
            if (!empty($rows)) {
                $secciones[] = [
                    'titulo'    => $titulo,
                    'materias'  => $rows,
                    'promedios' => $this->_calcularPromediosVerticalesPrimaria($rows)
                ];
            }
        };

        $addSection("LENGUAJE Y COMUNICACIÓN", $buckets['LC']);
        $addSection("PENSAMIENTO MATEMÁTICO", $buckets['PM']);
        $addSection("EXPLORACIÓN Y COMPRENSIÓN DEL MUNDO NATURAL Y SOCIAL", $buckets['ENS']);
        $addSection("DESARROLLO PERSONAL Y PARA LA CONVIVENCIA", $buckets['DP']);
        $addSection("HÁBITOS PERSONALES", $buckets['HP']);
        
        $secciones_ingles = [];
        if (!empty($buckets['ENG'])) {
            $secciones_ingles[] = ['titulo' => "REPORT CARD", 'materias' => $buckets['ENG'], 'promedios' => $this->_calcularPromediosVerticalesPrimaria($buckets['ENG'])];
        }
        if (!empty($buckets['TF'])) {
            $secciones_ingles[] = ['titulo' => "TALLER FORMATIVO", 'materias' => $buckets['TF'], 'promedios' => $this->_calcularPromediosVerticalesPrimaria($buckets['TF'])];
        }

        $secciones_extra = [];
        if (!empty($buckets['OTRO'])) {
             $secciones_extra[] = ['titulo' => "OTRAS ASIGNATURAS", 'materias' => $buckets['OTRO'], 'promedios' => $this->_calcularPromediosVerticalesPrimaria($buckets['OTRO'])];
        }

        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'id_grado' => $id_grado,
            'id_anterior' => $id_anterior, 'id_siguiente' => $id_siguiente,
            'secciones_espanol' => $secciones,
            'secciones_ingles'  => $secciones_ingles,
            'secciones_extra'   => $secciones_extra
        ];

        return view('boletas/ver_boleta_primaria', $data);
    }

    // Auxiliar Primaria: Promedios Verticales (Fila Gris)
    private function _calcularPromediosVerticalesPrimaria($materias)
    {
        $sumas = array_fill(1, 10, 0); $counts = array_fill(1, 10, 0);  
        $sum_p1=0; $c_p1=0; $sum_p2=0; $c_p2=0; $sum_p3=0; $c_p3=0; $sum_fin=0; $c_fin=0;

        foreach ($materias as $m) {
            for ($i=1; $i<=10; $i++) {
                $val = $m['notas'][$i] ?? 0;
                if ($val > 0) { $sumas[$i] += $val; $counts[$i]++; }
            }
            if (($m['p_t1']??0) > 0) { $sum_p1 += $m['p_t1']; $c_p1++; }
            if (($m['p_t2']??0) > 0) { $sum_p2 += $m['p_t2']; $c_p2++; }
            if (($m['p_t3']??0) > 0) { $sum_p3 += $m['p_t3']; $c_p3++; }
            if (($m['final']??0) > 0) { $sum_fin += $m['final']; $c_fin++; }
        }

        $promedios = [];
        for ($i=1; $i<=10; $i++) { $promedios[$i] = ($counts[$i]>0) ? round($sumas[$i]/$counts[$i], 1) : ''; }
        $promedios['p_t1'] = ($c_p1>0) ? round($sum_p1/$c_p1, 1) : '';
        $promedios['p_t2'] = ($c_p2>0) ? round($sum_p2/$c_p2, 1) : '';
        $promedios['p_t3'] = ($c_p3>0) ? round($sum_p3/$c_p3, 1) : '';
        $promedios['final'] = ($c_fin>0) ? round($sum_fin/$c_fin, 1) : '';

        return $promedios;
    }

    // =========================================================================
    // 3. MÓDULO SECUNDARIA
    // =========================================================================

    private function _procesarSecundaria($model, $id_grado, $id_alumno, $alumno, $cicloInfo)
    {
        $id_ciclo = $cicloInfo['id_ciclo'];
        $materias = $model->getMaterias($id_grado); 
        $calificaciones = $model->getCalificaciones($id_alumno, $id_grado, $id_ciclo);

        // Navegación
        $listaAlumnos = $model->getAlumnosPorGrado($id_grado);
        $ids = array_column($listaAlumnos, 'id'); 
        $posicion = array_search($id_alumno, $ids);
        $id_anterior = ($posicion !== false && $posicion > 0) ? $ids[$posicion - 1] : null;
        $id_siguiente = ($posicion !== false && $posicion < count($ids) - 1) ? $ids[$posicion + 1] : null;

        // A. Procesar Materias (Horizontal)
        $materias_procesadas = [];
        foreach ($materias as $mat) {
            $id_mat = $mat['id_materia'];
            $notas = $calificaciones[$id_mat] ?? []; 

            $t1_suma = ($notas[1]??0) + ($notas[2]??0) + ($notas[3]??0);
            $t1_prom = ($t1_suma > 0) ? round($t1_suma / 3, 1) : null;

            $t2_suma = ($notas[4]??0) + ($notas[5]??0) + ($notas[6]??0) + ($notas[7]??0);
            $t2_prom = ($t2_suma > 0) ? round($t2_suma / 4, 1) : null;

            $t3_suma = ($notas[8]??0) + ($notas[9]??0) + ($notas[10]??0);
            $t3_prom = ($t3_suma > 0) ? round($t3_suma / 3, 1) : null;

            $final = null;
            $div = 0; $sum = 0;
            if($t1_prom) { $sum += $t1_prom; $div++; }
            if($t2_prom) { $sum += $t2_prom; $div++; }
            if($t3_prom) { $sum += $t3_prom; $div++; }
            if($div > 0) { $final = round($sum / $div, 1); }

            $mat['notas'] = $notas;
            $mat['p_t1'] = $t1_prom; $mat['p_t2'] = $t2_prom; $mat['p_t3'] = $t3_prom; $mat['final'] = $final;
            $materias_procesadas[] = $mat;
        }

        // B. Clasificar y C. Calcular Promedios
        $bloques = $model->clasificarSecundaria($materias_procesadas);
        $prom_academico = $this->_calcularPromediosBloqueSecundaria($bloques['academico']);
        $prom_talleres  = $this->_calcularPromediosBloqueSecundaria($bloques['talleres']);

        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'id_grado' => $id_grado,
            'id_anterior' => $id_anterior, 'id_siguiente' => $id_siguiente,
            'bloque_academico' => $bloques['academico'],
            'bloque_talleres'  => $bloques['talleres'],
            'promedios_academico' => $prom_academico,
            'promedios_talleres'  => $prom_talleres
        ];

        return view('boletas/ver_boleta_secundaria', $data);
    }

    // Auxiliar Secundaria: Promedios por Bloque
    private function _calcularPromediosBloqueSecundaria($lista_materias)
    {
        if (empty($lista_materias)) return [];

        $sumas_meses = array_fill(1, 10, 0);
        $conteos_meses = array_fill(1, 10, 0);
        $sum_v_t1 = 0; $count_v_t1 = 0;
        $sum_v_t2 = 0; $count_v_t2 = 0;
        $sum_v_t3 = 0; $count_v_t3 = 0;
        $sum_v_final = 0; $count_v_final = 0;

        foreach ($lista_materias as $mat) {
            $notas = $mat['notas'] ?? [];
            for ($m = 1; $m <= 10; $m++) {
                if (isset($notas[$m]) && is_numeric($notas[$m]) && $notas[$m] > 0) {
                    $sumas_meses[$m] += $notas[$m];
                    $conteos_meses[$m]++;
                }
            }
            if (($mat['p_t1']??0) > 0) { $sum_v_t1 += $mat['p_t1']; $count_v_t1++; }
            if (($mat['p_t2']??0) > 0) { $sum_v_t2 += $mat['p_t2']; $count_v_t2++; }
            if (($mat['p_t3']??0) > 0) { $sum_v_t3 += $mat['p_t3']; $count_v_t3++; }
            if (($mat['final']??0) > 0) { $sum_v_final += $mat['final']; $count_v_final++; }
        }

        $resultados = [];
        for ($m = 1; $m <= 10; $m++) {
            $resultados[$m] = ($conteos_meses[$m] > 0) ? round($sumas_meses[$m] / $conteos_meses[$m], 1) : null;
        }
        $resultados['p_t1']  = ($count_v_t1 > 0) ? round($sum_v_t1 / $count_v_t1, 1) : null;
        $resultados['p_t2']  = ($count_v_t2 > 0) ? round($sum_v_t2 / $count_v_t2, 1) : null;
        $resultados['p_t3']  = ($count_v_t3 > 0) ? round($sum_v_t3 / $count_v_t3, 1) : null;
        $resultados['final'] = ($count_v_final > 0) ? round($sum_v_final / $count_v_final, 1) : null;

        return $resultados;
    }

    // =========================================================================
    // 4. MÓDULO BACHILLERATO
    // =========================================================================

    private function _procesarBachiller($model, $id_grado, $id_alumno, $alumno, $cicloInfo)
    {
        $id_ciclo = $cicloInfo['id_ciclo'];
        
        // 1. Detectar Semestre
        $semestre_actual = $this->request->getGet('semestre') ?? 1;
        
        if ($semestre_actual == 2) {
            $meses_ids = [4, 5, 6]; 
            $headers   = ['FEB-MAR', 'ABR-MAY', 'JUN-JUL'];
            $link_otro_semestre = base_url("boleta/ver/$id_grado/$id_alumno?semestre=1");
            $texto_boton = "Ver boleta de 1er semestre";
            $clase_boton = "btn-semestre-azul";
        } else {
            $meses_ids = [1, 2, 3]; 
            $headers   = ['AGO-SEP', 'OCT-NOV', 'DIC-ENE'];
            $link_otro_semestre = base_url("boleta/ver/$id_grado/$id_alumno?semestre=2");
            $texto_boton = "Ver boleta de 2do semestre";
            $clase_boton = "btn-semestre-verde";
        }

        // 2. Obtener Datos
        $materias_raw = $model->getMaterias($id_grado);
        $calificaciones = $model->getCalificaciones($id_alumno, $id_grado, $id_ciclo);
        
        // Navegación
        $listaAlumnos = $model->getAlumnosPorGrado($id_grado);
        $ids = array_column($listaAlumnos, 'id'); 
        $posicion = array_search($id_alumno, $ids);
        $id_anterior = ($posicion !== false && $posicion > 0) ? $ids[$posicion - 1] : null;
        $id_siguiente = ($posicion !== false && $posicion < count($ids) - 1) ? $ids[$posicion + 1] : null;

        // 3. Filtrado y Ordenamiento
        $blacklist = [
            'METODOLOGIA Y TALLER DE INVESTIGACIÓN / ORIENTACIÓN EDUCATIVA',
            'METODOLOGIA Y TALLER DE INVESTIGACI√ìN / ORIENTACIÓN EDUCATIVA',
            'USE OF ENGLISH', 'READING'
        ];
        $talleres_list = [
            'TKD/ DANZA', 'NATACIÓN', 'CRITICAL THINKING I / II', 
            'ARTES I / II', 'TALLER MUN / LEADERSHIP & BUSINESS'
        ];

        $materias_academicas = [];
        $materias_talleres   = [];

        foreach ($materias_raw as $mat) {
            $nombre_raw = $mat['nombre_materia'] ?? $mat['nombre'] ?? '';
            $nombre_limpio = trim(html_entity_decode($nombre_raw));
            
            if (in_array($nombre_limpio, $blacklist)) { continue; }

            if (in_array($nombre_limpio, $talleres_list)) {
                $mat['es_taller'] = true; 
                $materias_talleres[] = $mat;
            } else {
                $mat['es_taller'] = false;
                $materias_academicas[] = $mat;
            }
        }
        $materias_finales = array_merge($materias_academicas, $materias_talleres);

        // 4. Procesar Datos (Model)
        $resultado = $model->procesarBachillerato($materias_finales, $calificaciones, $meses_ids);

        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'id_grado' => $id_grado,
            'id_anterior' => $id_anterior, 'id_siguiente'=> $id_siguiente,
            'boleta'      => $resultado['materias'],
            'prom_gral'   => $resultado['promedio_general'],
            'headers'     => $headers,
            'semestre_actual' => $semestre_actual,
            'link_otro_semestre' => $link_otro_semestre,
            'texto_boton' => $texto_boton,
            'clase_boton' => $clase_boton
        ];

        return view('boletas/ver_boleta_bachillerato', $data);
    }
}