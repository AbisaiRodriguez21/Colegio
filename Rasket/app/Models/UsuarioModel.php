<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usr';
    protected $primaryKey = 'email';
    protected $allowedFields = [
        'email', 'pass', 'Nombre', 'ap_Alumno', 'am_Alumno',
        'estatus', 'nivel', 'foto'
    ];

    public function verificarLogin($usuario, $password)
    {
        $user = $this->where('email', $usuario)->first();
        if (!$user) {
            return false;
        }

        if ($user['pass'] === $password) {
            return $user;
        }
        return false;
    }
}
