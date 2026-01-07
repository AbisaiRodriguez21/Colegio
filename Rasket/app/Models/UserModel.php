<?php namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'usr';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'matricula', 'nivel', 'estatus', 'activo', 'pass', 
        'Nombre', 'ap_Alumno', 'am_Alumno', 
        'curp', 'rfc', 'nia', 
        'fechaNacAlumno', 'sexo_alum', 
        'direccion', 'cp_alum', 'telefono_alum',
        'email',       
        'mail_alumn',  
        'grado', 'generacionactiva', 
        'extra'
    ];

    /**
     * Calcula la siguiente matrícula disponible.
     * Ignora registros vacíos o NULL (Titulares).
     */
    public function generarProximaMatricula()
    {
        // 1. Buscamos el último registro que SÍ tenga matrícula
        $ultimo = $this->select('matricula')
                       ->where('nivel', 7) // Solo alumnos
                       ->where('matricula IS NOT NULL') // Ignora NULLs
                       ->where('matricula !=', '')      // Ignora vacíos
                       ->orderBy('id', 'DESC')
                       ->first();

        $anio_actual = date('y'); // Ej: "25"
        $consecutivo = 1;        

        if ($ultimo && !empty($ultimo['matricula'])) {
            
            $partes = explode('A0', $ultimo['matricula']);
            
            
            if (isset($partes[1])) {
                $consecutivo = intval($partes[1]) + 1; 
            }
        }

        // Retorna formato: 2502A0 + consecutivo
        return $anio_actual . '02A0' . $consecutivo;
    }
}