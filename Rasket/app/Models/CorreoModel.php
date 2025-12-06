<?php namespace App\Models;

use CodeIgniter\Model;

class CorreoModel extends Model
{
    protected $table = 'correos';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'emisor_id', 'grado_destinario_id', 'fecha_envio', 'asunto', 
        'para', 'mensaje', 'adjunto', 'estado', 
        'destacado', 'eliminado', 'archivado'
    ];

    /**
     * Obtiene TODOS los grados (He quitado el filtro de activo por si acaso)
     */
    public function getGrados()
    {
        return $this->db->table('grados')
            ->select('id_grado, nombreGrado')
            // ->where('grado_activo', 1)  <-- COMENTADO: Descomenta si solo quieres ver los activos
            ->orderBy('id_grado', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Obtiene correos por grado
     */
    public function getEmailsPorGrado($id_grado)
    {
        return $this->db->table('usr')
            ->select('email')
            ->where('grado', $id_grado)
            ->where('activo', 1)
            ->where('email !=', '')
            ->get()->getResultArray();
    }

    /**
     * Obtiene correos por nivel
     */
    public function getEmailsPorNivel($id_nivel)
    {
        return $this->db->table('usr')
            ->select('usr.email')
            ->join('grados', 'usr.grado = grados.id_grado')
            ->where('grados.nivel_grado', $id_nivel)
            ->where('usr.activo', 1)
            ->where('usr.email !=', '')
            ->get()->getResultArray();
    }

    /**
     * Obtiene los correos filtrados por bandeja y usuario
     */
    public function getCorreos($filtro = 'recibidos', $userId = 1) 
    {
        $builder = $this->orderBy('fecha_envio', 'DESC');
        
        // Regla general: No mostrar eliminados (a menos que estemos en papelera)
        if ($filtro != 'papelera') {
            $builder->where('eliminado', 0);
        }

        switch ($filtro) {
            case 'papelera':
                // Mostramos todo lo eliminado, sea recibido o enviado por mí
                $builder->where('eliminado', 1)
                        ->groupStart() // Paréntesis para OR
                            ->where('emisor_id', $userId)
                            ->orWhere('emisor_id !=', $userId) // Aquí podrías filtrar por destinatario si tuvieras la columna
                        ->groupEnd();
                break;

            case 'destacados':
                $builder->where('destacado', 1)
                        ->where('archivado', 0);
                break;

            case 'archivados':
                $builder->where('archivado', 1);
                break;

            case 'enviados':
                // TODO lo que YO envié (emisor = yo), sin importar si falló o no
                $builder->where('emisor_id', $userId)
                        ->where('archivado', 0);
                break;

            case 'recibidos':
            default:
                // TODO lo que NO envié yo (emisor != yo) -> Asumimos que es recibido
                // Nota: En un sistema real filtrarías por 'destinatario_id = yo'
                $builder->where('emisor_id !=', $userId)
                        ->where('archivado', 0);
                break;
        }

        return $builder->findAll();
    }
}