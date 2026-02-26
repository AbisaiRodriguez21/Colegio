<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BoletaModel;          // Para ver lista y PDF
use App\Models\CalificacionesModel;  // Para la Sábana 

class TitularViewController extends BaseController
{
// =========================================================================
    // 1. VER LISTA DE ALUMNOS (Mi Grupo)
    // =========================================================================
    public function verGrupo()
    {
        // El filtro TitularAuth ya validó permisos. 
        $id_grado_asignado = session('nivelT');
        
        // Validación de seguridad por si la sesión expiró parcialmente
        if (!$id_grado_asignado) {
             return redirect()->to(base_url('dashboard'))->with('error', 'No se detectó el grupo asignado.');
        }

        $model = new BoletaModel(); 

        $alumnos = $model->getAlumnosPorGrado($id_grado_asignado);
        $infoGrado = $model->getInfoGrado($id_grado_asignado);

        $data = [
            'alumnos'  => $alumnos,
            'grado'    => $infoGrado,
            'id_grado' => $id_grado_asignado,
            'is_titular' => true 
        ];

        return view('boletas/lista_alumnos', $data);
    }

    // =========================================================================
    // 2. CALIFICAR GRUPO (Sábana de Calificaciones)
    // =========================================================================
    public function calificarGrupo()
    {
        $id_grado = session('nivelT');

        if (!$id_grado) {
            return redirect()->to(base_url('dashboard'));
        }

        // 1. CAPTURAR EL MES DE LA URL
        $mes_custom = $this->request->getGet('mes_custom'); 

        $model = new CalificacionesModel();
        
        // 2. PASARLO AL MODELO
        $data = $model->getSabana($id_grado, $mes_custom); 

        if (!$data) {
            return redirect()->back()->with('error', 'No se pudo cargar la configuración de la sábana.');
        }

        $data['user_level'] = 9; 

        return view('boletas/calificar_boleta', $data);
    }

    // =========================================================================
    // 3. VER BOLETA INDIVIDUAL (Vista de Impresión / PDF)
    // =========================================================================
    public function verBoletaAlumno($id_alumno_target)
    {
        $id_grado_titular = session('nivelT');
        $model = new BoletaModel(); 

        // --- VALIDACIÓN DE PERTENENCIA ---
        // Verificamos que el alumno que intenta ver pertenezca a SU grupo.
        $db = \Config\Database::connect();
        $check = $db->table('usr')->select('grado')->where('id', $id_alumno_target)->get()->getRow();
        
        if (!$check || $check->grado != $id_grado_titular) {
            return redirect()->to(base_url('titular/mi-grupo'))->with('error', 'Acceso denegado: Este alumno no pertenece a tu grupo.');
        }

        // --- PROCESAMIENTO DE BOLETA ---
        $datosAlumno = $model->getDatosAlumno($id_alumno_target);
        $cicloInfo = $model->getCicloActivo();
        $nombreGrado = strtolower($datosAlumno['nombreGrado']);

        // Delegamos a las funciones privadas (helpers) según el nivel
        if (strpos($nombreGrado, 'secundaria') !== false) {
            return $this->_procesarSecundaria($model, $id_grado_titular, $id_alumno_target, $datosAlumno, $cicloInfo);
        } elseif (strpos($nombreGrado, 'bachillerato') !== false || strpos($nombreGrado, 'prepa') !== false) {
            return $this->_procesarBachiller($model, $id_grado_titular, $id_alumno_target, $datosAlumno, $cicloInfo);
        } elseif (strpos($nombreGrado, 'kinder') !== false || strpos($nombreGrado, 'maternal') !== false) {
            return $this->_procesarKinder($model, $id_grado_titular, $id_alumno_target, $datosAlumno, $cicloInfo);
        } else {
            return $this->_procesarPrimaria($model, $id_grado_titular, $id_alumno_target, $datosAlumno, $cicloInfo);
        }
    }

    // =========================================================================
    // 4. HELPERS DE VISUALIZACIÓN 
    // =========================================================================
    // Estos son necesarios SOLO para la función verBoletaAlumno (PDF)
    // La sábana ya no los usa porque CalificacionesModel se encarga.
    
    private function _procesarPrimaria($model, $id_grado, $id_alumno, $alumno, $cicloInfo) {
        $id_ciclo = $cicloInfo['id_ciclo'];
        $gradoInfo = $model->getInfoGrado($id_grado);
        $config_espanol = json_decode($gradoInfo['boleta_config'] ?? '{}', true);
        $config_ingles  = json_decode($gradoInfo['boleta_ing_config'] ?? '{}', true);
        $materias_db = $model->getMaterias($id_grado); 
        $calificaciones = $model->getCalificaciones($id_alumno, $id_grado, $id_ciclo);
        $materias_map = [];
        foreach ($materias_db as $m) {
            $m['nombre'] = html_entity_decode($m['nombre_materia']);
            $materias_map[$m['id_materia']] = $m;
        }
        $secciones_espanol = $this->_helperProcesarPrimaria($config_espanol, $materias_map, $calificaciones);
        $secciones_ingles  = $this->_helperProcesarPrimaria($config_ingles, $materias_map, $calificaciones);
        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'id_grado' => $id_grado,
            'id_anterior' => null, 'id_siguiente' => null,
            'secciones_espanol' => $secciones_espanol, 'secciones_ingles' => $secciones_ingles, 'secciones_extra' => [] 
        ];
        return view('boletas/ver_boleta_primaria', $data);
    }

    private function _procesarSecundaria($model, $id_grado, $id_alumno, $alumno, $cicloInfo) {
        $id_ciclo = $cicloInfo['id_ciclo'];
        $gradoInfo = $model->getInfoGrado($id_grado);
        $config_espanol = json_decode($gradoInfo['boleta_config'] ?? '{}', true);
        $config_ingles  = json_decode($gradoInfo['boleta_ing_config'] ?? '{}', true);
        $materias_db = $model->getMaterias($id_grado); 
        $calificaciones = $model->getCalificaciones($id_alumno, $id_grado, $id_ciclo);
        $materias_map = [];
        foreach ($materias_db as $m) {
            $m['nombre'] = html_entity_decode($m['nombre_materia']);
            $materias_map[$m['id_materia']] = $m;
        }
        $secciones_espanol = $this->_helperProcesarSecundaria($config_espanol, $materias_map, $calificaciones);
        $secciones_ingles  = $this->_helperProcesarSecundaria($config_ingles, $materias_map, $calificaciones);
        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'id_grado' => $id_grado,
            'id_anterior' => null, 'id_siguiente' => null,
            'secciones_espanol' => $secciones_espanol, 'secciones_ingles' => $secciones_ingles
        ];
        return view('boletas/ver_boleta_secundaria', $data);
    }

    private function _procesarBachiller($model, $id_grado, $id_alumno, $alumno, $cicloInfo) {
        $id_ciclo = $cicloInfo['id_ciclo'];
        $semestre_actual = $this->request->getGet('semestre') ?? 1;
        if ($semestre_actual == 2) {
            $meses_ids = [4, 5, 6]; $headers = ['FEB-MAR', 'ABR-MAY', 'JUN-JUL'];
            $link_otro = base_url("titular/ver-boleta/$id_alumno?semestre=1");
            $txt_btn = "Ver boleta de 1er semestre"; $cls_btn = "btn-semestre-azul";
        } else {
            $meses_ids = [1, 2, 3]; $headers = ['AGO-SEP', 'OCT-NOV', 'DIC-ENE'];
            $link_otro = base_url("titular/ver-boleta/$id_alumno?semestre=2");
            $txt_btn = "Ver boleta de 2do semestre"; $cls_btn = "btn-semestre-verde";
        }
        $gradoInfo = $model->getInfoGrado($id_grado);
        $config_json = json_decode($gradoInfo['boleta_config'] ?? '{}', true);
        $materias_db = $model->getMaterias($id_grado);
        $calificaciones = $model->getCalificaciones($id_alumno, $id_grado, $id_ciclo);
        $materias_map = [];
        foreach ($materias_db as $m) {
            $m['nombre'] = html_entity_decode($m['nombre_materia']);
            $materias_map[$m['id_materia']] = $m;
        }
        $resultado = $this->_helperProcesarBachiller($config_json, $materias_map, $calificaciones, $meses_ids);
        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'id_grado' => $id_grado,
            'id_anterior' => null, 'id_siguiente'=> null,
            'secciones' => $resultado['secciones'], 'prom_gral' => $resultado['promedio_general'],
            'headers' => $headers, 'semestre_actual' => $semestre_actual,
            'link_otro_semestre' => $link_otro, 'texto_boton' => $txt_btn, 'clase_boton' => $cls_btn
        ];
        return view('boletas/ver_boleta_bachillerato', $data);
    }

    private function _procesarKinder($model, $id_grado, $id_alumno, $alumno, $cicloInfo) {
        $id_ciclo = $cicloInfo['id_ciclo'];
        $gradoInfo = $model->getInfoGrado($id_grado);
        $config_json = json_decode($gradoInfo['boleta_config'] ?? '{}', true);
        $materias_db = $model->getMaterias($id_grado);
        $calificaciones = $model->getCalificaciones($id_alumno, $id_grado, $id_ciclo);
        $materias_map = [];
        foreach ($materias_db as $m) {
            $m['nombre'] = html_entity_decode($m['nombre_materia']);
            $materias_map[$m['id_materia']] = $m;
        }
        $estructura = $model->procesarKinder($config_json, $materias_map, $calificaciones);
        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'id_grado' => $id_grado,
            'id_anterior' => null, 'id_siguiente' => null,
            'left_groups' => $estructura['left'], 'right_groups' => $estructura['right'],
            'translateType'=> $estructura['translateType'], 'momentos' => [1, 2, 3]
        ];
        return view('boletas/ver_boleta_kinder', $data);
    }

    // Funciones Helper (Cálculos matemáticos para PDF)
    private function _helperProcesarPrimaria($configJson, $materias_map, $calificaciones) {
        $secciones_resultado = [];
        if (!isset($configJson['subject_groups']) || !is_array($configJson['subject_groups'])) return [];
        foreach ($configJson['subject_groups'] as $grupo) {
            if (isset($grupo['divider']) && $grupo['divider'] === true) continue;
            if (empty($grupo['subjects'])) continue;
            $titulo = $grupo['title'] ?? ''; $materias_del_grupo = [];
            foreach ($grupo['subjects'] as $itemMateria) {
                $id_materia = is_array($itemMateria) ? ($itemMateria['id'] ?? 0) : $itemMateria;
                if (isset($materias_map[$id_materia])) {
                    $mat = $materias_map[$id_materia]; $notas = $calificaciones[$id_materia] ?? [];
                    $t1_suma = ($notas[1]??0)+($notas[2]??0)+($notas[3]??0); $t1_prom = ($t1_suma>0)?round($t1_suma/3,1):null;
                    $t2_suma = ($notas[4]??0)+($notas[5]??0)+($notas[6]??0)+($notas[7]??0); $t2_prom = ($t2_suma>0)?round($t2_suma/4,1):null;
                    $t3_suma = ($notas[8]??0)+($notas[9]??0)+($notas[10]??0); $t3_prom = ($t3_suma>0)?round($t3_suma/3,1):null;
                    $final = null; $div=0; $acum=0;
                    if($t1_prom>0){$acum+=$t1_prom;$div++;} if($t2_prom>0){$acum+=$t2_prom;$div++;} if($t3_prom>0){$acum+=$t3_prom;$div++;}
                    if($div>0){$final=round($acum/$div,1);}
                    $mat['notas']=$notas; $mat['p_t1']=$t1_prom; $mat['p_t2']=$t2_prom; $mat['p_t3']=$t3_prom; $mat['final']=$final;
                    $materias_del_grupo[] = $mat;
                }
            }
            if (!empty($materias_del_grupo)) {
                $secciones_resultado[] = ['titulo'=>$titulo, 'materias'=>$materias_del_grupo, 'promedios'=>$this->_calcPromVertPrimaria($materias_del_grupo)];
            }
        }
        return $secciones_resultado;
    }
    private function _calcPromVertPrimaria($materias) {
        $sumas = array_fill(1, 10, 0); $counts = array_fill(1, 10, 0); 
        $sum_p1=0; $c_p1=0; $sum_p2=0; $c_p2=0; $sum_p3=0; $c_p3=0; $sum_fin=0; $c_fin=0;
        foreach ($materias as $m) {
            for ($i=1; $i<=10; $i++) { $val = $m['notas'][$i] ?? 0; if ($val > 0) { $sumas[$i] += $val; $counts[$i]++; } }
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
    private function _helperProcesarSecundaria($configJson, $materias_map, $calificaciones) {
        $secciones = [];
        if (!isset($configJson['subject_groups']) || !is_array($configJson['subject_groups'])) return [];
        foreach ($configJson['subject_groups'] as $grupo) {
            if (isset($grupo['divider']) || empty($grupo['subjects'])) continue;
            $titulo = $grupo['title'] ?? ''; $materias_grupo = [];
            foreach ($grupo['subjects'] as $itemMateria) {
                $id_materia = is_array($itemMateria) ? ($itemMateria['id'] ?? 0) : $itemMateria;
                if (isset($materias_map[$id_materia])) {
                    $mat = $materias_map[$id_materia]; $notas = $calificaciones[$id_materia] ?? [];
                    $t1_sum=($notas[1]??0)+($notas[2]??0)+($notas[3]??0); $t1_prom=($t1_sum>0)?round($t1_sum/3,1):null;
                    $t2_sum=($notas[4]??0)+($notas[5]??0)+($notas[6]??0)+($notas[7]??0); $t2_prom=($t2_sum>0)?round($t2_sum/4,1):null;
                    $t3_sum=($notas[8]??0)+($notas[9]??0)+($notas[10]??0); $t3_prom=($t3_sum>0)?round($t3_sum/3,1):null;
                    $final=null; $div=0; $sum=0; if($t1_prom){$sum+=$t1_prom;$div++;} if($t2_prom){$sum+=$t2_prom;$div++;} if($t3_prom){$sum+=$t3_prom;$div++;} if($div>0)$final=round($sum/$div,1);
                    $mat['notas']=$notas; $mat['p_t1']=$t1_prom; $mat['p_t2']=$t2_prom; $mat['p_t3']=$t3_prom; $mat['final']=$final;
                    $materias_grupo[] = $mat;
                }
            }
            if (!empty($materias_grupo)) {
                $secciones[] = ['titulo'=>$titulo, 'materias'=>$materias_grupo, 'promedios'=>$this->_calcPromVertPrimaria($materias_grupo)]; // Reusa logica prom
            }
        }
        return $secciones;
    }
    private function _helperProcesarBachiller($configJson, $materias_map, $calificaciones, $meses_ids) {
        $secciones_out = []; $total_sum=0; $total_count=0; $lista_grupos = [];
        if (isset($configJson['subject_groups'])) $lista_grupos = $configJson['subject_groups'];
        elseif (isset($configJson['subjects'])) $lista_grupos[] = ['title'=>'', 'subjects'=>$configJson['subjects']];
        foreach ($lista_grupos as $grupo) {
            if (isset($grupo['divider']) || empty($grupo['subjects'])) continue;
            $titulo = $grupo['title'] ?? ''; $materias_grupo = [];
            foreach ($grupo['subjects'] as $itemMateria) {
                $id_materia = is_array($itemMateria) ? ($itemMateria['id'] ?? 0) : $itemMateria;
                $es_taller = (is_array($itemMateria) && !empty($itemMateria['bgcolor']));
                if (isset($materias_map[$id_materia])) {
                    $mat = $materias_map[$id_materia]; $notas_materia = $calificaciones[$id_materia] ?? [];
                    $mat['notas'] = []; $sum_horiz = 0; $count_horiz = 0;
                    foreach ($meses_ids as $id_mes) {
                        $val = $notas_materia[$id_mes] ?? null; $mat['notas'][] = $val;
                        if (is_numeric($val) && $val > 0) { $sum_horiz += $val; $count_horiz++; }
                    }
                    $mat['promedio'] = ($count_horiz > 0) ? round($sum_horiz/$count_horiz, 1) : null;
                    if ($mat['promedio']) { $total_sum += $mat['promedio']; $total_count++; }
                    $mat['es_taller'] = $es_taller; $materias_grupo[] = $mat;
                }
            }
            if (!empty($materias_grupo)) $secciones_out[] = ['titulo'=>$titulo, 'materias'=>$materias_grupo];
        }
        $prom_gral = ($total_count > 0) ? round($total_sum/$total_count, 1) : null;
        return ['secciones' => $secciones_out, 'promedio_general' => $prom_gral];
    }
}