<?php namespace App\Models;

use CodeIgniter\Model;

class RegistroProfesorModel extends Model
{
    protected $table      = 'usr';
    protected $primaryKey = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'generacionactiva', 'Nombre', 'email', 'pass', 'nivel',
        'sexo_alum', 'estado', 'municipio', 'localidad',
        'direccion', 'cp_alum', 'ap_Alumno', 'am_Alumno',
        'fechaNacAlumno', 'curp', 'rfc', 'matricula', 'extra', 'activo'
    ];

    // ==========================================
    // FUNCIONES AUXILIARES PARA LOS SELECTS
    // ==========================================

    public function getCiclos()
    {
        // Usamos la conexión interna del modelo ($this->db)
        return $this->db->table('cicloEscolar')
                        ->orderBy('nombreCicloEscolar', 'ASC')
                        ->get()->getResultArray();
    }

    public function getEstados()
    {
        return $this->db->table('estados')
                        ->orderBy('nombre', 'ASC')
                        ->get()->getResultArray();
    }

    // ============================================================================
    //  SECCIÓN 2: GESTIÓN DE GRADOS (TABLA 'grados')
    //  Nota: Usamos Query Builder directo porque $table arriba es 'usr'
    // ============================================================================

    /**
     * Obtener todos los grados ordenados por ID
     */
    public function listarGrados()
    {
        return $this->db->table('grados')
                        ->orderBy('id_grado', 'ASC')
                        ->get()->getResultArray();
    }

    /**
     * Insertar un nuevo grado
     */
    public function insertarGrado($nombre)
    {
        return $this->db->table('grados')->insert([
            'nombreGrado' => $nombre
        ]);
    }

    /**
     * Eliminar un grado por ID
     */
    public function borrarGrado($id)
    {
        return $this->db->table('grados')->where('id_grado', $id)->delete();
    }
}