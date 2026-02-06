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
        
        // 1. Seguridad
        if ($session->get('nivel') == 7) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No tienes permisos.']);
        }

        $request = $this->request;
        
        // 2. Recibir Datos
        $id_cal     = $request->getPost('scoreId'); // Puede venir vacío si es nuevo
        $valor      = $request->getPost('value');
        $tipo       = $request->getPost('type');
        $id_usuario = $session->get('id');

        // Datos extra necesarios para INSERT (que enviaremos desde la vista)
        $id_alumno  = $request->getPost('studentId');
        $id_materia = $request->getPost('subjectId');
        $id_grado   = $request->getPost('gradeId');
        
        // Validar valor
        if (!isset($valor) || !$id_usuario) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Datos incompletos']);
        }

        $model = new CalificacionesModel();

        // ---------------------------------------------------------
        // ESCENARIO A: ACTUALIZACIÓN (Ya existe ID)
        // ---------------------------------------------------------
        if (!empty($id_cal)) {
            $resultado = $model->updateCalificacion($id_cal, $tipo, $valor, $id_usuario);
            
            if ($resultado) {
                return $this->response->setJSON(['status' => 'success', 'action' => 'update', 'msg' => 'Actualizado']);
            }
        } 
        // ---------------------------------------------------------
        // ESCENARIO B: INSERCIÓN (No existe ID, es la primera vez)
        // ---------------------------------------------------------
        else {
            // Validamos que tengamos los datos estructurales
            if(!$id_alumno || !$id_materia || !$id_grado) {
                return $this->response->setJSON(['status' => 'error', 'msg' => 'Faltan datos para crear el registro']);
            }

            // Obtenemos la configuración activa ACTUAL para saber en qué mes/ciclo guardar
            // OJO: Podríamos pasarlo desde la vista, pero es más seguro recalcularlo aquí
            // para evitar que inyecten datos en ciclos cerrados.
            $gradoInfo = $model->db->table('grados')->select('nivel_grado')->where('id_grado', $id_grado)->get()->getRow();
            $config    = $model->getConfiguracionActiva($gradoInfo->nivel_grado);

            $dataInsert = [
                'id_usr'        => $id_alumno,
                'id_materia'    => $id_materia,
                'id_grado'      => $id_grado,
                'cicloEscolar'  => $config['id_ciclo'],
                'id_mes'        => $config['id_mes'],
                'fechaInsertar' => date('Y-m-d H:i:s'),
                'bandera'       => $id_usuario,
            ];

            // Asignar valor según tipo
            if ($tipo === 'score') {
                $dataInsert['calificacion'] = $valor;
                $dataInsert['faltas'] = 0;
            } else {
                $dataInsert['faltas'] = $valor;
                $dataInsert['calificacion'] = 0;
            }

            $newId = $model->crearCalificacion($dataInsert);

            if ($newId) {
                // Retornamos el nuevo ID para que el JS actualice la celda
                // y la próxima vez que editen, ya sea un UPDATE y no otro INSERT
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
    // 3. EXPORTAR PLANTILLA CSV (Generador Inteligente)
    // =========================================================================
    public function exportarPlantilla($id_grado)
    {
        $session = session();
        
        // 1. Seguridad básica
        if (!$session->has('id')) return redirect()->to('/login');

        // 2. Obtener los datos (Reutilizamos la lógica de la Sábana)
        $model = new CalificacionesModel();
        $data  = $model->getSabana($id_grado);

        if (!$data) return "Error: No hay datos para exportar.";

        // Extraer variables clave
        $alumnos     = $data['alumnos'];      // Ya vienen ordenados A-Z
        $materiasMap = $data['materias_map']; // ID => Nombre Real
        $configJson  = $data['config_json'];  // Estructura de grupos
        $cicloInfo   = $data['ciclo_info'];   // IDs de contexto (Mes/Ciclo activos)
        $gradoInfo   = $data['grado_info'];

        // 3. Preparar el archivo CSV
        $filename = 'Plantilla_' . $gradoInfo['nombreGrado'] . '_' . date('Ymd_His') . '.csv';
        
        // Forzar descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Abrir salida
        $output = fopen('php://output', 'w');

        // BOM para que Excel reconozca acentos (UTF-8)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // ---------------------------------------------------------
        // A. CONSTRUIR ENCABEZADOS
        // ---------------------------------------------------------
        
        // Columnas Fijas (Candados de Seguridad)
        // id_sistema = id_usr (para el update rápido)
        $headers = ['id_sistema', 'matricula', 'nombre_completo', 'id_grado', 'id_ciclo', 'id_mes'];
        
        // Columnas Dinámicas (Materias)
        $listaMateriasOrdenadas = []; // Guardamos el orden para luego llenar las filas

        foreach ($configJson['groups'] as $grupo) {
            if (empty($grupo['subjects'])) continue;

            foreach ($grupo['subjects'] as $id_mat) {
                // Manejo de ID si viene en array o simple
                $real_id = is_array($id_mat) ? ($id_mat['id'] ?? 0) : $id_mat;
                
                // Obtener nombre real
                $nombreRaw = $materiasMap[$real_id] ?? 'MATERIA_'.$real_id;

                // --- ALGORITMO ACORTADOR DE NOMBRES ---
                // 1. Convertir a mayúsculas y quitar acentos básicos
                $cleanName = strtoupper($this->limpiarTexto($nombreRaw));
                // 2. Quedarnos solo con letras y números (quitar / . , -)
                $cleanName = preg_replace('/[^A-Z0-9]/', '', $cleanName);
                // 3. Cortar a 20 caracteres
                $shortName = substr($cleanName, 0, 20);
                // 4. PEGAR EL ID (CRUCIAL PARA LA IMPORTACIÓN)
                $headerFinal = $shortName . '_' . $real_id;

                $headers[] = $headerFinal;
                $listaMateriasOrdenadas[] = $real_id; // Guardamos el ID para saber qué escribir en la fila
            }
        }

        // Escribir encabezados en el archivo
        fputcsv($output, $headers);

        // ---------------------------------------------------------
        // B. CONSTRUIR FILAS (ALUMNOS)
        // ---------------------------------------------------------
        foreach ($alumnos as $alumno) {
            $row = [];

            // 1. Datos Fijos (Validación Contextual)
            $row[] = $alumno['id'];             // id_sistema
            $row[] = $alumno['matricula'];      // matricula
            $row[] = $alumno['nombre'];         // nombre_completo (Visual)
            $row[] = $gradoInfo['id_grado'];    // Candado Grado
            $row[] = $cicloInfo['id_ciclo'];    // Candado Ciclo
            $row[] = $cicloInfo['id_mes'];      // Candado Mes (Periodo)

            // 2. Datos Dinámicos (Calificaciones)
            foreach ($listaMateriasOrdenadas as $idMateria) {
                // Verificamos si ya tiene nota en la sábana
                $nota = isset($alumno['notas'][$idMateria]) ? $alumno['notas'][$idMateria]['calificacion'] : '';
                $row[] = $nota;
            }

            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    // Helper para limpiar acentos (simple)
    private function limpiarTexto($cadena) {
        $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
        $modificadas = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
        return strtr(utf8_decode($cadena), utf8_decode($originales), $modificadas);
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
        $idGradoActual = $this->request->getPost('id_grado_actual');

        if (!$file->isValid() || $file->getExtension() !== 'csv') {
            return redirect()->back()->with('error', 'Archivo inválido. Debe ser CSV.');
        }

        // 2. Obtener Configuración Activa del Sistema
        $model = new CalificacionesModel();
        
        $gradoInfo = $model->db->table('grados')->select('nivel_grado')->where('id_grado', $idGradoActual)->get()->getRow();
        $configActiva = $model->getConfiguracionActiva($gradoInfo->nivel_grado);
        
        $cicloReal = $configActiva['id_ciclo'];
        $mesReal   = $configActiva['id_mes'];

        // 3. Leer CSV
        $handle = fopen($file->getTempName(), 'r');
        
        // A. Leer Encabezados
        $headers = fgetcsv($handle);
        
        $idxSistema = array_search('id_sistema', $headers);
        $idxGrado   = array_search('id_grado', $headers);
        $idxCiclo   = array_search('id_ciclo', $headers);
        $idxMes     = array_search('id_mes', $headers);

        // Detectar materias
        $mapaMaterias = []; 
        foreach ($headers as $index => $colName) {
            $parts = explode('_', $colName);
            $possibleId = end($parts);
            if (is_numeric($possibleId) && !in_array($colName, ['id_sistema','id_grado','id_ciclo','id_mes'])) {
                $mapaMaterias[$index] = $possibleId;
            }
        }

        if ($idxSistema === false) {
            fclose($handle);
            return redirect()->back()->with('error', 'Formato inválido: Falta columna id_sistema.');
        }

        // B. Procesar Filas
        // Contadores Semánticos (Lo que le importa al maestro)
        $countNuevas     = 0; // Inserts + Updates desde 0
        $countCorregidas = 0; // Updates de valor a valor
        $countIgnoradas  = 0; // Sin cambios

        while (($row = fgetcsv($handle)) !== false) {
            $idAlumno = $row[$idxSistema];
            
            // --- CANDADO DE SEGURIDAD ---
            $csvMes   = $row[$idxMes];
            $csvCiclo = $row[$idxCiclo];
            $csvGrado = $row[$idxGrado];

            // 1. PRIMERO REVISAMOS EL GRADO (Lo más importante)
            if ($csvGrado != $idGradoActual) {
                 fclose($handle);
                 // Mensaje corto y claro
                 return redirect()->back()->with('error', "❌ Error: El archivo no corresponde al grado seleccionado.");
            }

            // 2. DESPUÉS REVISAMOS EL PERIODO (Mes y Ciclo)
            if ($csvMes != $mesReal || $csvCiclo != $cicloReal) {
                fclose($handle);
                // Mensaje corto y claro
                return redirect()->back()->with('error', "❌ Error: El archivo no corresponde al periodo (Mes/Ciclo) actual.");
            }

            // --- PROCESAR MATERIAS ---
            foreach ($mapaMaterias as $colIndex => $idMateria) {
                $calificacionCSV = trim($row[$colIndex] ?? '');

                // Si está vacío, saltamos
                if ($calificacionCSV === '') continue;

                // Verificar si ya existe registro
                $existe = $model->where([
                    'id_usr' => $idAlumno,
                    'id_materia' => $idMateria,
                    'id_mes' => $mesReal,
                    'cicloEscolar' => $cicloReal,
                    'id_grado' => $idGradoActual
                ])->first();

                if ($existe) {
                    // --- ANÁLISIS INTELIGENTE ---
                    $valorViejo = $existe['calificacion'];
                    
                    // Solo actuamos si son diferentes (Comparamos loose para 9 == 9.0)
                    if ($valorViejo != $calificacionCSV) {
                        
                        // ACTUALIZAR EN BD
                        $model->updateCalificacion($existe['Id_cal'], 'score', $calificacionCSV, $session->get('id'));

                        // CLASIFICAR PARA EL MENSAJE
                        // Si era 0, cuenta como "Nueva Asignación". Si era otro número, es "Corrección".
                        if ($valorViejo == 0) {
                            $countNuevas++;
                        } else {
                            $countCorregidas++;
                        }

                    } else {
                        // Son iguales, no hacemos nada
                        $countIgnoradas++;
                    }

                } else {
                    // --- INSERT (Siempre es Nueva Asignación) ---
                    $dataInsert = [
                        'id_usr'        => $idAlumno,
                        'id_materia'    => $idMateria,
                        'id_grado'      => $idGradoActual,
                        'cicloEscolar'  => $cicloReal,
                        'id_mes'        => $mesReal,
                        'calificacion'  => $calificacionCSV,
                        'faltas'        => 0,
                        'fechaInsertar' => date('Y-m-d H:i:s'),
                        'bandera'       => $session->get('id')
                    ];
                    $model->crearCalificacion($dataInsert);
                    $countNuevas++;
                }
            }
        }

        fclose($handle);

        // --- MENSAJE FINAL DETALLADO ---
        if ($countNuevas == 0 && $countCorregidas == 0) {
            $mensajeFinal = "El archivo se procesó pero <b>no se encontraron cambios</b> (todos los datos son idénticos a los actuales).";
        } else {
            // Construimos el mensaje dinámico
            $partes = [];
            if ($countNuevas > 0)     $partes[] = "✅ <b>$countNuevas asignadas</b>";
            if ($countCorregidas > 0) $partes[] = "✏️ <b>$countCorregidas corregidas</b>";
            
            $mensajeFinal = "Proceso terminado: " . implode(" y ", $partes) . ".";
        }

        return redirect()->back()->with('mensaje', $mensajeFinal);
    }
}