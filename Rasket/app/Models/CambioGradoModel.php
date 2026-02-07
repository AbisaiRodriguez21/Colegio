<?php namespace App\Models;

use CodeIgniter\Model;

class CambioGradoModel extends Model
{
    protected $table      = 'usr';
    protected $primaryKey = 'id';
    protected $allowedFields = ['estatus', 'activo', 'grado']; 

    
    public function getAlumnos($busqueda = null, $porPagina = 20)
    {
        $builder = $this->select('usr.*, grados.nombreGrado, estatus_usr.nombre as nombre_estatus')
                        ->join('grados', 'usr.grado = grados.Id_grado', 'left')
                        ->join('estatus_usr', 'usr.estatus = estatus_usr.Id', 'left')
                        ->where('usr.nivel', 7)
                        ->where('usr.activo', 1);

        if (!empty($busqueda)) {
            $builder->groupStart()
                    ->like('usr.Nombre', $busqueda)
                    ->orLike('usr.ap_Alumno', $busqueda)
                    ->orLike('usr.am_Alumno', $busqueda)
                    ->orLike('usr.email', $busqueda)
                    ->groupEnd();
        }
        $builder->orderBy('usr.ap_Alumno', 'ASC');
        return $this->paginate($porPagina);
    }

    public function getAlumnoDetalle($id)
    {
        return $this->select('usr.id, usr.grado, usr.email, usr.Nombre, usr.ap_Alumno, usr.am_Alumno, grados.nombreGrado')
                    ->join('grados', 'usr.grado = grados.Id_grado', 'left')
                    ->where('usr.id', $id)
                    ->first(); 
    }

    public function getListaGrados()
    {
        $db = \Config\Database::connect();
        return $db->table('grados')->orderBy('id_grado', 'ASC')->get()->getResultArray();
    }

    public function bajaAlumno($id)
    {
        return $this->update($id, ['estatus' => 2]);
    }

    // =======================================================
    // NUEVAS FUNCIONES DE LÓGICA DE NEGOCIO
    // =======================================================

    // 1. Obtener el ciclo activo real (Reemplaza a la variable mágica $cicloActivo)
    public function getCicloActivo()
    {
        $db = \Config\Database::connect();
        // El sistema viejo usaba el registro con id=1 para Primaria/Secundaria
        $row = $db->table('mesycicloactivo')->where('id', 1)->get()->getRow();
        
        // Si existe devuelve el id_ciclo (11), si no, devuelve 11 por defecto para no fallar
        return ($row) ? $row->id_ciclo : 11; 
    }

    // 2. Activación y Pago 
    public function activarConPago($idAlumno, $idNuevoGrado, $datosPago, $idCiclo)
    {
        $db = \Config\Database::connect();
        
        try {
            // A. FOLIO (MAX + 1)
            $query = $db->query("SELECT MAX(num_folio) as max_folio FROM folio");
            $row = $query->getRow();
            $siguienteNumFolio = ($row && $row->max_folio > 0) ? $row->max_folio + 1 : 1;

            if (!$db->table('folio')->insert(['num_folio' => $siguienteNumFolio])) {
                return "ERROR_FOLIO_INSERT: " . $db->error()['message'];
            }

            // B. PAGO (Usando $idCiclo real, no el 5)
            $datosInsertar = [
                'id_usr'        => $idAlumno,
                'cantidad'      => $datosPago['cantidad'],
                'recargos'      => 0,
                'total'         => $datosPago['cantidad'],
                'mes'           => $datosPago['mes'], 
                'fechaPago'     => $datosPago['fechaPago'],
                'qrp'           => $datosPago['qrp'],
                'concepto'      => $datosPago['concepto'],
                'modoPago'      => $datosPago['modoPago'],
                'nota'          => $datosPago['nota'],
                'validar_ficha' => 49, 
                'ficha'         => null, 
                'cilcoescolar'  => $idCiclo, // <--- USO DEL CICLO REAL (11)
                'id_folio'      => $siguienteNumFolio, 
                'fechaEnvio'    => date('Y-m-d H:i:s')
            ];

            if (!$db->table('pago')->insert($datosInsertar)) {
                return "ERROR_INSERT_PAGO: " . $db->error()['message'];
            }

            // C. ACTUALIZAR ALUMNO
            $datosAlumno = [
                'estatus' => 1, 
                'activo'  => 1, // Aseguramos activo=1
                'grado'   => intval($idNuevoGrado)
            ];

            if (!$this->update($idAlumno, $datosAlumno)) {
                return "ERROR_UPDATE_ALUMNO: " . $db->error()['message'];
            }

            return $siguienteNumFolio;

        } catch (\Exception $e) {
            return "EXCEPCION_PHP: " . $e->getMessage();
        }
    }

    // 3. Inicialización Académica (Lógica del archivo viejo activacion.php)
    public function inicializarCalificaciones($idAlumno, $idGrado, $idCiclo)
    {
        $db = \Config\Database::connect();
        
        // A. Definir número de meses según el grado
        $meses = 0;
        
        // Primaria y Secundaria (según código viejo)
        if (in_array($idGrado, [22,23,24,25,26,27,28,29,30,34,35,36])) {
            $meses = 10;
        }
        // Bachillerato
        elseif (in_array($idGrado, [31,32,33])) {
            $meses = 6;
        }
        // Otros (Preescolar?)
        elseif (in_array($idGrado, [19,20,21])) {
            $meses = 3;
        }

        if ($meses == 0) return; // Si no cae en ningún rango, no hacemos nada

        // B. Obtener materias del nuevo grado
        $materias = $db->table('materia')
                       ->select('Id_materia')
                       ->where('id_grados', $idGrado)
                       ->get()
                       ->getResultArray();

        // C. Insertar calificaciones vacías
        if (!empty($materias)) {
            $dataBatch = [];
            foreach ($materias as $mat) {
                for ($i = 1; $i <= $meses; $i++) {
                    $dataBatch[] = [
                        'id_usr'       => $idAlumno,
                        'id_materia'   => $mat['Id_materia'],
                        'id_mes'       => $i,
                        'cicloEscolar' => $idCiclo, // <--- Usamos el 11
                        'id_grado'     => $idGrado,
                        'calificacion' => 0
                    ];
                }
            }
            
            // Insertar todo de un golpe (Optimizado)
            if (!empty($dataBatch)) {
                // Usamos ignore(true) para que si ya existen no de error
                $db->table('calificacion')->ignore(true)->insertBatch($dataBatch);
            }
        }
    }
}