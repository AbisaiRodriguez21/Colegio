<?php namespace App\Models;

use CodeIgniter\Model;

class PagoModel extends Model
{
    protected $table      = 'pago';
    protected $primaryKey = 'id_pago'; 
    protected $allowedFields = ['validar_ficha', 'fechaPago', 'qrp', 'id_usr'];

    /**
     * Obtiene los pagos pendientes con paginación y búsqueda.
     */
    public function getPagosPendientes($busqueda = null, $perPage = 10)
    {
        $builder = $this->select('pago.*, u.Nombre, u.ap_Alumno, u.am_Alumno, u.email, g.nombreGrado')
                        ->join('usr u', 'pago.id_usr = u.id')
                        ->join('grados g', 'u.grado = g.Id_grado', 'left')
                        ->where('u.nivel', 7)       
                        ->where('u.estatus', 2)     
                        // USAMOS 48 PORQUE ASÍ LO REQUIERE TU DB (Valor ASCII de '0')
                        ->where('pago.validar_ficha', 48); 

        // LÓGICA DE BÚSQUEDA
        if (!empty($busqueda)) {
            $builder->groupStart(); // IMPORTANTE: Abrir paréntesis para que el filtro 48 no se rompa
                
                // 1. Búsqueda individual
                $builder->like('u.Nombre', $busqueda)
                        ->orLike('u.ap_Alumno', $busqueda)
                        ->orLike('u.am_Alumno', $busqueda)
                        ->orLike('u.email', $busqueda);
                
                // 2. Búsqueda compuesta (Nombre + Apellidos)
                // CONCAT_WS ignora los nulos y une con espacios
                $nombresCompletos = "CONCAT_WS(' ', u.Nombre, u.ap_Alumno, u.am_Alumno)";
                $builder->orWhere("$nombresCompletos LIKE '%" . $this->db->escapeLikeString($busqueda) . "%'");
            
            $builder->groupEnd(); // Cerrar paréntesis
        }

        $builder->orderBy('pago.fechaEnvio', 'DESC'); 

        return $this->paginate($perPage);
    }

    /**
     * Valida el pago: Cambia estatus a 1.
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