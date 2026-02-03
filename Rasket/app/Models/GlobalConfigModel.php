<?php namespace App\Models;

use CodeIgniter\Model;

class GlobalConfigModel extends Model
{
    protected $table = 'mesycicloactivo';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id_mes', 'id_ciclo'];

    // 1. Obtener Catálogo de Meses (Reemplaza mysql_months.php)
    public function getMeses()
    {
        return $this->db->table('mes')
                        ->select('id, nombre')
                        ->orderBy('id', 'ASC')
                        ->get()->getResultArray();
    }

    // 2. Obtener Catálogo de Ciclos (Reemplaza mysql_school_cycle.php)
    public function getCiclos()
    {
        return $this->db->table('cicloescolar')
                        ->select('Id_cicloEscolar as id, nombreCicloEscolar as nombre')
                        ->orderBy('Id_cicloEscolar', 'DESC') // Lo más nuevo primero
                        ->get()->getResultArray();
    }

    // 3. Saber qué tiene seleccionado actualmente ese nivel (Primaria o Bachiller)
    public function getConfiguracionActual($id_config)
    {
        return $this->table($this->table) // tabla mesycicloactivo
                    ->where('id', $id_config)
                    ->first();
    }

    // 4. Obtener Periodos para Bachillerato
    public function getPeriodosBachillerato()
    {
        return [
            ['id' => 1, 'nombre' => 'AGO-SEP'],
            ['id' => 2, 'nombre' => 'OCT-NOV'],
            ['id' => 3, 'nombre' => 'DIC-ENE'],
            ['id' => 4, 'nombre' => 'FEB-MAR'],
            ['id' => 5, 'nombre' => 'ABR-MAY'],
            ['id' => 6, 'nombre' => 'JUN-JUL'],
        ];
    }
}