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
}