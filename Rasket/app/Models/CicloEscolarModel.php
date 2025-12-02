<?php

namespace App\Models;
use CodeIgniter\Model;

class CicloEscolarModel extends Model
{
    protected $table = 'cicloEscolar'; 
    protected $primaryKey = 'id_cicloEscolar'; 
 
    protected $allowedFields = ['id_cicloEscolar', 'nombreCicloEscolar'];
}