<?php 

namespace App\Models;

use CodeIgniter\Model;

class PagoAlumnoModel extends Model
{
    protected $table      = 'pago';
    protected $primaryKey = 'Id_pago'; // Respetando la mayúscula de tu BD
    protected $returnType = 'array';

    // Aquí definimos los campos que el ALUMNO tiene permiso de llenar
    protected $allowedFields = [
        'id_usr', 
        'fechaPago', 
        'mes', 
        'concepto', 
        'cantidad', 
        'recargos', 
        'modoPago', 
        'nota', 
        'ficha',         // Imagen
        'cilcoescolar',  // Campo legacy
        'id_folio',      // Ticket
        'fechaEnvio',
        'validar_ficha', // Estatus
        'qrp'            // Nombre del alumno
    ];
}