<?php namespace App\Models;

use CodeIgniter\Model;

class NivelesModel extends Model
{
    protected $table = 'usr';
    protected $primaryKey = 'id';
    protected $allowedFields = ['estatus', 'activo', 'pass'];

    /**
     * Obtiene usuarios con búsqueda, paginación y ordenamiento dinámico
     */
    public function getUsuarios($columna = 'nombre', $orden = 'ASC', $busqueda = null, $porPagina = 25)
    {
        // 1. Sanitizar orden
        $orden = strtoupper($orden) === 'DESC' ? 'DESC' : 'ASC';

        // 2. Mapa de columnas permitidas para ordenar
        // 'clave_js' => 'campo_real_bd'
        $columnasPermitidas = [
            'nombre' => 'usr.Nombre',
            'email'  => 'usr.email',
            'nivel'  => 'usr_nivel.nombre' 
        ];

        // Si envían una columna desconocida, usamos nombre por defecto
        $campoOrden = $columnasPermitidas[$columna] ?? 'usr.Nombre';

        // 3. Construir consulta
        $builder = $this->select('usr.*, usr_nivel.nombre as nombre_rol')
                        ->join('usr_nivel', 'usr.nivel = usr_nivel.id', 'left')
                        ->where('usr.nivel !=', 7) // Excluir Alumnos
                        ->where('usr.estatus', 1); // Solo Activos

        // 4. Aplicar búsqueda si existe
        if (!empty($busqueda)) {
            $builder->groupStart()
                    ->like('usr.Nombre', $busqueda)
                    ->orLike('usr.ap_Alumno', $busqueda)
                    ->orLike('usr.am_Alumno', $busqueda)
                    ->orLike('usr.email', $busqueda)
                    ->groupEnd();
        }

        // 5. Ordenar y Paginar
        return $builder->orderBy($campoOrden, $orden)
                       ->paginate($porPagina);
    }
}