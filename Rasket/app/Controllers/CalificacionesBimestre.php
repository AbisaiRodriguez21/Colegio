<?php namespace App\Controllers;

use App\Models\CalificacionesBimestreModel;
use App\Models\BoletaModel; 

class CalificacionesBimestre extends BaseController
{
    // -------------------------------------------------------------------------
    // 1. LISTA DE ALUMNOS
    // -------------------------------------------------------------------------
    public function lista($id_grado)
    {
        $session = session();
        if (!$session->has('id')) { return redirect()->to('/login'); }

        $model = new BoletaModel();
        $grado = $model->getInfoGrado($id_grado);
        $alumnos = $model->getAlumnosPorGrado($id_grado);

        $data = ['alumnos' => $alumnos, 'grado' => $grado];
        return view('boletas/lista_alumnos_bimestre', $data);
    }

    // -------------------------------------------------------------------------
    // 2. DIRECTOR DE TRÁFICO (Ruteo)
    // -------------------------------------------------------------------------
    public function alumno_completo($id_alumno, $id_grado)
    {
        $session = session();
        if (!$session->has('id')) { return redirect()->to('/login'); }

        $modelBoleta = new BoletaModel();
        $alumno = $modelBoleta->getDatosAlumno($id_alumno);
        if (!$alumno) return "Alumno no encontrado";

        $nombreGrado = strtolower($alumno['nombreGrado']);

        if (strpos($nombreGrado, 'secundaria') !== false) {
            return $this->_editarSecundaria($id_alumno, $id_grado);
        }
        elseif (strpos($nombreGrado, 'bachillerato') !== false || strpos($nombreGrado, 'prepa') !== false) {
            return $this->_editarBachillerato($id_alumno, $id_grado);
        }
        // 🌟 NUEVA RUTA PARA KINDER
        elseif (strpos($nombreGrado, 'kinder') !== false || strpos($nombreGrado, 'maternal') !== false) {
            return $this->_editarKinder($id_alumno, $id_grado);
        }
        else {
            return $this->_editarPrimaria($id_alumno, $id_grado);
        }
    }

    // -------------------------------------------------------------------------
    // 3. LÓGICA PRIMARIA
    // -------------------------------------------------------------------------
    private function _editarPrimaria($id_alumno, $id_grado)
    {
        $session = session();
        $modelBoleta = new BoletaModel(); 
        $cicloInfo = $modelBoleta->getCicloActivo();
        $id_ciclo = $cicloInfo['id_ciclo'];

        $alumno = $modelBoleta->getDatosAlumno($id_alumno);
        $alumno['nombre_completo'] = trim(($alumno['ap_Alumno']??'') . ' ' . ($alumno['am_Alumno']??'') . ' ' . ($alumno['Nombre']??''));
        $gradoInfo = $modelBoleta->getInfoGrado($id_grado);

        $config_espanol = json_decode($gradoInfo['boleta_config'] ?? '{}', true);
        $config_ingles  = json_decode($gradoInfo['boleta_ing_config'] ?? '{}', true);
        $materias_db = $modelBoleta->getMaterias($id_grado);
        $calificaciones = $modelBoleta->getCalificaciones($id_alumno, $id_grado, $id_ciclo);
        
        $materias_map = [];
        foreach ($materias_db as $m) {
            $m['nombre'] = html_entity_decode($m['nombre_materia']);
            $materias_map[$m['id_materia']] = $m;
        }

        $listaAlumnos = $modelBoleta->getAlumnosPorGrado($id_grado);
        $ids = array_column($listaAlumnos, 'id'); 
        $pos = array_search($id_alumno, $ids);
        $id_anterior = ($pos > 0) ? $ids[$pos - 1] : null;
        $id_siguiente = ($pos < count($ids) - 1) ? $ids[$pos + 1] : null;

        $procesarSecciones = function($configJson) use ($materias_map, $calificaciones, $modelBoleta) {
            $secciones = [];
            if (!isset($configJson['subject_groups']) || !is_array($configJson['subject_groups'])) return [];

            foreach ($configJson['subject_groups'] as $grupo) {
                if (empty($grupo['subjects']) || isset($grupo['divider'])) continue;
                $materias_del_grupo = [];
                foreach ($grupo['subjects'] as $itemMateria) {
                    $id_materia = is_array($itemMateria) ? ($itemMateria['id'] ?? 0) : $itemMateria;
                    if (isset($materias_map[$id_materia])) {
                        $mat = $materias_map[$id_materia];
                        $notas = $calificaciones[$id_materia] ?? [];
                        
                        $t1_sum = ($notas[1]??0)+($notas[2]??0)+($notas[3]??0);
                        $t1_prom = ($t1_sum > 0) ? round($t1_sum/3, 1) : null;
                        $t2_sum = ($notas[4]??0)+($notas[5]??0)+($notas[6]??0)+($notas[7]??0);
                        $t2_prom = ($t2_sum > 0) ? round($t2_sum/4, 1) : null;
                        $t3_sum = ($notas[8]??0)+($notas[9]??0)+($notas[10]??0);
                        $t3_prom = ($t3_sum > 0) ? round($t3_sum/3, 1) : null;
                        $div=0; $ac=0;
                        if($t1_prom){$ac+=$t1_prom;$div++;} if($t2_prom){$ac+=$t2_prom;$div++;} if($t3_prom){$ac+=$t3_prom;$div++;}
                        $final = ($div>0) ? round($ac/$div, 1) : null;

                        $mat['notas'] = $notas;
                        $mat['p_t1'] = $t1_prom; $mat['p_t2'] = $t2_prom; $mat['p_t3'] = $t3_prom;
                        $mat['final'] = $final;
                        $materias_del_grupo[] = $mat;
                    }
                }
                if (!empty($materias_del_grupo)) {
                    $prom_vert = $modelBoleta->getPromediosVerticales($materias_del_grupo);
                    $secciones[] = ['titulo' => $grupo['title'] ?? '', 'materias' => $materias_del_grupo, 'promedios' => $prom_vert];
                }
            }
            return $secciones;
        };

        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'grado' => $gradoInfo, 
            'id_grado' => $id_grado, 'id_anterior' => $id_anterior, 'id_siguiente' => $id_siguiente,
            'secciones_espanol' => $procesarSecciones($config_espanol),
            'secciones_ingles' => $procesarSecciones($config_ingles),
            'secciones_extra' => [],
            'user_level' => $session->get('nivel')
        ];

        return view('boletas/editar_boleta_primaria', $data);
    }

    // -------------------------------------------------------------------------
    // 4. LÓGICA SECUNDARIA
    // -------------------------------------------------------------------------
    private function _editarSecundaria($id_alumno, $id_grado)
    {
        $session = session();
        $modelBoleta = new BoletaModel(); 
        $cicloInfo = $modelBoleta->getCicloActivo();
        $id_ciclo = $cicloInfo['id_ciclo'];
        
        $alumno = $modelBoleta->getDatosAlumno($id_alumno);
        $alumno['nombre_completo'] = trim(($alumno['ap_Alumno']??'') . ' ' . ($alumno['am_Alumno']??'') . ' ' . ($alumno['Nombre']??''));
        $gradoInfo = $modelBoleta->getInfoGrado($id_grado);

        $config_espanol = json_decode($gradoInfo['boleta_config'] ?? '{}', true);
        $config_ingles  = json_decode($gradoInfo['boleta_ing_config'] ?? '{}', true);
        $materias_db = $modelBoleta->getMaterias($id_grado);
        $calificaciones = $modelBoleta->getCalificaciones($id_alumno, $id_grado, $id_ciclo);

        $materias_map = [];
        foreach ($materias_db as $m) {
            $m['nombre'] = html_entity_decode($m['nombre_materia']);
            $materias_map[$m['id_materia']] = $m;
        }

        $listaAlumnos = $modelBoleta->getAlumnosPorGrado($id_grado);
        $ids = array_column($listaAlumnos, 'id'); 
        $pos = array_search($id_alumno, $ids);
        $id_anterior = ($pos > 0) ? $ids[$pos - 1] : null;
        $id_siguiente = ($pos < count($ids) - 1) ? $ids[$pos + 1] : null;

        // HELPER PARA PROCESAR Y USAR LA FUNCIÓN PRIVADA
        $procesarSeccionesSecu = function($configJson) use ($materias_map, $calificaciones) {
            $secciones = [];
            if (!isset($configJson['subject_groups']) || !is_array($configJson['subject_groups'])) return [];

            foreach ($configJson['subject_groups'] as $grupo) {
                if (empty($grupo['subjects']) || isset($grupo['divider'])) continue;
                $materias_del_grupo = [];
                foreach ($grupo['subjects'] as $itemMateria) {
                    $id_materia = is_array($itemMateria) ? ($itemMateria['id'] ?? 0) : $itemMateria;
                    if (isset($materias_map[$id_materia])) {
                        $mat = $materias_map[$id_materia];
                        $notas = $calificaciones[$id_materia] ?? [];
                        
                        $t1_sum = ($notas[1]??0)+($notas[2]??0)+($notas[3]??0);
                        $t1_prom = ($t1_sum > 0) ? round($t1_sum/3, 1) : null;
                        $t2_sum = ($notas[4]??0)+($notas[5]??0)+($notas[6]??0)+($notas[7]??0);
                        $t2_prom = ($t2_sum > 0) ? round($t2_sum/4, 1) : null;
                        $t3_sum = ($notas[8]??0)+($notas[9]??0)+($notas[10]??0);
                        $t3_prom = ($t3_sum > 0) ? round($t3_sum/3, 1) : null;
                        $div=0; $ac=0;
                        if($t1_prom){$ac+=$t1_prom;$div++;} if($t2_prom){$ac+=$t2_prom;$div++;} if($t3_prom){$ac+=$t3_prom;$div++;}
                        $final = ($div>0) ? round($ac/$div, 1) : null;

                        $mat['notas'] = $notas;
                        $mat['p_t1'] = $t1_prom; $mat['p_t2'] = $t2_prom; $mat['p_t3'] = $t3_prom;
                        $mat['final'] = $final;
                        $materias_del_grupo[] = $mat;
                    }
                }
                if (!empty($materias_del_grupo)) {
                    // LLAMADA A LA FUNCIÓN PARA CÁLCULO DE PROMEDIOS VERTICALES
                    $prom_vert = $this->_calcularPromediosVerticalesSecundaria($materias_del_grupo);
                    $secciones[] = ['titulo' => $grupo['title'] ?? '', 'materias' => $materias_del_grupo, 'promedios' => $prom_vert];
                }
            }
            return $secciones;
        };

        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'grado' => $gradoInfo,
            'id_grado' => $id_grado, 'id_anterior' => $id_anterior, 'id_siguiente' => $id_siguiente,
            'secciones_espanol' => $procesarSeccionesSecu($config_espanol),
            'secciones_ingles'  => $procesarSeccionesSecu($config_ingles),
            'user_level'        => $session->get('nivel')
        ];

        return view('boletas/editar_boleta_secundaria', $data);
    }

    // -------------------------------------------------------------------------
    // 5. HELPER SECUNDARIA - CÁLCULO PROMEDIOS VERTICALES
    // -------------------------------------------------------------------------
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

    // -------------------------------------------------------------------------
    // 4. LÓGICA BACHILLERATO
    // -------------------------------------------------------------------------
    private function _editarBachillerato($id_alumno, $id_grado)
    {
        $session = session();
        $model = new BoletaModel(); 
        $cicloInfo = $model->getCicloActivo();
        $id_ciclo = $cicloInfo['id_ciclo'];
        
        $alumno = $model->getDatosAlumno($id_alumno);
        $alumno['nombre_completo'] = trim(($alumno['ap_Alumno']??'') . ' ' . ($alumno['am_Alumno']??'') . ' ' . ($alumno['Nombre']??''));
        $gradoInfo = $model->getInfoGrado($id_grado);

        // 1. Configurar Semestre 
        $semestre_actual = $this->request->getGet('semestre') ?? 1;

        if ($semestre_actual == 2) {
            $meses_ids = [4, 5, 6]; 
            $headers = ['FEB-MAR', 'ABR-MAY', 'JUN-JUL'];
            $link_otro = base_url("calificaciones_bimestre/alumno_completo/$id_alumno/$id_grado?semestre=1");
            $txt_btn = "Ver boleta de 1er semestre";
            $cls_btn = "btn-semestre-azul";
        } else {
            $meses_ids = [1, 2, 3]; 
            $headers = ['AGO-SEP', 'OCT-NOV', 'DIC-ENE'];
            $link_otro = base_url("calificaciones_bimestre/alumno_completo/$id_alumno/$id_grado?semestre=2");
            $txt_btn = "Ver boleta de 2do semestre";
            $cls_btn = "btn-semestre-verde";
        }

        // 2. Datos y Mapeo
        $config_json = json_decode($gradoInfo['boleta_config'] ?? '{}', true);
        $materias_db = $model->getMaterias($id_grado);
        $calificaciones = $model->getCalificaciones($id_alumno, $id_grado, $id_ciclo);
        
        $materias_map = [];
        foreach ($materias_db as $m) {
            $nombre_crudo = html_entity_decode($m['nombre_materia']);
            
            // Explode |
            if (strpos($nombre_crudo, '|') !== false) {
                $partes = explode('|', $nombre_crudo);
                // usar el nombre segun el semestre 
                if ($semestre_actual == 2 && isset($partes[1])) {
                    $m['nombre'] = trim($partes[1]);
                } else {
                    $m['nombre'] = trim($partes[0]);
                }
            } else {
                // Si no hay | se pasa igual
                $m['nombre'] = trim($nombre_crudo);
            }

            $materias_map[$m['id_materia']] = $m;
        }

        // Navegación
        $listaAlumnos = $model->getAlumnosPorGrado($id_grado);
        $ids = array_column($listaAlumnos, 'id'); 
        $pos = array_search($id_alumno, $ids);
        $id_anterior = ($pos > 0) ? $ids[$pos - 1] : null;
        $id_siguiente = ($pos < count($ids) - 1) ? $ids[$pos + 1] : null;

        
        $procesarSeccionesBach = function($configJson, $materias_map, $calificaciones) use ($meses_ids) {
            $secciones_out = [];
            $total_sum_semestre = 0;
            $total_count_semestre = 0;

            // Normalizar estructura
            $lista_grupos = [];
            if (isset($configJson['subject_groups'])) {
                $lista_grupos = $configJson['subject_groups'];
            } elseif (isset($configJson['subjects'])) {
                $lista_grupos[] = [
                    'title' => '', 
                    'subjects' => $configJson['subjects']
                ];
            }

            // Procesar grupos
            foreach ($lista_grupos as $grupo) {
                if (isset($grupo['divider']) || empty($grupo['subjects'])) continue;

                $titulo = $grupo['title'] ?? '';
                $materias_grupo = [];

                foreach ($grupo['subjects'] as $itemMateria) {
                    $id_materia = is_array($itemMateria) ? ($itemMateria['id'] ?? 0) : $itemMateria;
                    
                    // Detectar Taller por color 
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
                            $mat['notas'][$id_mes] = $val; 
                            
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
            
            $promedio_general = ($total_count_semestre > 0) ? round($total_sum_semestre/$total_count_semestre, 1) : null;

            return ['secciones' => $secciones_out, 'promedio_general' => $promedio_general];
        };

        $resultado = $procesarSeccionesBach($config_json, $materias_map, $calificaciones);

        $data = [
            'alumno' => $alumno, 
            'ciclo' => $cicloInfo, 
            'grado' => $gradoInfo,
            'id_grado' => $id_grado, 
            'id_anterior' => $id_anterior, 
            'id_siguiente' => $id_siguiente,
            
            'secciones'       => $resultado['secciones'],
            'prom_gral'       => $resultado['promedio_general'],
            'headers'         => $headers,
            'col_ids'         => $meses_ids, 
            'semestre_actual' => $semestre_actual,
            'link_otro_semestre' => $link_otro,
            'texto_boton'     => $txt_btn,
            'clase_boton'     => $cls_btn,
            'user_level'      => $session->get('nivel')
        ];

        return view('boletas/editar_boleta_bachillerato', $data);
    }

    // -------------------------------------------------------------------------
    // 6. LÓGICA KINDER (NUEVO)
    // -------------------------------------------------------------------------
    private function _editarKinder($id_alumno, $id_grado)
    {
        $session = session();
        $modelBoleta = new BoletaModel(); 
        $cicloInfo = $modelBoleta->getCicloActivo();
        $id_ciclo = $cicloInfo['id_ciclo'];

        $alumno = $modelBoleta->getDatosAlumno($id_alumno);
        $alumno['nombre_completo'] = trim(($alumno['ap_Alumno']??'') . ' ' . ($alumno['am_Alumno']??'') . ' ' . ($alumno['Nombre']??''));
        $gradoInfo = $modelBoleta->getInfoGrado($id_grado);

        $config_json = json_decode($gradoInfo['boleta_config'] ?? '{}', true);
        $materias_db = $modelBoleta->getMaterias($id_grado);
        $calificaciones = $modelBoleta->getCalificaciones($id_alumno, $id_grado, $id_ciclo);
        
        $materias_map = [];
        foreach ($materias_db as $m) {
            $m['nombre'] = html_entity_decode($m['nombre_materia']);
            $materias_map[$m['id_materia']] = $m;
        }

        // 2. Navegación entre alumnos
        $listaAlumnos = $modelBoleta->getAlumnosPorGrado($id_grado);
        $ids = array_column($listaAlumnos, 'id'); 
        $pos = array_search($id_alumno, $ids);
        $id_anterior = ($pos > 0) ? $ids[$pos - 1] : null;
        $id_siguiente = ($pos < count($ids) - 1) ? $ids[$pos + 1] : null;

        $procesarLado = function($ladoData) use ($materias_map, $calificaciones) {
            $resultado = [];
            if (!isset($ladoData['groups']) || !is_array($ladoData['groups'])) return $resultado;

            foreach ($ladoData['groups'] as $grupo) {
                if (empty($grupo['subjects'])) {
                    $resultado[] = ['titulo' => $grupo['title'], 'materias' => []];
                    continue;
                }

                $materias_grupo = [];
                foreach ($grupo['subjects'] as $item) {
                    $id_mat = is_array($item) ? ($item['id'] ?? 0) : $item;
                    if (isset($materias_map[$id_mat])) {
                        $mat = $materias_map[$id_mat];
                        $mat['notas'] = $calificaciones[$id_mat] ?? [];
                        $mat['isPercentage'] = $grupo['isPercentage'] ?? false;
                        $mat['calculated'] = is_array($item) ? ($item['calculated'] ?? false) : false;
                        $materias_grupo[] = $mat;
                    }
                }
                
                if (!empty($materias_grupo)) {
                    $resultado[] = ['titulo' => $grupo['title'] ?? '', 'materias' => $materias_grupo];
                }
            }
            return $resultado;
        };

        $left_groups = $procesarLado($config_json['left'] ?? []);
        $right_groups = $procesarLado($config_json['right'] ?? []);

        $left_title = $config_json['left']['title'] ?? 'CAMPOS DE FORMACIÓN';
        $right_title = $config_json['right']['title'] ?? 'ÁREAS DE DESARROLLO';

        // 4. Empaquetar y enviar a la vista
        $data = [
            'alumno'       => $alumno, 
            'ciclo'        => $cicloInfo, 
            'grado'        => $gradoInfo,
            'id_grado'     => $id_grado, 
            'id_anterior'  => $id_anterior, 
            'id_siguiente' => $id_siguiente,
            'left_title'   => $left_title,   
            'right_title'  => $right_title,   
            'left_groups'  => $left_groups,
            'right_groups' => $right_groups,
            'momentos'     => [1, 2, 3],
            'user_level'   => $session->get('nivel')
        ];

        return view('boletas/editar_boleta_kinder', $data);
    }

    // -------------------------------------------------------------------------
    // 7. ACTUALIZAR AJAX
    // -------------------------------------------------------------------------
    public function actualizar()
    {
        if (!$this->request->isAJAX()) return "Prohibido";
        
        $session = session();
        $nivel = $session->get('nivel');

        if ($nivel == 7) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No tienes permisos.']);
        }

        $model = new CalificacionesBimestreModel();
        $modelBoleta = new BoletaModel();
        $cicloInfo = $modelBoleta->getCicloActivo();

        $res = $model->guardarCalificacion(
            $this->request->getPost('id_alumno'),
            $this->request->getPost('id_grado'),
            $this->request->getPost('id_materia'),
            $this->request->getPost('id_mes'),
            $this->request->getPost('valor'),
            $session->get('id'), 
            $cicloInfo['id_ciclo']
        );
        
        return $this->response->setJSON(['status' => $res ? 'success' : 'error']);
    }
}