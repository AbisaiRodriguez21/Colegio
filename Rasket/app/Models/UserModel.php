<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'usr'; 
    protected $primaryKey = 'id'; 
    protected $useAutoIncrement = true; 


    protected $allowedFields = [
        'Nombre',
        'ap_Alumno',
        'am_Alumno',
        'curp',
        'rfc',
        'nia',
        'fechaNacAlumno',
        'sexo_alum',
        'direccion',
        'cp_alum',
        'telefono_alum',
        'email',
        'pass',
        'grado',
        'extra',
        'matricula',
        'generacionactiva', 
        'estatus',
        'nivel'
    ];
}