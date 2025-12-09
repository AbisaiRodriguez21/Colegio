<?php namespace App\Models;

use CodeIgniter\Model;

class BoletaModel extends Model
{
    // No definimos $table principal porque usaremos varias, pero es buena práctica tener el archivo.
    
    /**
     * Obtiene todos los grados para construir el menú lateral.
     */
    public function getGradosMenu()
    {
        return $this->db->table('grados')
            // ->where('grado_activo', 1) // Descomenta si quieres filtrar inactivos
            ->orderBy('id_grado', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * 1. Obtiene la configuración del Ciclo Escolar ACTIVO.
     * Basado en tu tabla 'mesycicloactivo'.
     */
    public function getCicloActivo()
    {
        // En tu sistema viejo, id=1 solía ser Primaria/Secundaria.
        // Buscamos la configuración activa y hacemos JOIN para saber el nombre del ciclo (ej: 2025-2026)
        $builder = $this->db->table('mesycicloactivo');
        $builder->select('mesycicloactivo.id_ciclo, cicloescolar.nombreCicloEscolar, mesycicloactivo.id_mes');
        $builder->join('cicloescolar', 'mesycicloactivo.id_ciclo = cicloescolar.Id_cicloEscolar');
        // Filtramos por el ID 1 (Primaria) o el que estés usando por defecto
        $builder->where('mesycicloactivo.id', 1); 
        
        return $builder->get()->getRowArray();
    }

    /**
     * 2. Obtiene las materias del grado.
     * Tabla: 'materia'
     */
    public function getMaterias($id_grado)
    {
        return $this->db->table('materia')
            ->select('Id_materia, nombre_materia, promedio_final') 
            ->where('id_grados', $id_grado)
            ->where('incluida_promedio_general', 1) // Solo las que cuentan para promedio
            ->orderBy('Id_materia', 'ASC') // O un campo 'orden' si existe
            ->get()->getResultArray();
    }

    /**
     * 3. Obtiene las calificaciones (MATRIZ DE DATOS).
     * Tabla: 'calificacion'
     * Retorna: Un arreglo donde $matriz[id_materia][id_mes] = calificacion
     */
    public function getCalificaciones($id_usuario, $id_grado, $id_ciclo)
    {
        $rows = $this->db->table('calificacion')
            ->select('id_materia, id_mes, calificacion')
            ->where('id_usr', $id_usuario)
            ->where('id_grado', $id_grado)
            ->where('cicloEscolar', $id_ciclo)
            ->get()->getResultArray();

        // Transformamos la lista plana de SQL en una Matriz fácil de usar en PHP
        $matriz = [];
        foreach ($rows as $r) {
            $matriz[$r['id_materia']][$r['id_mes']] = $r['calificacion'];
        }
        return $matriz;
    }

    /**
     * 4. Obtiene datos del alumno para el encabezado.
     * Tabla: 'usr' y 'grados'
     */
    public function getDatosAlumno($id_usuario)
    {
        return $this->db->table('usr')
            ->select('usr.id, usr.Nombre, usr.ap_Alumno, usr.am_Alumno, usr.matricula, grados.nombreGrado')
            ->join('grados', 'usr.grado = grados.id_grado') // Ojo: en tu imagen es 'id_grado' (minúscula)
            ->where('usr.id', $id_usuario)
            ->get()->getRowArray();
    }
}