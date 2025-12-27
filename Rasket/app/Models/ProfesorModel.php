<?php namespace App\Models;

use CodeIgniter\Model;

class ProfesorModel extends Model
{
    protected $table = 'usr';
    protected $primaryKey = 'id';
    protected $allowedFields = ['Nombre', 'ap_Alumno', 'am_Alumno', 'email', 'pass', 'estatus', 'nivel'];

    // =========================================================================
    // 1. FUNCIONES AUXILIARES (CICLO ESCOLAR)
    // =========================================================================

    /**
     * Obtiene el ID del ciclo escolar más reciente (el mayor ID).
     * Basado en tu tabla 'cicloescolar'.
     */
    public function getCicloActivo()
    {
        $fila = $this->db->table('cicloescolar')
                         ->selectMax('id_cicloEscolar') // Busca el número más alto
                         ->get()
                         ->getRow();
                         
        return $fila ? $fila->id_cicloEscolar : 1; // Retorna el ID o 1 por defecto si falla
    }

    // =========================================================================
    // 2. LECTURA DE DATOS
    // =========================================================================

    /**
     * Obtiene profesores activos (nivel 9 o el que uses para profes)
     */
    public function getProfesoresActivos()
    {
        // Nota: Ajusta el nivel '5' o '9' según tu configuración real de profesores
        return $this->select('usr.*, estatus_usr.nombre AS nombre_nivel')
                    ->join('estatus_usr', 'usr.estatus = estatus_usr.Id')
                    ->where('usr.nivel', 5) // Ajusta si tus profes son nivel 5
                    ->where('usr.estatus', 1)
                    ->findAll();
    }

    /**
     * Datos básicos de un profesor específico
     */
    public function getProfesor($id)
    {
        return $this->db->table('usr')
            ->select('id, Nombre, ap_Alumno, am_Alumno')
            ->where('id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Verifica si el profesor tiene materias asignadas activas (Para botón eliminar).
     */
    public function tieneMaterias($idProfesor)
    {
        return $this->db->table('materia_asignadaoriginal')
            ->where('id_usr', $idProfesor)
            ->where('activo', 1)
            ->countAllResults() > 0;
    }

    /**
     * Obtiene las materias para la vista de "Ver Carga" (Solo lectura).
     */
    public function getMateriasAsignadas($id)
    {
        return $this->db->table('materia_asignadaoriginal MA')
            ->select('M.nombre_materia, G.nombreGrado, MA.activo')
            // Asumiendo que la tabla de nombres de materias es 'materiaoriginal' basado en tu historial
            // Si tu tabla de nombres es 'materia', cambia 'materiaoriginal' por 'materia' abajo.
            ->join('materia M', 'MA.id_materia = M.Id_materia', 'inner') 
            ->join('grados G', 'M.id_grados = G.id_grado', 'inner')
            ->where('MA.id_usr', $id)
            ->where('MA.activo', 1)
            ->get()
            ->getResultArray();
    }

    /**
     * Obtiene los grados para armar el acordeón (Nivel >= 3).
     */
    public function getGrados()
    {
        return $this->db->table('grados')
            ->where('nivel_grado >=', 3)
            ->orderBy('Id_grado', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Obtiene todas las materias disponibles de un grado específico.
     */
    public function getMateriasPorGrado($id_grado)
    {
        return $this->db->table('materia') 
            ->where('id_grados', $id_grado)
            ->orderBy('Id_materia', 'ASC')
            ->get()->getResultArray();
    }

    // =========================================================================
    // 3. LÓGICA DE ESTADOS Y ASIGNACIÓN
    // =========================================================================

    /**
     * Verifica estado para pintar los switches.
     * CAMBIO: Se eliminó la verificación de "Ocupada". 
     * Ahora solo existen dos estados: 'propia' (Asignada a mí) o 'libre' (Disponible).
     */
    public function verificarEstadoMateria($id_materia, $id_profesor_actual)
    {
        // Solo verificamos si el profesor ACTUAL la tiene asignada
        $esPropia = $this->db->table('materia_asignadaoriginal')
            ->where('id_materia', $id_materia)
            ->where('id_usr', $id_profesor_actual)
            ->where('activo', 1)
            ->countAllResults();

        if ($esPropia > 0) {
            return 'propia';
        }

        // Si no es propia, siempre está libre (aunque otros la tengan)
        return 'libre';
    }

    /**
     * GUARDA LA CARGA MASIVA DE UN GRADO (VERSIÓN CORREGIDA Y ROBUSTA)
     */
    public function guardarCargaMasiva($id_profesor, $id_grado, $materias_seleccionadas)
    {
        $db = \Config\Database::connect();
        
        // 1. Obtener el Ciclo Escolar Activo
        $idCicloActivo = $this->getCicloActivo();

        // 2. Obtener materias del grado para saber cuáles tocar
        $todasLasMaterias = $this->getMateriasPorGrado($id_grado);

        // --- CORRECCIÓN DE MAYÚSCULAS/MINÚSCULAS ---
        // Verificamos si la base de datos devuelve 'Id_materia' o 'id_materia'
        $columnaID = 'id_materia'; 
        if (!empty($todasLasMaterias)) {
            $primeraFila = (array)$todasLasMaterias[0];
            if (array_key_exists('Id_materia', $primeraFila)) {
                $columnaID = 'Id_materia';
            }
        }

        // Extraemos los IDs usando la columna correcta
        $idsMateriasGrado = array_column($todasLasMaterias, $columnaID);

        // DIAGNÓSTICO: Si esto está vacío, el código se detiene.
        if (empty($idsMateriasGrado)) {
            log_message('error', "ProfesorModel: No se encontraron materias para el grado $id_grado. Verifique tabla 'materia'.");
            return; 
        }

        // Iniciar Transacción
        $db->transStart(); 

        // 3. RESETEO (Soft Delete)
        $builder = $db->table('materia_asignadaoriginal');
        $builder->where('id_usr', $id_profesor);
        $builder->whereIn('id_materia', $idsMateriasGrado); 
        $builder->update(['activo' => 0]);

        // 4. PROCESAR SELECCIONADAS
        if (!empty($materias_seleccionadas)) {
            foreach ($materias_seleccionadas as $id_mat) {
                
                // Verificar si ya existe el registro 
                $existe = $db->table('materia_asignadaoriginal')
                             ->where('id_usr', $id_profesor)
                             ->where('id_materia', $id_mat)
                             ->countAllResults();

                if ($existe > 0) {
                    // Update
                    $db->table('materia_asignadaoriginal')
                       ->where('id_usr', $id_profesor)
                       ->where('id_materia', $id_mat)
                       ->update([
                           'activo' => 1,
                           'id_cicloEscolar' => $idCicloActivo
                       ]);
                } else {
                    // Insert
                    $dataInsert = [
                        'id_usr'          => $id_profesor,
                        'id_materia'      => $id_mat,
                        'activo'          => 1,
                        'id_cicloEscolar' => $idCicloActivo
                    ];
                    
                    $db->table('materia_asignadaoriginal')->insert($dataInsert);
                }
            }
        }

        $db->transComplete(); // Confirmar Transacción
        
        // Verificar si hubo error en la transacción
        if ($db->transStatus() === false) {
            log_message('error', 'ProfesorModel: Error en transacción DB. ' . json_encode($db->error()));
        }
    }
}