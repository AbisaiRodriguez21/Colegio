<?php namespace App\Models;

use CodeIgniter\Model;

class BoletaModel extends Model
{

    // -------------------------------------------------------------------------
    //     FUNCION PARA OBTENER PROMEDIOS VERTICALES (DE CADA MES)
    // -------------------------------------------------------------------------
    /**
     * Calcula los promedios verticales de un conjunto de materias.
     * Retorna un array con los promedios por mes (1-10) y por periodo (p_t1, p_t2, p_t3).
     */
    public function getPromediosVerticales($materias)
    {
        $sumas = array_fill(1, 10, 0);
        $conteos = array_fill(1, 10, 0);
        
        // Acumuladores para los periodos verticales
        $sum_pt1 = 0; $c_pt1 = 0;
        $sum_pt2 = 0; $c_pt2 = 0;
        $sum_pt3 = 0; $c_pt3 = 0;
        $sum_pf  = 0; $c_pf  = 0;

        foreach ($materias as $mat) {
            $notas = $mat['notas'] ?? [];

            // 1. Sumar Meses Individuales (1 al 10)
            for ($i = 1; $i <= 10; $i++) {
                if (isset($notas[$i]) && is_numeric($notas[$i]) && $notas[$i] > 0) {
                    $sumas[$i] += $notas[$i];
                    $conteos[$i]++;
                }
            }

            // 2. Sumar Promedios de Periodo (Verticales)
            if (($mat['p_t1'] ?? 0) > 0) { $sum_pt1 += $mat['p_t1']; $c_pt1++; }
            if (($mat['p_t2'] ?? 0) > 0) { $sum_pt2 += $mat['p_t2']; $c_pt2++; }
            if (($mat['p_t3'] ?? 0) > 0) { $sum_pt3 += $mat['p_t3']; $c_pt3++; }
            if (($mat['final'] ?? 0) > 0) { $sum_pf  += $mat['final']; $c_pf++; }
        }

        // Calcular resultados finales
        $promedios = [];
        
        // Meses
        for ($i = 1; $i <= 10; $i++) {
            $promedios[$i] = ($conteos[$i] > 0) ? round($sumas[$i] / $conteos[$i], 1) : null;
        }

        // Periodos
        $promedios['p_t1'] = ($c_pt1 > 0) ? round($sum_pt1 / $c_pt1, 1) : null;
        $promedios['p_t2'] = ($c_pt2 > 0) ? round($sum_pt2 / $c_pt2, 1) : null;
        $promedios['p_t3'] = ($c_pt3 > 0) ? round($sum_pt3 / $c_pt3, 1) : null;
        $promedios['final'] = ($c_pf > 0) ? round($sum_pf / $c_pf, 1) : null;

        return $promedios;
    }
    // ==========================================================================
    // ==========================================================================

    // =========================================================================
    // 1. ACCESOS GENERALES Y MENÚ
    // =========================================================================

    /**
     * Obtiene todos los grados para construir el menú lateral.
     */
    public function getGradosMenu()
    {
        return $this->db->table('grados')
            // ->where('grado_activo', 1) 
            ->orderBy('id_grado', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Obtiene la configuración activa (Ciclo y Mes)
     * Se conecta a 'mesycicloactivo' 
     */
    public function getCicloActivo()
    {
        $builder = $this->db->table('mesycicloactivo');
        $builder->select('mesycicloactivo.id_ciclo, cicloescolar.nombreCicloEscolar, mesycicloactivo.id_mes');
        $builder->join('cicloescolar', 'mesycicloactivo.id_ciclo = cicloescolar.id_cicloEscolar');
        $builder->where('mesycicloactivo.id', 1);
        
        return $builder->get()->getRowArray();
    }

    /**
     * Obtiene la información de un grado específico (incluyendo JSONs) - PRIMARIA
     */
    public function getInfoGrado($id_grado) {
        return $this->db->table('grados')
            ->select('id_grado, nombreGrado, nivel_grado, boleta_config, boleta_ing_config') 
            ->where('id_grado', $id_grado)
            ->get()->getRowArray();
    }

    // =========================================================================
    // 2. OBTENCIÓN DE DATOS DEL ALUMNO Y MATERIAS
    // =========================================================================

    /**
     * Obtiene datos del alumno para el encabezado de la boleta
     */
    public function getDatosAlumno($id_usuario)
    {
        return $this->db->table('usr')
            ->select('usr.id, usr.Nombre, usr.ap_Alumno, usr.am_Alumno, usr.matricula, grados.nombreGrado')
            ->join('grados', 'usr.grado = grados.id_grado')
            ->where('usr.id', $id_usuario)
            ->get()->getRowArray();
    }
    
    /**
     * Obtiene lista de alumnos de un grado (Para navegación)
     */
    public function getAlumnosPorGrado($id_grado)
    {
        return $this->db->table('usr')
            ->select('id, generacionactiva, matricula, Nombre, ap_Alumno, am_Alumno, email, activo')
            ->where('grado', $id_grado)
            ->where('activo', 1)
            ->where('generacionactiva', 11)
            ->orderBy('ap_Alumno', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Obtiene las materias de un grado ordenadas
     */
    public function getMaterias($id_grado)
    {
        return $this->db->table('materia')
            ->select('id_materia, nombre_materia, GrupoMat') 
            ->where('id_grados', $id_grado)
            ->orderBy('orden', 'ASC') 
            ->get()->getResultArray();
    }

    /**
     * Obtiene todas las calificaciones del alumno en ese ciclo
     * Devuelve una matriz: [id_materia][id_mes] = calificacion
     */
    public function getCalificaciones($id_usuario, $id_grado, $id_ciclo)
    {
        $rows = $this->db->table('calificacion')
            ->select('id_materia, id_mes, calificacion, cicloEscolar')
            ->where('id_usr', $id_usuario)
            ->where('id_grado', $id_grado)
            ->where('cicloEscolar', $id_ciclo)
            ->get()->getResultArray();

        $matriz = [];
        foreach ($rows as $r) {
            $matriz[$r['id_materia']][$r['id_mes']] = $r['calificacion'];
        }
        return $matriz;
    }

    // =========================================================================
    // 3. LÓGICA DE SECUNDARIA
    // =========================================================================

    public function clasificarSecundaria($materias_procesadas, $id_grado)
    {
        $bloque_academico = [];
        $bloque_talleres = [];

        // 1. OBTENER EL JSON DE CONFIGURACIÓN DE LA BASE DE DATOS
        $gradoInfo = $this->db->table('grados')
                              ->select('boleta_config')
                              ->where('id_grado', $id_grado)
                              ->get()
                              ->getRowArray();
        
        $config = json_decode($gradoInfo['boleta_config'] ?? '{}', true);

        // 2. EXTRAER LOS IDs DE LOS GRUPOS DEL JSON
        $ids_academicas = $config['subject_groups'][0]['subjects'] ?? [];
        $ids_talleres   = $config['subject_groups'][1]['subjects'] ?? [];

        // 3. MAPEAR MATERIAS POR ID PARA ACCESO RÁPIDO
        $mapa_materias = [];
        foreach ($materias_procesadas as $mat) {
            $id = $mat['id_materia'] ?? $mat['id'] ?? 0;
            if ($id > 0) {
                $mapa_materias[$id] = $mat;
            }
        }

        // 4. LLENAR EL BLOQUE ACADÉMICO 
        foreach ($ids_academicas as $id_target) {
            $id = is_array($id_target) ? ($id_target['id'] ?? 0) : $id_target;

            if (isset($mapa_materias[$id])) {
                $bloque_academico[] = $mapa_materias[$id];
            }
        }

        // 5. LLENAR EL BLOQUE TALLERES 
        foreach ($ids_talleres as $id_target) {
            $id = is_array($id_target) ? ($id_target['id'] ?? 0) : $id_target;

            if (isset($mapa_materias[$id])) {
                $bloque_talleres[] = $mapa_materias[$id];
            }
        }

        // FALLBACK DE SEGURIDAD:
        // Si por alguna razón el JSON está vacío o no coincidió nada (ej. error de config),
        // devolvemos todo en "academico" para que la boleta no salga en blanco.
        if (empty($bloque_academico) && empty($bloque_talleres)) {
            $bloque_academico = $materias_procesadas;
        }

        return [
            'academico' => $bloque_academico,
            'talleres'  => $bloque_talleres
        ];
    }

    // =========================================================================
    // 4. LÓGICA DE BACHILLERATO
    // =========================================================================

    /**
     * Procesa las materias de Bachillerato para un semestre específico.
     * Calcula el promedio horizontal (Semestral) basado en los meses solicitados.
     */
    public function procesarBachillerato($materias, $calificaciones, $meses_ids)
    {
        $boleta = [];
        
        // Acumuladores para el promedio vertical FINAL
        $sum_vertical_final = 0;
        $count_vertical_final = 0;

        foreach ($materias as $mat) {
            $id_mat = $mat['id_materia'];
            $notas_materia = $calificaciones[$id_mat] ?? [];
            
            $fila = [
                'nombre' => html_entity_decode($mat['nombre_materia']),
                'notas'  => [], // Aquí guardaremos las 3 notas del semestre
                'promedio' => null,
                'es_taller' => $mat['es_taller'] ?? false // Pasamos la bandera si existe
            ];

            $sum_horiz = 0;
            $count_horiz = 0;

            // Recorremos los 3 meses del semestre (ej: 1, 2, 3)
            foreach ($meses_ids as $id_mes) {
                $val = $notas_materia[$id_mes] ?? null;
                $fila['notas'][] = $val; // Guardamos para la vista

                if (is_numeric($val) && $val > 0) {
                    $sum_horiz += $val;
                    $count_horiz++;
                }
            }

            // Calcular Promedio Semestral (Horizontal)
            if ($count_horiz > 0) {
                // Regla de negocio: Suma / cantidad de notas disponibles
                $fila['promedio'] = round($sum_horiz / $count_horiz, 1);
                
                // Acumular para el vertical
                $sum_vertical_final += $fila['promedio'];
                $count_vertical_final++;
            }

            $boleta[] = $fila;
        }

        // Calcular Promedio General del Semestre (Vertical)
        $promedio_general = ($count_vertical_final > 0) 
            ? round($sum_vertical_final / $count_vertical_final, 1) 
            : null;

        return [
            'materias' => $boleta,
            'promedio_general' => $promedio_general
        ];
    }

    // =========================================================================
    // 5. LÓGICA DE KINDER 
    // =========================================================================
    public function procesarKinder($config_json, $materias_map, $calificaciones)
    {
        // Función anónima auxiliar para procesar una columna (Left o Right)
        $procesarLado = function($ladoData) use ($materias_map, $calificaciones) {
            $grupos_out = [];
            
            if (!isset($ladoData['groups']) || !is_array($ladoData['groups'])) return [];

            foreach ($ladoData['groups'] as $grupo) {
                // Si no tiene materias ni título, saltar
                if (empty($grupo['subjects']) && empty($grupo['title'])) continue;

                $grupo_procesado = [
                    'titulo' => $grupo['title'] ?? '',
                    'materias' => []
                ];

                if (!empty($grupo['subjects'])) {
                    foreach ($grupo['subjects'] as $item) {
                        // El item puede ser un ID (int) o un Objeto con configuración (array)
                        $id_materia = is_array($item) ? ($item['id'] ?? 0) : $item;
                        
                        // Verificar si es un campo calculado (Ej: Inasistencias con fórmula)
                        $is_calculated = is_array($item) && isset($item['calculated']) && $item['calculated'] === true;

                        if (isset($materias_map[$id_materia])) {
                            $mat = $materias_map[$id_materia];
                            $notas = $calificaciones[$id_materia] ?? [];
                            
                            // Si es calculado (Inasistencias), aplicamos la fórmula del JSON
                            if ($is_calculated && isset($item['calculateExpressionsByMonth'])) {
                                foreach ($item['calculateExpressionsByMonth'] as $mes => $formula) {
                                    $valor_capturado = $notas[$mes] ?? 0;
                                    
                                    // Reemplazamos %value% por el valor real
                                    // Ej: "100 - (5 * 100 / 74)"
                                    $math_str = str_replace('%value%', $valor_capturado, $formula);
                                    
                                    // Evaluamos la operación matemática de forma segura
                                    try {
                                        
                                        $resultado = 0;
                                        // Solo permitimos números y operadores básicos
                                        if (preg_match('/^[0-9\+\-\*\/\.\(\)\s]+$/', $math_str)) {
                                            eval("\$resultado = $math_str;");
                                        }
                                        // Redondeamos a enteros para porcentajes
                                        $notas[$mes] = round($resultado); 
                                    } catch (\Exception $e) {
                                        $notas[$mes] = 0;
                                    }
                                }
                                $mat['es_calculado'] = true;
                                $mat['es_porcentaje'] = $grupo['isPercentage'] ?? false;
                            }

                            $mat['notas'] = $notas;
                            $grupo_procesado['materias'][] = $mat;
                        }
                    }
                }
                $grupos_out[] = $grupo_procesado;
            }
            return $grupos_out;
        };

        return [
            'left'  => $procesarLado($config_json['left'] ?? []),
            'right' => $procesarLado($config_json['right'] ?? []),
            'translateType' => $config_json['scoreTranslateType'] ?? 'number'
        ];
    }
}