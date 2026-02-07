<?php namespace App\Controllers;

use App\Models\CalificacionesModel;

class Calificaciones extends BaseController
{
    // =========================================================================
    // 1. PANTALLA PRINCIPAL (La Sábana Editable)
    // =========================================================================
    public function editar($id_grado)
    {
        $session = session();

        // 1. Se verifica que el usuario correcto esté logueado 
        if (!$session->has('id')) {
            return redirect()->to('/login'); 
        }

        // 2. Obtener el modelo
        $model = new CalificacionesModel();

        // 3. Obtener la sábana
        $data = $model->getSabana($id_grado);

        if (!$data) {
            return "Error: Grado no encontrado o sin configuración.";
        }

        // 4. Pasar el nivel real a la vista (para permisos)
        $data['user_level'] = $session->get('nivel');

        return view('boletas/calificar_boleta', $data);
    }

    // =========================================================================
    // 2. ACTUALIZACIÓN AJAX (Edición Celda por Celda)
    // =========================================================================
    public function actualizar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody("Prohibido");
        }

        $session = session();
        $id_usuario = $session->get('id'); // Tu ID (ej. 926)

        // 1. Seguridad
        if ($session->get('nivel') == 7) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No tienes permisos.']);
        }

        $request = $this->request;
        
        // 2. Recibir Datos
        $id_cal     = $request->getPost('scoreId');
        $valor      = $request->getPost('value');
        $tipo       = $request->getPost('type');
        
        // ⬅️ CLAVE 1: Recibimos el mes que mandaste desde el JavaScript
        $id_mes_post = $request->getPost('monthId'); 

        // Datos extra
        $id_alumno  = $request->getPost('studentId');
        $id_materia = $request->getPost('subjectId');
        $id_grado   = $request->getPost('gradeId');
        
        if (!isset($valor) || !$id_usuario) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Datos incompletos']);
        }

        $model = new CalificacionesModel();

        // ---------------------------------------------------------
        // ESCENARIO A: ACTUALIZACIÓN (UPDATE)
        // ---------------------------------------------------------
        if (!empty($id_cal)) {
            // Actualizamos la calificación existente
            // IMPORTANTE: Aquí NO cambiamos el mes, porque el registro ya existe en su mes correcto.
            $resultado = $model->updateCalificacion($id_cal, $tipo, $valor, $id_usuario);
            
            if ($resultado) {
                return $this->response->setJSON(['status' => 'success', 'action' => 'update', 'msg' => 'Actualizado']);
            }
        } 
        // ---------------------------------------------------------
        // ESCENARIO B: INSERCIÓN (INSERT) - AQUÍ ESTABA EL ERROR
        // ---------------------------------------------------------
        else {
            if(!$id_alumno || !$id_materia || !$id_grado) {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'Faltan datos']);
            }

            // Consultamos la configuración global como respaldo
            $gradoInfo = $model->db->table('grados')->select('nivel_grado')->where('id_grado', $id_grado)->get()->getRow();
            $config    = $model->getConfiguracionActiva($gradoInfo->nivel_grado);

            // ⬅️ CLAVE 2: LÓGICA DE PRIORIDAD
            // Si $id_mes_post tiene datos (viene del JS), úsalo. Si no, usa el del Director ($config).
            $id_mes_final = !empty($id_mes_post) ? $id_mes_post : $config['id_mes'];

            $dataInsert = [
                'id_usr'        => $id_alumno,
                'id_materia'    => $id_materia,
                'id_grado'      => $id_grado,
                'cicloEscolar'  => $config['id_ciclo'], // El ciclo sí suele ser el mismo
                'id_mes'        => $id_mes_final,       // ⬅️ CLAVE 3: Guardamos el mes correcto
                'fechaInsertar' => date('Y-m-d H:i:s'),
                'bandera'       => $id_usuario,         // Tu ID para la jerarquía
            ];

            if ($tipo === 'score') {
                $dataInsert['calificacion'] = $valor;
                $dataInsert['faltas'] = 0;
            } else {
                $dataInsert['faltas'] = $valor;
                $dataInsert['calificacion'] = 0;
            }

            $newId = $model->crearCalificacion($dataInsert);

            if ($newId) {
                return $this->response->setJSON([
                    'status' => 'success', 
                    'action' => 'insert', 
                    'newId'  => $newId,
                    'msg'    => 'Registrado'
                ]);
            }
        }

        return $this->response->setJSON(['status' => 'error', 'msg' => 'No se pudo guardar']);
    }

    // =========================================================================
    // 3. EXPORTAR PLANTILLA (CSV) - LÓGICA INTELIGENTE
    // =========================================================================
    // =========================================================================
    // 3. EXPORTAR PLANTILLA CON DATOS (Para editar masivamente)
    // =========================================================================
    public function exportarPlantilla($id_grado)
    {
        $session = session();
        if (!$session->has('id')) return redirect()->to('/login');

        // 1. Recibir el Mes Customizado
        $mes_custom = $this->request->getGet('mes_custom');

        $model = new CalificacionesModel();
        
        // 2. Obtener Info del Grado
        $gradoInfo = $model->db->table('grados')->where('id_grado', $id_grado)->get()->getRow();
        if (!$gradoInfo) return "Grado no encontrado";

        // 3. Obtener Configuración
        $config = $model->getConfiguracionActiva($gradoInfo->nivel_grado);

        // --- SOBRESCRITURA DEL MES (Override) ---
        if ($mes_custom && is_numeric($mes_custom)) {
            $config['id_mes'] = $mes_custom;

            // Obtener nombre del mes
            $nivel = $gradoInfo->nivel_grado;
            if ($nivel == 5) { // Bachillerato
                $row = $model->db->table('bimestres')->select('nombre')->where('id', $mes_custom)->get()->getRow();
                if($row) $config['nombre_mes'] = $row->nombre;
            } elseif ($nivel == 1) { // Kinder
                $config['nombre_mes'] = $mes_custom . " Evaluacion";
            } else { // Primaria/Secundaria
                $row = $model->db->table('mes')->select('nombre')->where('id', $mes_custom)->get()->getRow();
                if($row) $config['nombre_mes'] = $row->nombre;
            }
        }
        // ----------------------------------------

        $nombre_archivo = "Plantilla_" . str_replace(' ', '_', $gradoInfo->nombreGrado) . "_" . str_replace(' ', '', $config['nombre_mes']) . ".csv";
        $id_ciclo = $config['id_ciclo'];
        $id_mes   = $config['id_mes']; 

        // 4. Obtener Materias (Columnas)
        $materias = $model->db->table('materia')
            ->select('id_materia, nombre_materia')
            ->where('id_grados', $id_grado)
            ->orderBy('orden', 'ASC')
            ->get()->getResultArray();

        // 5. Obtener Alumnos (Filas)
        $alumnos = $model->db->table('usr')
            ->select('id, matricula, ap_Alumno, am_Alumno, Nombre')
            ->where('grado', $id_grado)
            ->where('estatus', 1)
            ->where('nivel', 7) 
            ->orderBy('ap_Alumno, am_Alumno, Nombre')
            ->get()->getResultArray();

        // ---------------------------------------------------------------------
        // 🟢 NUEVO: OBTENER CALIFICACIONES EXISTENTES
        // ---------------------------------------------------------------------
        $notasRaw = $model->db->table('calificacion')
            ->select('id_usr, id_materia, calificacion')
            ->where('id_grado', $id_grado)
            ->where('cicloEscolar', $id_ciclo)
            ->where('id_mes', $id_mes) // <--- Clave: Solo las del mes seleccionado
            ->get()->getResultArray();

        // Convertimos a un Mapa para búsqueda rápida: $mapa[id_alumno][id_materia] = calificacion
        $mapaNotas = [];
        foreach ($notasRaw as $row) {
            $mapaNotas[$row['id_usr']][$row['id_materia']] = $row['calificacion'];
        }
        // ---------------------------------------------------------------------

        // 6. GENERAR CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $nombre_archivo);

        $output = fopen('php://output', 'w');

        // A. ENCABEZADOS (Orden visual preferido)
        $csv_headers = ['id_sistema', 'matricula', 'nombre_completo', 'id_grado', 'id_ciclo', 'id_mes'];

        foreach ($materias as $mat) {
            $cleanName = preg_replace('/[^A-Za-z0-9 ]/', '', $mat['nombre_materia']);
            $csv_headers[] = strtoupper($cleanName) . '_' . $mat['id_materia'];
        }

        fputcsv($output, $csv_headers);

        // B. DATOS
        foreach ($alumnos as $alumno) {
            $nombreCompleto = $alumno['ap_Alumno'] . ' ' . $alumno['am_Alumno'] . ' ' . $alumno['Nombre'];
            
            $fila = [
                $alumno['id'],       
                $alumno['matricula'],
                $nombreCompleto,     
                $id_grado,           
                $id_ciclo,           
                $id_mes              
            ];

            // Rellenar materias con DATOS REALES del mapa
            foreach ($materias as $m) {
                $id_alumno  = $alumno['id'];
                $id_materia = $m['id_materia'];

                // Si existe nota en el mapa, la ponemos. Si no, va vacío.
                if (isset($mapaNotas[$id_alumno][$id_materia])) {
                    $fila[] = $mapaNotas[$id_alumno][$id_materia];
                } else {
                    $fila[] = ''; // Casilla vacía para calificar
                }
            }

            fputcsv($output, $fila);
        }

        fclose($output);
        exit(); 
    }

    // Helper para limpiar acentos (simple)
    private function limpiarTexto($cadena) {
        $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
        $modificadas = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
        return strtr(utf8_decode($cadena), utf8_decode($originales), $modificadas);
    }

    // Helper privado para traducir ID de mes a Nombre legible
    private function _obtenerNombreMes($id_mes, $nivel_grado) {
        $db = \Config\Database::connect();
        $nombre = "Desconocido ($id_mes)";

        if ($nivel_grado == 5) { // Bachillerato (Tabla bimestres)
            $row = $db->table('bimestres')->select('nombre')->where('id', $id_mes)->get()->getRow();
            if ($row) $nombre = $row->nombre;
        } elseif ($nivel_grado == 1) { // Kinder
            $nombre = $id_mes . "° Evaluación";
        } else { // Primaria/Secundaria (Tabla mes)
            $row = $db->table('mes')->select('mes')->where('id', $id_mes)->get()->getRow();
            if ($row) $nombre = $row->mes; // O 'nombre' según tu tabla
        }
        return $nombre;
    }

    // =========================================================================
    // 4. IMPORTAR CALIFICACIONES (Lógica Inteligente: Asignación vs Corrección)
    // =========================================================================
    public function importar()
    {
        $session = session();
        if (!$session->has('id')) return redirect()->to('/login');

        // 1. Validar Archivo
        $file = $this->request->getFile('archivo_csv');
        if (!$file->isValid() || $file->getExtension() !== 'csv') {
            return redirect()->back()->with('error', 'El archivo no es válido o no es CSV.');
        }

        // Datos esperados por la vista
        $id_grado_esperado = $this->request->getPost('id_grado_actual');
        $id_mes_esperado   = $this->request->getPost('id_mes_esperado');

        $handle = fopen($file->getTempName(), 'r');
        if (!$handle) return redirect()->back()->with('error', 'No se pudo leer el archivo.');

        // 2. Leer Encabezados
        $headers = fgetcsv($handle); 
        $idx_id_usr   = array_search('id_sistema', $headers);
        $idx_id_grado = array_search('id_grado', $headers);
        $idx_id_ciclo = array_search('id_ciclo', $headers);
        $idx_id_mes   = array_search('id_mes', $headers);

        if ($idx_id_usr === false || $idx_id_grado === false || $idx_id_mes === false) {
             fclose($handle);
             return redirect()->back()->with('error', 'Formato incorrecto: Faltan columnas clave (id_sistema, id_grado, id_mes).');
        }

        $model = new CalificacionesModel();
        
        // CONTADORES HUMANOS
        $countNuevos = 0;      // Estaba en 0 y cambió a valor, O no existía registro
        $countCambios = 0;     // Tenía valor y cambió a otro valor

        // Leer primera fila para validar contexto
        $firstRow = fgets($handle); 
        rewind($handle); 
        fgetcsv($handle); // Saltar headers

        while (($row = fgetcsv($handle)) !== false) {
            
            $csv_id_alumno = $row[$idx_id_usr];
            $csv_id_grado  = $row[$idx_id_grado];
            $csv_id_mes    = $row[$idx_id_mes];
            $csv_id_ciclo  = $row[$idx_id_ciclo];

            // -----------------------------------------------------------------
            // 🔒 CANDADO DE SEGURIDAD (HUMANIZADO)
            // -----------------------------------------------------------------
            
            // Validar Grado
            if ($csv_id_grado != $id_grado_esperado) {
                fclose($handle);
                // Obtenemos nombres de grados para el mensaje
                $gActual = $model->db->table('grados')->select('nombreGrado')->where('id_grado', $id_grado_esperado)->get()->getRow();
                $gArchivo = $model->db->table('grados')->select('nombreGrado')->where('id_grado', $csv_id_grado)->get()->getRow();
                
                $txtActual = $gActual ? $gActual->nombreGrado : "ID $id_grado_esperado";
                $txtArchivo = $gArchivo ? $gArchivo->nombreGrado : "ID $csv_id_grado";

                return redirect()->back()->with('error', "ERROR DE SEGURIDAD: Estás subiendo un archivo de <b>$txtArchivo</b> en la pantalla de <b>$txtActual</b>.");
            }

            // Validar Mes
            if ($csv_id_mes != $id_mes_esperado) {
                fclose($handle);
                
                // Consultamos el nivel del grado para saber en qué tabla buscar el nombre del mes
                $gradoInfo = $model->db->table('grados')->select('nivel_grado')->where('id_grado', $id_grado_esperado)->get()->getRow();
                $nivel = $gradoInfo ? $gradoInfo->nivel_grado : 0;

                // Usamos la función auxiliar para obtener nombres reales
                $nombreMesArchivo = $this->_obtenerNombreMes($csv_id_mes, $nivel);
                $nombreMesPantalla = $this->_obtenerNombreMes($id_mes_esperado, $nivel);

                return redirect()->back()->with('error', "ERROR DE SEGURIDAD: El archivo corresponde a <b>$nombreMesArchivo</b>, pero estás ubicado en la sección de <b>$nombreMesPantalla</b>. Verifica tu selección.");
            }
            // -----------------------------------------------------------------

            // Procesar Materias
            foreach ($headers as $index => $header) {
                if (preg_match('/_(\d+)$/', $header, $matches)) {
                    $id_materia = $matches[1];
                    $valorNuevo = trim($row[$index]); // El valor que viene del Excel

                    // Solo procesamos si es numérico (ignoramos celdas vacías del excel si las hubiera)
                    if ($valorNuevo !== '' && is_numeric($valorNuevo)) {
                        
                        // Buscamos calificación existente en BD
                        $existe = $model->db->table('calificacion')
                            ->select('Id_cal, calificacion')
                            ->where('id_usr', $csv_id_alumno)
                            ->where('id_materia', $id_materia)
                            ->where('id_grado', $csv_id_grado)
                            ->where('id_mes', $csv_id_mes)
                            ->where('cicloEscolar', $csv_id_ciclo)
                            ->get()->getRow();

                        if ($existe) {
                            $valorAnterior = floatval($existe->calificacion); // Lo que hay en BD
                            $valorNuevoFloat = floatval($valorNuevo);        // Lo que viene del Excel

                            // Si son iguales, NO HACEMOS NADA (Ni update, ni contar)
                            if ($valorAnterior == $valorNuevoFloat) {
                                continue; 
                            }

                            // UPDATE SQL
                            $model->updateCalificacion($existe->Id_cal, 'score', $valorNuevo, $session->get('id'));

                            // LOGICA DE CONTADORES INTELIGENTE
                            // Si antes era 0 y ahora es algo (ej: 8), cuenta como NUEVO para el usuario
                            if ($valorAnterior == 0 && $valorNuevoFloat >= 0) {
                                $countNuevos++;
                            } else {
                                // Si antes era 8 y ahora es 9 (o 0), cuenta como CAMBIO
                                $countCambios++;
                            }

                        } else {
                            // INSERT SQL (Siempre es nuevo)
                            $dataInsert = [
                                'id_usr'        => $csv_id_alumno,
                                'id_materia'    => $id_materia,
                                'id_grado'      => $csv_id_grado,
                                'cicloEscolar'  => $csv_id_ciclo,
                                'id_mes'        => $csv_id_mes,
                                'calificacion'  => $valorNuevo,
                                'faltas'        => 0,
                                'fechaInsertar' => date('Y-m-d H:i:s'),
                                'bandera'       => $session->get('id')
                            ];
                            $model->crearCalificacion($dataInsert);
                            $countNuevos++;
                        }
                    }
                }
            }
        }

        fclose($handle);

        if ($countCambios > 0 || $countNuevos > 0) {
            return redirect()->back()->with('mensaje', "Importación exitosa: <b>$countNuevos</b> calificaciones nuevas, <b>$countCambios</b> correcciones.");
        } else {
            return redirect()->back()->with('error', 'El archivo se procesó pero no contenía cambios ni calificaciones nuevas.');
        }
    }
}