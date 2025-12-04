<?php 
namespace App\Models;

use CodeIgniter\Model;

class PagoModel extends Model
{
    protected $table      = 'pago';
    protected $primaryKey = 'id_pago'; 
    protected $allowedFields = ['validar_ficha']; 

    public function getPagosPaginados($busqueda = null, $porPagina = 10)
    {
        $this->select("pago.*, u.Nombre, u.ap_Alumno, u.am_Alumno, u.email, g.nombreGrado");
        $this->join('usr u', 'pago.id_usr = u.id');
        $this->join('grados g', 'u.grado = g.id_grado', 'left');
        
        if (!empty($busqueda)) {
            $this->groupStart();
            $this->like('u.Nombre', $busqueda);
            $this->orLike('u.ap_Alumno', $busqueda);
            $this->orLike('u.am_Alumno', $busqueda);
            $this->orLike('u.email', $busqueda);
            $this->groupEnd();
        }
        
        $this->orderBy('pago.fechaEnvio', 'DESC');

        return [
            'pagos' => $this->paginate($porPagina),
            'pager' => $this->pager
        ];
    }
}