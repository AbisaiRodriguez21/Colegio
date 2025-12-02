<?php

namespace App\Models;
use CodeIgniter\Model;

class GradoModel extends Model
{
    protected $table = 'grados'; 
    protected $primaryKey = 'id_grado'; 
    
    protected $allowedFields = ['id_grado', 'nombreGrado'];
}