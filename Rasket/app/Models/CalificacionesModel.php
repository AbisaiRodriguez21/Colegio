<?php namespace App\Models;

use CodeIgniter\Model;

class CalificacionesModel extends Model
{
    protected $table = 'calificacion';
    protected $primaryKey = 'Id_cal';
    protected $allowedFields = ['calificacion', 'faltas', 'bandera', 'fechaInsertar', 'comentarios'];

    // =========================================================================
    // 1. CONFIGURACIÓN DEL CICLO 
    // =========================================================================
    public function getConfiguracionActiva($nivel_grado)
    {
        // ID 1 = Primaria/Secundaria | ID 2 = Bachillerato | ID 3 = Kinder
        $id_config = 1; 
        if ($nivel_grado == 5) { $id_config = 2; } 
        elseif ($nivel_grado <= 2) { $id_config = 3; } 

        $builder = $this->db->table('mesycicloactivo MA');
        $builder->select('MA.id_mes, MA.id_ciclo, M.nombre as nombre_mes, CE.nombreCicloEscolar');
        $builder->join('mes M', 'MA.id_mes = M.id');
        $builder->join('cicloescolar CE', 'MA.id_ciclo = CE.id_cicloEscolar');
        $builder->where('MA.id', $id_config);

        return $builder->get()->getRowArray();
    }

    // =========================================================================
    // 2. CONSTRUIR LA SÁBANA DE CALIFICACIONES
    // =========================================================================
    public function getSabana($id_grado)
    {
        // A. Obtener Info del Grado y su Configuración JSON
        $grado = $this->db->table('grados')
            ->select('id_grado, nombreGrado, nivel_grado, sabana_calif_config')
            ->where('id_grado', $id_grado)
            ->get()->getRowArray();

        if (!$grado) return null;

        // Se decodifica el JSON 
        $configJson = json_decode($grado['sabana_calif_config'] ?? '{"groups":[]}', true);
        
        // Obtenemos qué Mes y Ciclo están abiertos para captura
        $activeConfig = $this->getConfiguracionActiva($grado['nivel_grado']);

        // B. Obtener Catálogo de Materias (ID => Nombre)
        $materiasRaw = $this->db->table('materia')
            ->select('id_materia, nombre_materia')
            ->where('id_grados', $id_grado)
            ->orderBy('orden', 'ASC')
            ->get()->getResultArray();

        $materiasMap = [];
        foreach($materiasRaw as $m) {
            // Normalizar nombres para evitar problemas de acentos
            $materiasMap[$m['id_materia']] = html_entity_decode($m['nombre_materia']);
        }

        // C. Obtener Alumnos y sus Calificaciones 
        $id_ciclo = $activeConfig['id_ciclo'];
        $id_mes   = $activeConfig['id_mes'];

        $sql = "SELECT 
                    u.id as id_alumno,
                    u.matricula,
                    CONCAT(IFNULL(u.ap_Alumno,''), ' ', IFNULL(u.am_Alumno,''), ' ', u.Nombre) as nombre_completo,
                    c.Id_cal,
                    c.id_materia,
                    c.calificacion,
                    c.faltas,
                    c.bandera
                FROM usr u
                LEFT JOIN calificacion c ON u.id = c.id_usr 
                     AND c.id_grado = ? 
                     AND c.cicloEscolar = ? 
                     AND c.id_mes = ?
                WHERE u.grado = ? 
                  AND u.activo = 1 
                  AND u.nivel = 7
                  AND u.generacionactiva = 11  
                ORDER BY u.ap_Alumno, u.am_Alumno, u.Nombre";

        // Ejecutar consulta
        $query = $this->db->query($sql, [$id_grado, $id_ciclo, $id_mes, $id_grado]);
        $resultados = $query->getResultArray();

        // D. Estructurar Datos para la Vista
        // lista plana de SQL en un Array Indexado por Alumno
        $sabana = [];
        foreach ($resultados as $row) {
            $id_al = $row['id_alumno'];
            
            // Si es la primera vez que vemos a este alumno, creamos su fila
            if (!isset($sabana[$id_al])) {
                $sabana[$id_al] = [
                    'id' => $id_al,
                    'matricula' => $row['matricula'],
                    'nombre' => strtoupper($row['nombre_completo']),
                    'notas' => [] // Aquí se guardan las materias
                ];
            }

            // Si el alumno tiene calificación registrada la guardamos
            if ($row['Id_cal']) {
                $sabana[$id_al]['notas'][$row['id_materia']] = [
                    'id_cal'       => $row['Id_cal'],       // ID clave para editar
                    'calificacion' => $row['calificacion'],
                    'faltas'       => $row['faltas'],
                    'bandera'      => $row['bandera']       // Quién la editó
                ];
            }
        }

        return [
            'grado_info'  => $grado,
            'ciclo_info'  => $activeConfig,
            'config_json' => $configJson,  // Estructura de grupos
            'materias_map'=> $materiasMap, // Nombres de materias
            'alumnos'     => $sabana       // Datos listos
        ];
    }

    // =========================================================================
    // 3. ACTUALIZAR UNA CELDA
    // =========================================================================
    public function updateCalificacion($id_cal, $tipo, $valor, $id_usuario)
    {
        // ID del usuario que edita.
        $bandera = $id_usuario;

        $data = [];
        
        // Caso 1: Editar Calificación
        if ($tipo === 'score') {
            $data = [
                'calificacion'  => $valor,
                'fechaInsertar' => date('Y-m-d H:i:s'),
                'bandera'       => $bandera 
            ];
        } 
        // Caso 2: Editar Faltas
        elseif ($tipo === 'absence') {
            $data = [
                'faltas' => $valor,
                'bandera' => $bandera,
                'fechaInsertar' => date('Y-m-d H:i:s')
            ];
        }

        return $this->update($id_cal, $data);
    }
}