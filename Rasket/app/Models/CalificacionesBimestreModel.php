<?php namespace App\Models;

use CodeIgniter\Model;

class CalificacionesBimestreModel extends Model
{
    // tabla de la base de datos con la que se va a interactuar.
    protected $table = 'calificacion';
    
    // se establece la llave primaria para poder realizar actualizaciones sin errores.
    protected $primaryKey = 'Id_cal'; 

    protected $allowedFields = [
        'id_usr',       // ID del alumno
        'id_grado',     // ID del grado escolar
        'id_materia',   // ID de la materia
        'id_mes',       // ID del mes o periodo evaluado
        'calificacion', // El valor de la nota (número)
        'cicloEscolar', // ID del ciclo escolar actual
        'fechaInsertar',// Fecha y hora de la modificación
        'bandera'       // ID del usuario (capturista/profesor) que realizó la acción
    ];

    /**
     * Función para guardar una nota.
     */
    public function guardarCalificacion($id_usr, $id_grado, $id_materia, $id_mes, $val, $id_capturista, $id_ciclo)
    {
        $existe = $this->where([
            'id_usr'       => $id_usr,
            'id_materia'   => $id_materia,
            'id_mes'       => $id_mes,
            'cicloEscolar' => $id_ciclo
        ])->first();

        $datos = [
            'calificacion'  => $val,
            'fechaInsertar' => date('Y-m-d H:i:s'), // Capturo el momento exacto
            'bandera'       => $id_capturista
        ];

        if ($existe) {
            return $this->update($existe['Id_cal'], $datos);
        } else {
            $datos['id_usr']       = $id_usr;
            $datos['id_grado']     = $id_grado;
            $datos['id_materia']   = $id_materia;
            $datos['id_mes']       = $id_mes;
            $datos['cicloEscolar'] = $id_ciclo;
            
            return $this->insert($datos);
        }
    }
}