<?php namespace App\Models;

use CodeIgniter\Model;

class PagoModel extends Model
{
    protected $table      = 'pago';
    protected $primaryKey = 'id_pago'; 
    protected $allowedFields = ['validar_ficha', 'fechaPago', 'qrp', 'id_usr'];


    /**
     * Obtiene los pagos pendientes con búsqueda y ORDENAMIENTO dinámico.
     */
    public function getPagosPendientes($busqueda = null, $perPage = 10, $columna = 'fecha', $orden = 'DESC')
    {
        // 1. Mapa de columnas permitidas 
        $columnasPermitidas = [
            'fecha'  => 'pago.fechaEnvio',
            'nombre' => 'u.Nombre', 
            'monto'  => 'pago.concepto',
            'grado'  => 'g.nombreGrado',
            'ticket' => 'pago.ficha'
        ];

        // Si envían una columna rara, usamos 'fecha' por defecto
        $campoOrden = $columnasPermitidas[$columna] ?? 'pago.fechaEnvio';
        
        // Asegurar que el orden sea solo ASC o DESC
        $orden = strtoupper($orden) === 'ASC' ? 'ASC' : 'DESC';

        $builder = $this->select('pago.*, u.Nombre, u.ap_Alumno, u.am_Alumno, u.email, g.nombreGrado')
                        ->join('usr u', 'pago.id_usr = u.id')
                        ->join('grados g', 'u.grado = g.Id_grado', 'left')
                        ->where('u.nivel', 7)       
                        ->where('u.estatus', 2)     
                        ->where('pago.validar_ficha', 48); 

        // LÓGICA DE BÚSQUEDA 
        if (!empty($busqueda)) {
            $builder->groupStart(); 
                $builder->like('u.Nombre', $busqueda)
                        ->orLike('u.ap_Alumno', $busqueda)
                        ->orLike('u.am_Alumno', $busqueda)
                        ->orLike('u.email', $busqueda);
                $nombresCompletos = "CONCAT_WS(' ', u.Nombre, u.ap_Alumno, u.am_Alumno)";
                $builder->orWhere("$nombresCompletos LIKE '%" . $this->db->escapeLikeString($busqueda) . "%'");
            $builder->groupEnd(); 
        }

        // 2. APLICAMOS EL ORDENAMIENTO
        $builder->orderBy($campoOrden, $orden); 

        return $this->paginate($perPage);
    }

    /**
     * Valida el pago: Cambia estatus a 1 igual a 49 como en la bd.
     */
    public function validarPago($idPago, $nombreQuienValida)
    {
        return $this->update($idPago, [
            'validar_ficha' => 49, 
            'fechaPago'     => date('Y-m-d'),
            'qrp'           => $nombreQuienValida 
        ]);
    }
}