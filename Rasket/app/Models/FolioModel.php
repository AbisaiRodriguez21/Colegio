<?php

namespace App\Models;

use CodeIgniter\Model;

class FolioModel extends Model
{
    protected $table      = 'folio';
    protected $primaryKey = 'id'; // Según tu captura de phpMyAdmin la llave es 'id'
    protected $allowedFields = ['num_folio'];

    // Esta función hace lo que vimos en tu captura de VS Code
    public function generarNuevo()
    {
        // 1. Buscamos el número más alto actual
        $query = $this->db->query("SELECT MAX(num_folio) as max_folio FROM folio");
        $row   = $query->getRow();
        
        // 2. Calculamos el siguiente (Si no hay nada, empezamos en 1000000 por ejemplo)
        $siguienteFolio = ($row && $row->max_folio > 0) ? $row->max_folio + 1 : 1000001;

        // 3. Insertamos el nuevo folio
        $this->insert(['num_folio' => $siguienteFolio]);

        // 4. Devolvemos el ID autoincrementable (id) para relacionarlo con el pago
        return $this->getInsertID();
    }
    
    // Función auxiliar para obtener el número visible (ej: 1005244) dado un ID
    public function obtenerNumero($id)
    {
        $fila = $this->find($id);
        return $fila ? $fila['num_folio'] : '---';
    }
}