<?php

namespace App\Models;

use CodeIgniter\Model;

class MateriaModel extends Model
{
    protected $table      = 'materia';
    protected $primaryKey = 'Id_materia'; 
    protected $returnType = 'array';

    protected $allowedFields = ['nombre_materia', 'id_grados'];

    public function obtenerPorGrado($idGrado)
    {
        return $this->where('id_grados', $idGrado)
                    ->orderBy('orden', 'ASC') 
                    ->findAll();
    }
}