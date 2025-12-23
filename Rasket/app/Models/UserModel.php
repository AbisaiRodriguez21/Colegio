<?php namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'usr';
    protected $primaryKey = 'id';

    // 1. LIMPIEZA: Solo permitimos los campos que realmente usas en tu formulario
    protected $allowedFields = [
        'matricula', 'nivel', 'estatus', 'activo', 'pass', 
        'Nombre', 'ap_Alumno', 'am_Alumno', 
        'curp', 'rfc', 'nia', 
        'fechaNacAlumno', 'sexo_alum', 
        'direccion', 'cp_alum', 'telefono_alum',
        'email',       // Institucional
        'mail_alumn',  // Tutor 
        'grado', 'generacionactiva', 
        'extra'
    ];

    /**
     * Calcula la siguiente matrícula disponible.
     * Lógica: Año(2) + '02A0' + Consecutivo
     */
    public function generarProximaMatricula()
    {
        // Buscamos el último alumno registrado (Nivel 7)
        $ultimo = $this->select('matricula')
                       ->where('nivel', 7)
                       ->orderBy('id', 'DESC')
                       ->first();

        $anio_actual = date('y'); // Ej: "25"
        $consecutivo = 1;         

        if ($ultimo && !empty($ultimo['matricula'])) {
            // Ejemplo BD: 2502A02147 -> Rompemos en 'A0'
            $partes = explode('A0', $ultimo['matricula']);
            
            if (isset($partes[1])) {
                $consecutivo = intval($partes[1]) + 1; 
            }
        }

        return $anio_actual . '02A0' . $consecutivo;
    }
}