<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BoletaModel;

class AlumnoViewController extends BaseController
{
    // =========================================================================
    // 1. PUNTO DE ENTRADA SEGURO 
    // =========================================================================
    public function verBoleta()
    {
        // A) OBTENER ID DE SESIÓN
        // No aceptamos parámetros en la función. El ID soy YO.
        $id_alumno = session()->get('id');

        if (!$id_alumno) {
            return redirect()->to(base_url('login'));
        }

        $model = new BoletaModel();

        // B) DATOS GENERALES
        $alumno = $model->getDatosAlumno($id_alumno);
        if (!$alumno) {
            return redirect()->to(base_url('dashboard'))->with('error', 'No se encontraron datos académicos.');
        }

        $cicloInfo = $model->getCicloActivo();
        if (!$cicloInfo) { die("Error: No hay ciclo escolar activo configurado."); }

        // el 'grado' viene en la info del alumno de la tabla usr
        
        // Vamos a usar el 'grado' que viene en la tabla usr (que es el ID)
        $db = \Config\Database::connect();
        $row = $db->table('usr')->select('grado')->where('id', $id_alumno)->get()->getRow();
        $id_grado = $row->grado;

        $nombreGrado = strtolower($alumno['nombreGrado']);

        // C) ENRUTAMIENTO INTELIGENTE 
        
        // 1. Secundaria
        if (strpos($nombreGrado, 'secundaria') !== false) {
            return $this->_procesarSecundaria($model, $id_grado, $id_alumno, $alumno, $cicloInfo);
        }
        // 2. Bachillerato
        elseif (strpos($nombreGrado, 'bachillerato') !== false || strpos($nombreGrado, 'prepa') !== false) {
            return $this->_procesarBachiller($model, $id_grado, $id_alumno, $alumno, $cicloInfo);
        }
        // 3. Kinder
        elseif (strpos($nombreGrado, 'kinder') !== false || strpos($nombreGrado, 'maternal') !== false) {
            return $this->_procesarKinder($model, $id_grado, $id_alumno, $alumno, $cicloInfo);
        }
        // 4. Primaria (Default)
        else {
            return $this->_procesarPrimaria($model, $id_grado, $id_alumno, $alumno, $cicloInfo);
        }
    }

    // =========================================================================
    // 2. MÓDULOS DE PROCESAMIENTO (REUTILIZADOS Y ADAPTADOS)
    // =========================================================================

    // --- PRIMARIA ---
    private function _procesarPrimaria($model, $id_grado, $id_alumno, $alumno, $cicloInfo)
    {
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

        // Procesar JSONs
        $secciones_espanol = $this->_helperProcesarPrimaria($config_espanol, $materias_map, $calificaciones);
        $secciones_ingles  = $this->_helperProcesarPrimaria($config_ingles, $materias_map, $calificaciones);

        $data = [
            'alumno' => $alumno,
            'ciclo'  => $cicloInfo,
            'id_grado' => $id_grado,
            // SEGURIDAD: Navegación deshabilitada para alumnos
            'id_anterior' => null, 
            'id_siguiente' => null,
            'secciones_espanol' => $secciones_espanol,
            'secciones_ingles'  => $secciones_ingles,
            'secciones_extra'   => [] 
        ];

        // REUTILIZAMOS LA VISTA EXISTENTE
        return view('boletas/ver_boleta_primaria', $data);
    }

    // --- SECUNDARIA ---
    private function _procesarSecundaria($model, $id_grado, $id_alumno, $alumno, $cicloInfo)
    {
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
            'secciones_espanol' => $secciones_espanol,
            'secciones_ingles'  => $secciones_ingles
        ];

        return view('boletas/ver_boleta_secundaria', $data);
    }

    // --- BACHILLERATO ---
    private function _procesarBachiller($model, $id_grado, $id_alumno, $alumno, $cicloInfo)
    {
        $id_ciclo = $cicloInfo['id_ciclo'];
        // Permitimos que el alumno cambie de semestre 
        $semestre_actual = $this->request->getGet('semestre') ?? 1;

        if ($semestre_actual == 2) {
            $meses_ids = [4, 5, 6]; 
            $headers = ['FEB-MAR', 'ABR-MAY', 'JUN-JUL'];
            // Ajustamos el link para que apunte a la ruta de alumno
            $link_otro = base_url("alumno/boleta?semestre=1");
            $txt_btn = "Ver boleta de 1er semestre";
            $cls_btn = "btn-semestre-azul";
        } else {
            $meses_ids = [1, 2, 3]; 
            $headers = ['AGO-SEP', 'OCT-NOV', 'DIC-ENE'];
            $link_otro = base_url("alumno/boleta?semestre=2");
            $txt_btn = "Ver boleta de 2do semestre";
            $cls_btn = "btn-semestre-verde";
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

        // Usamos el helper interno
        $resultado = $this->_helperProcesarBachiller($config_json, $materias_map, $calificaciones, $meses_ids);

        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'id_grado' => $id_grado,
            'id_anterior' => null, 'id_siguiente'=> null,
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

    // --- KINDER ---
    private function _procesarKinder($model, $id_grado, $id_alumno, $alumno, $cicloInfo)
    {
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
        $momentos = [1, 2, 3]; 

        $data = [
            'alumno' => $alumno, 'ciclo' => $cicloInfo, 'id_grado' => $id_grado,
            'id_anterior' => null, 'id_siguiente' => null,
            'left_groups'  => $estructura['left'],
            'right_groups' => $estructura['right'],
            'translateType'=> $estructura['translateType'],
            'momentos'     => $momentos
        ];

        return view('boletas/ver_boleta_kinder', $data);
    }

    // =========================================================================
    // 3. HELPERS DE CÁLCULO 
    // =========================================================================

    private function _helperProcesarPrimaria($configJson, $materias_map, $calificaciones) {
        $secciones_resultado = [];
        if (!isset($configJson['subject_groups']) || !is_array($configJson['subject_groups'])) return [];

        foreach ($configJson['subject_groups'] as $grupo) {
            if (isset($grupo['divider']) && $grupo['divider'] === true) continue;
            if (empty($grupo['subjects'])) continue;

            $titulo = $grupo['title'] ?? '';
            $materias_del_grupo = [];

            foreach ($grupo['subjects'] as $itemMateria) {
                $id_materia = is_array($itemMateria) ? ($itemMateria['id'] ?? 0) : $itemMateria;
                if (isset($materias_map[$id_materia])) {
                    $mat = $materias_map[$id_materia];
                    $notas = $calificaciones[$id_materia] ?? [];

                    $t1_suma = ($notas[1]??0) + ($notas[2]??0) + ($notas[3]??0);
                    $t1_prom = ($t1_suma > 0) ? round($t1_suma / 3, 1) : null;
                    $t2_suma = ($notas[4]??0) + ($notas[5]??0) + ($notas[6]??0) + ($notas[7]??0);
                    $t2_prom = ($t2_suma > 0) ? round($t2_suma / 4, 1) : null;
                    $t3_suma = ($notas[8]??0) + ($notas[9]??0) + ($notas[10]??0);
                    $t3_prom = ($t3_suma > 0) ? round($t3_suma / 3, 1) : null;

                    $final = null; $divisor = 0; $acumulado = 0;
                    if($t1_prom > 0) { $acumulado += $t1_prom; $divisor++; }
                    if($t2_prom > 0) { $acumulado += $t2_prom; $divisor++; }
                    if($t3_prom > 0) { $acumulado += $t3_prom; $divisor++; }
                    if ($divisor > 0) { $final = round($acumulado / $divisor, 1); }

                    $mat['notas'] = $notas; $mat['p_t1'] = $t1_prom; $mat['p_t2'] = $t2_prom; $mat['p_t3'] = $t3_prom; $mat['final'] = $final;
                    $materias_del_grupo[] = $mat;
                }
            }

            if (!empty($materias_del_grupo)) {
                $secciones_resultado[] = [
                    'titulo'    => $titulo,
                    'materias'  => $materias_del_grupo,
                    'promedios' => $this->_calcPromVertPrimaria($materias_del_grupo)
                ];
            }
        }
        return $secciones_resultado;
    }

    private function _calcPromVertPrimaria($materias) {
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

    private function _helperProcesarSecundaria($configJson, $materias_map, $calificaciones) {
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

                    $t1_sum = ($notas[1]??0)+($notas[2]??0)+($notas[3]??0);
                    $t1_prom = ($t1_sum > 0) ? round($t1_sum/3, 1) : null;
                    $t2_sum = ($notas[4]??0)+($notas[5]??0)+($notas[6]??0)+($notas[7]??0);
                    $t2_prom = ($t2_sum > 0) ? round($t2_sum/4, 1) : null;
                    $t3_sum = ($notas[8]??0)+($notas[9]??0)+($notas[10]??0);
                    $t3_prom = ($t3_sum > 0) ? round($t3_sum/3, 1) : null;

                    $final = null; $div=0; $sum=0;
                    if($t1_prom){ $sum+=$t1_prom; $div++; }
                    if($t2_prom){ $sum+=$t2_prom; $div++; }
                    if($t3_prom){ $sum+=$t3_prom; $div++; }
                    if($div>0) $final = round($sum/$div, 1);

                    $mat['notas'] = $notas; $mat['p_t1'] = $t1_prom; $mat['p_t2'] = $t2_prom; $mat['p_t3'] = $t3_prom; $mat['final'] = $final;
                    $materias_grupo[] = $mat;
                }
            }
            if (!empty($materias_grupo)) {
                $secciones[] = [
                    'titulo'    => $titulo,
                    'materias'  => $materias_grupo,
                    'promedios' => $this->_calcPromVertSecu($materias_grupo)
                ];
            }
        }
        return $secciones;
    }

    private function _calcPromVertSecu($materias) {
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

    private function _helperProcesarBachiller($configJson, $materias_map, $calificaciones, $meses_ids) {
        $secciones_out = [];
        $total_sum_semestre = 0;
        $total_count_semestre = 0;
        $lista_grupos = [];

        if (isset($configJson['subject_groups'])) {
            $lista_grupos = $configJson['subject_groups'];
        } elseif (isset($configJson['subjects'])) {
            $lista_grupos[] = ['title' => '', 'subjects' => $configJson['subjects']];
        }

        foreach ($lista_grupos as $grupo) {
            if (isset($grupo['divider']) || empty($grupo['subjects'])) continue;
            $titulo = $grupo['title'] ?? '';
            $materias_grupo = [];

            foreach ($grupo['subjects'] as $itemMateria) {
                $id_materia = is_array($itemMateria) ? ($itemMateria['id'] ?? 0) : $itemMateria;
                $es_taller = (is_array($itemMateria) && !empty($itemMateria['bgcolor']));

                if (isset($materias_map[$id_materia])) {
                    $mat = $materias_map[$id_materia];
                    $notas_materia = $calificaciones[$id_materia] ?? [];
                    $mat['notas'] = []; $sum_horiz = 0; $count_horiz = 0;

                    foreach ($meses_ids as $id_mes) {
                        $val = $notas_materia[$id_mes] ?? null;
                        $mat['notas'][] = $val;
                        if (is_numeric($val) && $val > 0) { $sum_horiz += $val; $count_horiz++; }
                    }
                    $mat['promedio'] = ($count_horiz > 0) ? round($sum_horiz/$count_horiz, 1) : null;
                    if ($mat['promedio']) { $total_sum_semestre += $mat['promedio']; $total_count_semestre++; }
                    $mat['es_taller'] = $es_taller; 
                    $materias_grupo[] = $mat;
                }
            }
            if (!empty($materias_grupo)) {
                $secciones_out[] = ['titulo' => $titulo, 'materias' => $materias_grupo];
            }
        }
        $promedio_general = ($total_count_semestre > 0) ? round($total_sum_semestre/$total_count_semestre, 1) : null;
        return ['secciones' => $secciones_out, 'promedio_general' => $promedio_general];
    }


    // =========================================================================
    // FICHA DEL ALUMNO  
    // =========================================================================
    public function ficha()
    {
        $session = session();
        $id_alumno = $session->get('id');
        
        if (!$id_alumno) return redirect()->to('/login');

        $model = new \App\Models\AlumnoFichaModel();
        
        $alumno = $model->getDatosFicha($id_alumno);

        // Si por alguna razón el grado viene vacío, le ponemos un valor por defecto
        if (!isset($alumno['nombreGrado'])) {
            $alumno['nombreGrado'] = 'No asignado';
        }

        $data = [
            'title'  => 'Ficha del Alumno',
            'alumno' => $alumno
        ];

        return view('VistadelAlumno/ficha', $data);
    }

    // =========================================================================
    // ACTUALIZAR FICHA
    // =========================================================================
    public function actualizarFicha()
    {
        $session = session();
        $id_alumno = $session->get('id');
        
        if (!$id_alumno) return redirect()->to('/login');

        $request = \Config\Services::request();

        $dataUpdate = [
            'nia'            => $request->getPost('nia'),
            'Nombre'         => $request->getPost('Nombre'),
            'ap_Alumno'      => $request->getPost('ap_Alumno'),
            'am_Alumno'      => $request->getPost('am_Alumno'),
            'curp'           => $request->getPost('curp'),
            'rfc'            => $request->getPost('rfc'),
            'fechaNacAlumno' => $request->getPost('fechaNacAlumno'),

            'direccion'      => $request->getPost('direccion_alum'),
            'cp_alum'        => $request->getPost('cp_alum'),
            'estado'         => $request->getPost('estado'),
            'telefono_alum'  => $request->getPost('telefono_alum'),
            'mail_alumn'     => $request->getPost('emailTutor'), 
            
            'p_nombre'       => $request->getPost('p_nombre'),
            'p_domicilio'    => $request->getPost('p_domicilio'),
            'p_empresa'      => $request->getPost('p_empresa'),
            'p_cargo'        => $request->getPost('p_cargo'),
            'p_mail'         => $request->getPost('p_mail'),
            'p_tel_particular'=> $request->getPost('p_tel_particular'),
            'p_celular'      => $request->getPost('p_celular'),
            'p_parentesco'   => $request->getPost('p_parentesco'),
            'p_ultimogradoestudios' => $request->getPost('p_ultimogradoestudios'),

            'm_nombre'       => $request->getPost('m_nombre'),
            'm_domicilio'    => $request->getPost('m_domicilio'),
            'm_empresa'      => $request->getPost('m_empresa'),
            'm_cargo'        => $request->getPost('m_cargo'),
            'm_mail'         => $request->getPost('m_mail'),
            'm_tel_particular'=> $request->getPost('m_tel_particular'),
            'm_celular'      => $request->getPost('m_celular'),
            'm_parentesco'   => $request->getPost('m_parentesco'),
            'm_ultimogradoestudios' => $request->getPost('m_ultimogradoestudios'),

            'e_nombre'       => $request->getPost('e_nombre'),
            'e_telefono'     => $request->getPost('e_telefono'),
            'extra'          => $request->getPost('extra'),
            
            'fecha_actualizar' => date('Y-m-d H:i:s')
        ];

        $model = new \App\Models\AlumnoFichaModel();
        $model->updateDatosContacto($id_alumno, $dataUpdate);

        return redirect()->back()->with('mensaje', 'Tu información se ha actualizado correctamente.');
    }
}