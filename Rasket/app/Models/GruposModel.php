<?php namespace App\Models;

use CodeIgniter\Model;

class GruposModel extends Model {

    // PROPIEDADES ESTÁNDARES DE CI4 (Reemplazan al constructor __construct() manual)
    protected $DBGroup          = 'default';
    protected $table            = 'usr'; 
    protected $primaryKey       = 'id';
    
    // Función para llenar el combobox
    public function getGrados() {
        // Usamos la inyección $this->db para acceder a otras tablas
        $builder = $this->db->table('grados');
        $builder->select('id_grado, nombreGrado');
        $builder->orderBy('id_grado', 'ASC');
        return $builder->get()->getResultArray();
    }

    // Función principal para la lista de alumnos
    public function getAlumnosPorGrado($gradoId = null) {
        $builder = $this->db->table('usr');
        
        // **IMPORTANTE:** Incluimos 'usr.activo' y el 'grado_id' numérico para depuración.
        $builder->select('usr.id, usr.matricula, usr.Nombre, usr.ap_Alumno, usr.am_Alumno, usr.activo AS estado_activo, usr.grado AS grado_id, grados.nombreGrado');
        
        // LEFT JOIN para que el alumno aparezca, aunque no encuentre el nombre del grado
        $builder->join('grados', 'usr.grado = grados.id_grado', 'left');
        
        // DEPURACIÓN: Se quitan los filtros estrictos para ver todos los alumnos.
        
        if ($gradoId != null && $gradoId != "") {
            $builder->where('usr.grado', $gradoId);
        }

        $builder->orderBy('usr.ap_Alumno', 'ASC'); 
        
        return $builder->get()->getResultArray();
    }
}