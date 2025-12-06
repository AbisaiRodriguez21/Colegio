<?php namespace App\Models;

use CodeIgniter\Model;

class ProfesorModel extends Model
{
    protected $table = 'usr';
    protected $primaryKey = 'id';
    protected $allowedFields = ['Nombre', 'ap_Alumno', 'am_Alumno', 'email', 'pass', 'estatus', 'nivel'];

    /**
     * Obtiene profesores activos (nivel 5) con su nombre de estatus.
     */
    public function getProfesoresActivos()
    {
        return $this->select('usr.*, estatus_usr.nombre AS nombre_nivel')
                    ->join('estatus_usr', 'usr.estatus = estatus_usr.Id')
                    ->where('usr.nivel', 5)
                    ->where('usr.estatus', 1)
                    ->findAll();
    }

    /**
     * Verifica si el profesor tiene materias asignadas activas.
     */
    public function tieneMaterias($idProfesor)
    {
        return $this->db->table('materia_Asignada')
            ->where('id_usr', $idProfesor)
            ->where('activo', 1)
            ->countAllResults() > 0;
    }

    /**
     * Obtiene datos básicos de un profesor.
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
     * Obtiene las materias asignadas a un profesor.
     */
    public function getMateriasAsignadas($id)
    {
        return $this->db->table('materia_asignadaoriginal MA')
            ->select('M.nombre_materia, G.nombreGrado, MA.activo')
            ->join('materiaoriginal M', 'MA.id_materia = M.id_materia', 'inner')
            ->join('grados G', 'M.id_grados = G.id_grado', 'inner')
            ->where('MA.id_usr', $id)
            ->where('MA.activo', 1)
            ->get()
            ->getResultArray();
    }


    /**
     * 1. Obtiene los grados para armar el acordeón.
     * Reemplaza a: SELECT * FROM grados WHERE nivel_grado >= 3
     */
    public function getGrados()
    {
        return $this->db->table('grados')
            ->where('nivel_grado >=', 3)
            ->orderBy('Id_grado', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * 2. Obtiene todas las materias disponibles de un grado específico.
     * Reemplaza a: SELECT * FROM materia WHERE id_grados = ...
     */
    public function getMateriasPorGrado($id_grado)
    {
        // NOTA: En tu código antiguo usabas la tabla 'materia'. 
        // Si ahora usas 'materiaoriginal', cambia el nombre aquí abajo.
        return $this->db->table('materia') 
            ->where('id_grados', $id_grado)
            ->orderBy('id_materia', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * 3. Verifica el estado de una materia para pintar el checkbox correcto.
     * Reemplaza la lógica compleja de los IF anidados dentro del DO...WHILE
     */
    public function verificarEstadoMateria($id_materia, $id_profesor_actual)
    {
        // A. ¿La tengo yo asignada? (Input Checked)
        $esPropia = $this->db->table('materia_Asignada')
            ->where('id_materia', $id_materia)
            ->where('id_usr', $id_profesor_actual)
            ->where('activo', 1) // Importante: Solo si está activa
            ->countAllResults();

        if ($esPropia > 0) {
            return 'propia';
        }

        // B. ¿La tiene OTRO profesor? (Texto "Materia Asignada")
        $estaOcupada = $this->db->table('materia_Asignada')
            ->where('id_materia', $id_materia)
            ->where('id_usr !=', $id_profesor_actual) // Diferente a mí
            ->where('activo', 1)
            ->countAllResults();

        if ($estaOcupada > 0) {
            return 'ocupada';
        }

        // C. Nadie la tiene (Input vacío)
        return 'libre';
    }

    /**
     * Asigna o quita una materia a un profesor.
     * Reemplaza tu antiguo UPDATE/INSERT
     */
    public function actualizarAsignacion($id_profesor, $id_materia, $activo)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('materia_Asignada');

        // 1. Verificamos si ya existe registro de esta materia para este profe
        $existe = $builder->where('id_usr', $id_profesor)
                          ->where('id_materia', $id_materia)
                          ->get()->getRow();

        if ($existe) {
            // A) Si existe, hacemos UPDATE del campo 'activo'
            return $builder->where('Id_ma', $existe->Id_ma) // Usamos la llave primaria si la tienes
                           ->update(['activo' => $activo]);
        } else {
            // B) Si NO existe, hacemos INSERT nuevo
            return $builder->insert([
                'id_usr'     => $id_profesor,
                'id_materia' => $id_materia,
                'activo'     => $activo,
                // Agrega aquí otros campos obligatorios si tu tabla los pide (ej. fecha, ciclo escolar)
                // 'ciclo' => '2025-2026' 
            ]);
        }
    }
}