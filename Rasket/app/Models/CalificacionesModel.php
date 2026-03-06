<?php namespace App\Models;

use CodeIgniter\Model;

class CalificacionesModel extends Model
{
    protected $table = 'calificacion';
    protected $primaryKey = 'Id_cal';

    protected $allowedFields = [
        'id_usr', 
        'id_materia', 
        'id_grado', 
        'id_mes', 
        'cicloEscolar', 
        'calificacion', 
        'faltas', 
        'bandera', 
        'fechaInsertar', 
        'comentarios'
    ];
    
    // =========================================================================
    // 1. CONFIGURACIÓN DEL CICLO 
    // =========================================================================
    public function getConfiguracionActiva($nivel_grado)
    {
        // ID 1 = Primaria/Secundaria | ID 2 = Bachillerato | ID 3 = Kinder
        $id_config = 1; 
        if ($nivel_grado == 5) { $id_config = 2; } 
        elseif ($nivel_grado == 2) { $id_config = 3; }  

        $builder = $this->db->table('mesycicloactivo MA');
        $builder->select('MA.id_mes, MA.id_ciclo, CE.nombreCicloEscolar');
        $builder->join('cicloescolar CE', 'MA.id_ciclo = CE.id_cicloEscolar');
        $builder->where('MA.id', $id_config);

        $data = $builder->get()->getRowArray();

        if (!$data) return null;

        if ($nivel_grado == 5) { 
            // BACHILLERATO: Consultamos 'bimestres'
            $bimestre = $this->db->table('bimestres')->select('nombre')->where('id', $data['id_mes'])->get()->getRow();
            $nombreReal = $bimestre ? $bimestre->nombre : 'Periodo Desconocido';
            $data['nombre_mes'] = $data['id_mes'] . '° Periodo (' . $nombreReal . ')';

        } elseif ($nivel_grado == 2) {
            // KINDER: Consultamos 'momentos'
            $momento = $this->db->table('momentos')->select('nombre')->where('id', $data['id_mes'])->get()->getRow();
            $data['nombre_mes'] = $momento ? $momento->nombre : $data['id_mes'] . '° Evaluación';

        } else {
            // PRIMARIA/SECUNDARIA: Consultamos 'mes'
            $mesInfo = $this->db->table('mes')->select('nombre')->where('id', $data['id_mes'])->get()->getRow();
            $data['nombre_mes'] = $mesInfo ? $mesInfo->nombre : 'Mes Desconocido';
        }

        return $data;
    }

    // =========================================================================
    // 2. CONSTRUIR LA SÁBANA DE CALIFICACIONES 
    // =========================================================================
    public function getSabana($id_grado, $mes_custom = null)
    {
        // A. Obtener Info del Grado y su Configuración JSON
        $grado = $this->db->table('grados')
            ->select('id_grado, nombreGrado, nivel_grado, sabana_calif_config')
            ->where('id_grado', $id_grado)
            ->get()->getRowArray();

        if (!$grado) return null;

        // Se decodifica el JSON 
        $configJson = json_decode($grado['sabana_calif_config'] ?? '{"groups":[]}', true);
        
        // Obtenemos configuración por defecto (lo que dicta el Admin)
        $activeConfig = $this->getConfiguracionActiva($grado['nivel_grado']);

        // ---------------------------------------------------------------------
        // SOBRESCRITURA INTELIGENTE DEL MES
        // ---------------------------------------------------------------------
        // Si el controlador nos mandó un mes específico 
        if ($mes_custom !== null && is_numeric($mes_custom)) {
            
            $activeConfig['id_mes'] = $mes_custom;
            $nivel = $grado['nivel_grado'];
            
            if ($nivel == 5) { // BACHILLERATO
                $rowB = $this->db->table('bimestres')->select('nombre')->where('id', $mes_custom)->get()->getRow();
                if ($rowB) $activeConfig['nombre_mes'] = $rowB->nombre;  
            
            } elseif ($nivel == 2) { // 🌟 KINDER (Corregido a 2 y usando 'momentos')
                $rowK = $this->db->table('momentos')->select('nombre')->where('id', $mes_custom)->get()->getRow();
                if ($rowK) {
                    $activeConfig['nombre_mes'] = $rowK->nombre;
                } else {
                    $activeConfig['nombre_mes'] = $mes_custom . "° Evaluación";
                }
            
            } else { // PRIMARIA/SECUNDARIA
                $rowM = $this->db->table('mes')->select('nombre')->where('id', $mes_custom)->get()->getRow();
                if ($rowM) $activeConfig['nombre_mes'] = $rowM->nombre;
            }
        }
        
        // --------------------------------------------------------------------- 
        // B. Obtener Catálogo de Materias  
        $materiasRaw = $this->db->table('materia')
            ->select('id_materia, nombre_materia')
            ->where('id_grados', $id_grado)
            ->orderBy('orden', 'ASC')
            ->get()->getResultArray();

        $materiasMap = [];
        
        $es_bachillerato = ($grado['nivel_grado'] == 5);
        $id_mes_actual = $activeConfig['id_mes'];

        foreach($materiasRaw as $m) {
            $nombre_crudo = html_entity_decode($m['nombre_materia']);

            // Explode | 
            if (strpos($nombre_crudo, '|') !== false) {
                $partes = explode('|', $nombre_crudo);
                
                // Si es el 2do Semestre (Periodos 4, 5 o 6)
                if ($es_bachillerato && in_array($id_mes_actual, [4, 5, 6]) && isset($partes[1])) {
                    $materiasMap[$m['id_materia']] = trim($partes[1]);
                } else {
                // si no, es semestre 1
                    $materiasMap[$m['id_materia']] = trim($partes[0]);
                }
            } else {
                $materiasMap[$m['id_materia']] = trim($nombre_crudo);
            }
        }

        // C. Obtener Alumnos y sus Calificaciones 
        $id_ciclo = $activeConfig['id_ciclo'];
        
        // AQUÍ ES DONDE SE USA EL MES (Ya sea el global o el customizado)
        $id_mes   = $activeConfig['id_mes']; 

        
        $sql = "SELECT 
                    u.id as id_alumno,
                    u.matricula,
                    CONCAT(IFNULL(u.ap_Alumno,''), ' ', IFNULL(u.am_Alumno,''), ' ', u.Nombre) as nombre_completo,
                    c.Id_cal,
                    c.id_materia,
                    c.calificacion,
                    c.faltas,
                    c.bandera,
                    editor.nivel as nivel_editor 
                FROM usr u
                LEFT JOIN calificacion c ON u.id = c.id_usr 
                      AND c.id_grado = ? 
                      AND c.cicloEscolar = ? 
                      AND c.id_mes = ?
                LEFT JOIN usr editor ON c.bandera = editor.id 
                WHERE u.grado = ? 
                  AND u.estatus = 1  
                  AND u.nivel = 7    
                ORDER BY u.ap_Alumno, u.am_Alumno, u.Nombre";

        // Ejecutar consulta
        $query = $this->db->query($sql, [$id_grado, $id_ciclo, $id_mes, $id_grado]);
        $resultados = $query->getResultArray();

        // D. Estructurar Datos para la Vista
        $sabana = [];
        foreach ($resultados as $row) {
            $id_al = $row['id_alumno'];
            
            if (!isset($sabana[$id_al])) {
                $sabana[$id_al] = [
                    'id' => $id_al,
                    'matricula' => $row['matricula'],
                    'nombre' => strtoupper($row['nombre_completo']),
                    'notas' => [] 
                ];
            }

            // Si el alumno tiene calificación registrada la guardamos
            if ($row['Id_cal']) {
                $sabana[$id_al]['notas'][$row['id_materia']] = [
                    'id_cal'       => $row['Id_cal'],       
                    'calificacion' => $row['calificacion'],
                    'faltas'       => $row['faltas'],
                    'bandera'      => $row['bandera'],
                    'nivel_editor' => $row['nivel_editor'] 
                ];
            }
        }

        return [
            'grado_info'  => $grado,
            'ciclo_info'  => $activeConfig,
            'config_json' => $configJson,  
            'materias_map'=> $materiasMap, 
            'alumnos'     => $sabana      
        ];
    }

    // =========================================================================
    // 3. ACTUALIZAR UNA CELDA (UPDATE)
    // =========================================================================
    public function updateCalificacion($id_cal, $tipo, $valor, $id_usuario)
    {
        // ID del usuario que edita.
        $bandera = $id_usuario;

        $data = [];
        
        // Editar Calificación
        if ($tipo === 'score') {
            $data = [
                'calificacion'  => $valor,
                'fechaInsertar' => date('Y-m-d H:i:s'),
                'bandera'       => $bandera 
            ];
        } 
        // Editar Faltas
        elseif ($tipo === 'absence') {
            $data = [
                'faltas' => $valor,
                'bandera' => $bandera,
                'fechaInsertar' => date('Y-m-d H:i:s')
            ];
        }

        return $this->update($id_cal, $data);
    }

    // =========================================================================
    // 4. CREAR UNA NUEVA CALIFICACIÓN (INSERT)
    // =========================================================================
    public function crearCalificacion($datos)
    {
        $this->insert($datos);
        return $this->getInsertID(); 
    }
}