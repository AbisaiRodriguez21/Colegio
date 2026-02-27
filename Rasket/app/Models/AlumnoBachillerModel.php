<?php namespace App\Models;

use CodeIgniter\Model;

class AlumnoBachillerModel extends Model
{
    protected $table      = 'usr';
    protected $primaryKey = 'id';
    
    // Permitimos editar solo el área
    protected $allowedFields = ['area3B'];

    /**
     * Obtiene los alumnos de 3° Bachillerato (Grado 33, Nivel 7, Estatus 1)
     * Paginados
     */
    public function getAlumnosTercero($perPage = 30)
    {
        return $this->select('usr.id, usr.Nombre, usr.ap_Alumno, usr.am_Alumno, usr.area3B, g.nombreGrado')
                    ->join('grados g', 'usr.grado = g.Id_grado')
                    ->where('usr.nivel', 7)        
                    ->where('usr.estatus', 1)     
                    ->where('usr.grado', 33)      // ID específico de 3° Bachiller
                    ->orderBy('usr.ap_Alumno', 'ASC')
                    ->paginate($perPage);
    }

    /**
     * Actualización Masiva Segura
     */
    public function actualizarAreas($ids, $areas)
    {
        $db = \Config\Database::connect();
        
        $db->transStart();

        // Recorremos los arrays paralelos
        for ($i = 0; $i < count($ids); $i++) {
            $id = $ids[$i];
            $area = $areas[$i];

            // Solo actualizamos si el ID es válido
            if (!empty($id)) {
                $this->update($id, ['area3B' => $area]);
            }
        }

        $db->transComplete();

        return $db->transStatus(); // Devuelve TRUE si todo salió bien
    }
}