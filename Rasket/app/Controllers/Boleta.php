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
        
        // 1. Obtener Configuración del Grado (JSONs)
        $gradoInfo = $model->getInfoGrado($id_grado);
        
        // Decodificar JSONs (Si son nulos, usar array vacío)
        $config_espanol = json_decode($gradoInfo['boleta_config'] ?? '{}', true);
        $config_ingles  = json_decode($gradoInfo['boleta_ing_config'] ?? '{}', true);

        // 2. Obtener Datos Crudos
        $materias_db = $model->getMaterias($id_grado); 
        $calificaciones = $model->getCalificaciones($id_alumno, $id_grado, $id_ciclo);

        // 3. Indexar Materias por ID para acceso rápido
        // Esto nos permite buscar "Materia ID 5" instantáneamente sin recorrer todo el array
        $materias_map = [];
        foreach ($materias_db as $m) {
            $m['nombre'] = html_entity_decode($m['nombre_materia']); // Normalizar nombre
            $materias_map[$m['id_materia']] = $m;
        }

        // --- NAVEGACIÓN ---
        $listaAlumnos = $model->getAlumnosPorGrado($id_grado);
        $ids = array_column($listaAlumnos, 'id'); 
        $posicion = array_search($id_alumno, $ids);
        $id_anterior = ($posicion !== false && $posicion > 0) ? $ids[$posicion - 1] : null;
        $id_siguiente = ($posicion !== false && $posicion < count($ids) - 1) ? $ids[$posicion + 1] : null;

        // ---------------------------------------------------------------------
        // FUNCIÓN LOCAL PARA PROCESAR SECCIONES DESDE EL JSON
        // ---------------------------------------------------------------------
        $procesarSeccionesDesdeConfig = function($configJson, $materias_map, $calificaciones) {
            $secciones_resultado = [];
            
            // Validar que exista la estructura 'subject_groups'
            if (!isset($configJson['subject_groups']) || !is_array($configJson['subject_groups'])) {
                return [];
            }

            foreach ($configJson['subject_groups'] as $grupo) {
                // Si es solo un divisor o no tiene título/materias, saltar o manejar especial
                if (isset($grupo['divider']) && $grupo['divider'] === true) continue;
                if (empty($grupo['subjects'])) continue;

                $titulo = $grupo['title'] ?? '';
                $materias_del_grupo = [];

                foreach ($grupo['subjects'] as $itemMateria) {
                    // El JSON puede traer solo el ID (ej: 5) o un objeto (ej: {"id": 16, ...})
                    $id_materia = is_array($itemMateria) ? ($itemMateria['id'] ?? 0) : $itemMateria;

                    // Verificar si la materia existe en nuestra BD
                    if (isset($materias_map[$id_materia])) {
                        $mat = $materias_map[$id_materia];
                        $notas = $calificaciones[$id_materia] ?? [];

                        // --- Cálculos Horizontales ---
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

                        // Asignar datos calculados
                        $mat['notas'] = $notas;
                        $mat['p_t1'] = $t1_prom;
                        $mat['p_t2'] = $t2_prom;
                        $mat['p_t3'] = $t3_prom;
                        $mat['final'] = $final;

                        $materias_del_grupo[] = $mat;
                    }
                }

                // Si encontramos materias para este grupo, lo agregamos
                if (!empty($materias_del_grupo)) {
                    $secciones_resultado[] = [
                        'titulo'    => $titulo,
                        'materias'  => $materias_del_grupo,
                        // Calculamos promedios verticales usando tu función auxiliar existente
                        'promedios' => $this->_calcularPromediosVerticalesPrimaria($materias_del_grupo)
                    ];
                }
            }
            return $secciones_resultado;
        };

        // 4. GENERAR ESTRUCTURAS USANDO EL JSON
        $secciones_espanol = $procesarSeccionesDesdeConfig($config_espanol, $materias_map, $calificaciones);
        $secciones_ingles  = $procesarSeccionesDesdeConfig($config_ingles, $materias_map, $calificaciones);

        // 5. ENVIAR A VISTA
        $data = [
            'alumno' => $alumno,
            'ciclo'  => $cicloInfo,
            'id_grado' => $id_grado,
            'id_anterior' => $id_anterior,
            'id_siguiente' => $id_siguiente,
            
            // Datos dinámicos desde JSON
            'secciones_espanol' => $secciones_espanol,
            'secciones_ingles'  => $secciones_ingles,
            
            // Ya no usamos lógica de "extra" o "Sin Clasificar" porque el JSON manda
            'secciones_extra'   => [] 
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
        
        // 1. Obtener Configuración (JSON)
        $gradoInfo = $model->getInfoGrado($id_grado);
        $config_espanol = json_decode($gradoInfo['boleta_config'] ?? '{}', true);
        $config_ingles  = json_decode($gradoInfo['boleta_ing_config'] ?? '{}', true);

        // 2. Obtener Datos y Mapear
        $materias_db = $model->getMaterias($id_grado); 
        $calificaciones = $model->getCalificaciones($id_alumno, $id_grado, $id_ciclo);

        $materias_map = [];
        foreach ($materias_db as $m) {
            $m['nombre'] = html_entity_decode($m['nombre_materia']);
            $materias_map[$m['id_materia']] = $m;
        }

        // --- Navegación ---
        $listaAlumnos = $model->getAlumnosPorGrado($id_grado);
        $ids = array_column($listaAlumnos, 'id'); 
        $posicion = array_search($id_alumno, $ids);
        $id_anterior = ($posicion !== false && $posicion > 0) ? $ids[$posicion - 1] : null;
        $id_siguiente = ($posicion !== false && $posicion < count($ids) - 1) ? $ids[$posicion + 1] : null;

        // ---------------------------------------------------------------------
        // HELPER SECUNDARIA: Procesa secciones y calcula T1, T2, T3
        // ---------------------------------------------------------------------
        $procesarSeccionesSecu = function($configJson, $materias_map, $calificaciones) {
            $secciones = [];
            if (!isset($configJson['subject_groups']) || !is_array($configJson['subject_groups'])) return [];

            foreach ($configJson['subject_groups'] as $grupo) {
                if (isset($grupo['divider']) || empty($grupo['subjects'])) continue;

                $titulo = $grupo['title'] ?? '';
                $materias_grupo = [];

                foreach ($grupo['subjects'] as $itemMateria) {
                    $id_materia = is_array($itemMateria) ? ($itemMateria['id'] ?? 0) : $itemMateria;

                    if (isset($materias_map[$id_materia])) {
                        $mat = $materias_map[$id_materia];
                        $notas = $calificaciones[$id_materia] ?? [];

                        // --- CÁLCULOS SECUNDARIA ---
                        // T1 (Sep-Nov)
                        $t1_sum = ($notas[1]??0)+($notas[2]??0)+($notas[3]??0);
                        $t1_prom = ($t1_sum > 0) ? round($t1_sum/3, 1) : null;
                        
                        // T2 (Dic-Mar - 4 meses)
                        $t2_sum = ($notas[4]??0)+($notas[5]??0)+($notas[6]??0)+($notas[7]??0);
                        $t2_prom = ($t2_sum > 0) ? round($t2_sum/4, 1) : null;

                        // T3 (Abr-Jun)
                        $t3_sum = ($notas[8]??0)+($notas[9]??0)+($notas[10]??0);
                        $t3_prom = ($t3_sum > 0) ? round($t3_sum/3, 1) : null;

                        // Final
                        $final = null; $div=0; $sum=0;
                        if($t1_prom){ $sum+=$t1_prom; $div++; }
                        if($t2_prom){ $sum+=$t2_prom; $div++; }
                        if($t3_prom){ $sum+=$t3_prom; $div++; }
                        if($div>0) $final = round($sum/$div, 1);

                        $mat['notas'] = $notas;
                        $mat['p_t1'] = $t1_prom; $mat['p_t2'] = $t2_prom; $mat['p_t3'] = $t3_prom;
                        $mat['final'] = $final;

                        $materias_grupo[] = $mat;
                    }
                }

                if (!empty($materias_grupo)) {
                    $secciones[] = [
                        'titulo'    => $titulo,
                        'materias'  => $materias_grupo,
                        'promedios' => $this->_calcularPromediosVerticalesSecundaria($materias_grupo)
                    ];
                }
            }
            return $secciones;
        };

        // Procesar datos
        $secciones_espanol = $procesarSeccionesSecu($config_espanol, $materias_map, $calificaciones);
        $secciones_ingles  = $procesarSeccionesSecu($config_ingles, $materias_map, $calificaciones);

        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'id_grado' => $id_grado,
            'id_anterior' => $id_anterior, 'id_siguiente' => $id_siguiente,
            // Enviamos las secciones dinámicas
            'secciones_espanol' => $secciones_espanol,
            'secciones_ingles'  => $secciones_ingles
        ];

        return view('boletas/ver_boleta_secundaria', $data);
    }

    // Auxiliar Secundaria: Promedios Verticales
    private function _calcularPromediosVerticalesSecundaria($materias)
    {
        $sumas = array_fill(1, 10, 0); $counts = array_fill(1, 10, 0);
        $sv_t1=0; $cv_t1=0; $sv_t2=0; $cv_t2=0; $sv_t3=0; $cv_t3=0; $sv_f=0; $cv_f=0;

        foreach ($materias as $m) {
            for ($i=1; $i<=10; $i++) {
                if (($m['notas'][$i]??0) > 0) { $sumas[$i]+=$m['notas'][$i]; $counts[$i]++; }
            }
            if (($m['p_t1']??0)>0) { $sv_t1+=$m['p_t1']; $cv_t1++; }
            if (($m['p_t2']??0)>0) { $sv_t2+=$m['p_t2']; $cv_t2++; }
            if (($m['p_t3']??0)>0) { $sv_t3+=$m['p_t3']; $cv_t3++; }
            if (($m['final']??0)>0) { $sv_f+=$m['final']; $cv_f++; }
        }

        $res = [];
        for ($i=1; $i<=10; $i++) $res[$i] = ($counts[$i]>0) ? round($sumas[$i]/$counts[$i], 1) : null;
        $res['p_t1'] = ($cv_t1>0) ? round($sv_t1/$cv_t1, 1) : null;
        $res['p_t2'] = ($cv_t2>0) ? round($sv_t2/$cv_t2, 1) : null;
        $res['p_t3'] = ($cv_t3>0) ? round($sv_t3/$cv_t3, 1) : null;
        $res['final'] = ($cv_f>0) ? round($sv_f/$cv_f, 1) : null;
        return $res;
    }

    // =========================================================================
    // 4. MÓDULO BACHILLERATO
    // =========================================================================

    private function _procesarBachiller($model, $id_grado, $id_alumno, $alumno, $cicloInfo)
    {
        $id_ciclo = $cicloInfo['id_ciclo'];
        $semestre_actual = $this->request->getGet('semestre') ?? 1;

        // Configurar Semestre
        if ($semestre_actual == 2) {
            $meses_ids = [4, 5, 6]; 
            $headers = ['FEB-MAR', 'ABR-MAY', 'JUN-JUL'];
            $link_otro = base_url("boleta/ver/$id_grado/$id_alumno?semestre=1");
            $txt_btn = "Ver boleta de 1er semestre";
            $cls_btn = "btn-semestre-azul";
        } else {
            $meses_ids = [1, 2, 3]; 
            $headers = ['AGO-SEP', 'OCT-NOV', 'DIC-ENE'];
            $link_otro = base_url("boleta/ver/$id_grado/$id_alumno?semestre=2");
            $txt_btn = "Ver boleta de 2do semestre";
            $cls_btn = "btn-semestre-verde";
        }

        // 1. Obtener Config (JSON)
        $gradoInfo = $model->getInfoGrado($id_grado);
        $config_json = json_decode($gradoInfo['boleta_config'] ?? '{}', true);

        // 2. Datos y Mapeo
        $materias_db = $model->getMaterias($id_grado);
        $calificaciones = $model->getCalificaciones($id_alumno, $id_grado, $id_ciclo);
        
        $materias_map = [];
        foreach ($materias_db as $m) {
            $m['nombre'] = html_entity_decode($m['nombre_materia']);
            $materias_map[$m['id_materia']] = $m;
        }

        // Navegación
        $listaAlumnos = $model->getAlumnosPorGrado($id_grado);
        $ids = array_column($listaAlumnos, 'id'); 
        $posicion = array_search($id_alumno, $ids);
        $id_anterior = ($posicion !== false && $posicion > 0) ? $ids[$posicion - 1] : null;
        $id_siguiente = ($posicion !== false && $posicion < count($ids) - 1) ? $ids[$posicion + 1] : null;

        // ---------------------------------------------------------------------
        // HELPER BACHILLERATO (CAPAZ DE LEER LISTAS PLANAS Y GRUPOS)
        // ---------------------------------------------------------------------
        $procesarSeccionesBach = function($configJson, $materias_map, $calificaciones) use ($meses_ids) {
            $secciones_out = [];
            $total_sum_semestre = 0;
            $total_count_semestre = 0;

            // Paso A: Normalizar la estructura. 
            // Si viene "subjects" directo (Bachillerato), lo convertimos a un solo grupo sin título.
            // Si viene "subject_groups" (Estilo nuevo), lo usamos tal cual.
            $lista_grupos = [];

            if (isset($configJson['subject_groups'])) {
                $lista_grupos = $configJson['subject_groups'];
            } elseif (isset($configJson['subjects'])) {
                // Caso Bachillerato actual: Lista plana
                $lista_grupos[] = [
                    'title' => '', // Sin título
                    'subjects' => $configJson['subjects']
                ];
            }

            // Paso B: Procesar grupos
            foreach ($lista_grupos as $grupo) {
                if (isset($grupo['divider']) || empty($grupo['subjects'])) continue;

                $titulo = $grupo['title'] ?? '';
                $materias_grupo = [];

                foreach ($grupo['subjects'] as $itemMateria) {
                    // El JSON de bachiller trae objetos: {"id": 472, "bgcolor": "92CDDC"}
                    $id_materia = is_array($itemMateria) ? ($itemMateria['id'] ?? 0) : $itemMateria;
                    
                    // Detectar color para saber si es Taller
                    $es_taller = false;
                    if (is_array($itemMateria) && !empty($itemMateria['bgcolor'])) {
                        $es_taller = true;
                    }

                    if (isset($materias_map[$id_materia])) {
                        $mat = $materias_map[$id_materia];
                        $notas_materia = $calificaciones[$id_materia] ?? [];

                        $mat['notas'] = []; 
                        $sum_horiz = 0;
                        $count_horiz = 0;

                        // Recorrer los 3 meses del semestre
                        foreach ($meses_ids as $id_mes) {
                            $val = $notas_materia[$id_mes] ?? null;
                            $mat['notas'][] = $val;
                            if (is_numeric($val) && $val > 0) {
                                $sum_horiz += $val;
                                $count_horiz++;
                            }
                        }

                        // Promedio Semestral
                        $mat['promedio'] = ($count_horiz > 0) ? round($sum_horiz/$count_horiz, 1) : null;
                        
                        // Acumular para el Global
                        if ($mat['promedio']) {
                            $total_sum_semestre += $mat['promedio'];
                            $total_count_semestre++;
                        }

                        // Asignar la bandera de taller detectada en el JSON
                        $mat['es_taller'] = $es_taller; 

                        $materias_grupo[] = $mat;
                    }
                }

                if (!empty($materias_grupo)) {
                    $secciones_out[] = [
                        'titulo' => $titulo,
                        'materias' => $materias_grupo
                    ];
                }
            }
            
            // Promedio General Semestral
            $promedio_general = ($total_count_semestre > 0) ? round($total_sum_semestre/$total_count_semestre, 1) : null;

            return ['secciones' => $secciones_out, 'promedio_general' => $promedio_general];
        };

        // Ejecutar procesamiento
        $resultado = $procesarSeccionesBach($config_json, $materias_map, $calificaciones);

        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'id_grado' => $id_grado,
            'id_anterior' => $id_anterior, 'id_siguiente'=> $id_siguiente,
            
            // Datos dinámicos (Ahora sí definidos correctamente)
            'secciones'   => $resultado['secciones'],
            'prom_gral'   => $resultado['promedio_general'],
            
            'headers'     => $headers,
            'semestre_actual' => $semestre_actual,
            'link_otro_semestre' => $link_otro,
            'texto_boton' => $txt_btn,
            'clase_boton' => $cls_btn
        ];

        return view('boletas/ver_boleta_bachillerato', $data);
    }
}