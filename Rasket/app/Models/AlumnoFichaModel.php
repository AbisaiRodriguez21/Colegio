<?php

namespace App\Models;

use CodeIgniter\Model;

class AlumnoFichaModel extends Model
{
    protected $table      = 'usr';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nia', 'Nombre', 'ap_Alumno', 'am_Alumno', 'curp', 'rfc', 'fechaNacAlumno',
        'direccion', 'cp_alum', 'estado', 'telefono_alum', 'mail_alumn',
        'p_nombre', 'p_domicilio', 'p_empresa', 'p_cargo', 'p_mail', 'p_tel_particular', 'p_celular', 'p_parentesco', 'p_ultimogradoestudios',
        'm_nombre', 'm_domicilio', 'm_empresa', 'm_cargo', 'm_mail', 'm_tel_particular', 'm_celular', 'm_parentesco', 'm_ultimogradoestudios',
        'e_nombre', 'e_telefono', 'extra', 'fecha_actualizar'
    ];

    public function getDatosFicha($id_alumno)
    {
        return $this->select('usr.*, grados.nombreGrado')
                    ->join('grados', 'grados.Id_grado = usr.grado', 'left')
                    ->where('usr.id', $id_alumno)
                    ->first(); 
    }

    public function updateDatosContacto($id_alumno, $datos)
    {
        return $this->update($id_alumno, $datos);
    }
}