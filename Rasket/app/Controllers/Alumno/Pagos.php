<?php

namespace App\Controllers\Alumno;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Models\PagoAlumnoModel; 
use App\Models\FolioModel;
use App\Models\CalificacionesModel;

class Pagos extends BaseController
{
    public function index()
    {
        $session = session();
        if (!$session->has('id') || $session->get('nivel') != 7) return redirect()->to('/login');

        $idAlumno = $session->get('id');

        // Datos del alumno
        $userModel = new UsuarioModel();
        $alumno = $userModel->find($idAlumno);

        // Ciclo Escolar Activo
        $califModel = new CalificacionesModel();
        $config = $califModel->getConfiguracionActiva($alumno['nivel'] ?? 5); 
        $idCicloActivo = $config['id_ciclo'];

        $pagoModel = new PagoAlumnoModel(); 
        $pagos = $pagoModel->where('id_usr', $idAlumno)
                           ->where('cilcoescolar', $idCicloActivo) 
                           ->orderBy('Id_pago', 'DESC')
                           ->findAll();

        return view('VistadelAlumno/pagos_lista', [
            'nombre'    => $session->get('nombre'),
            'apellidos' => $session->get('apellidos'),
            'alumno'    => $alumno,
            'pagos'     => $pagos,
            'ciclo'     => $idCicloActivo
        ]);
    }

    // GUARDAR MÚLTIPLES PAGOS DESDE EL ALUMNO (CARRITO)
    public function guardar()
    {
        $session = session();
        $request = \Config\Services::request();
        
        $folioModel = new FolioModel();
        $pagoModel  = new PagoAlumnoModel(); 

        // El alumno usa su propio ID de la sesión
        $idAlumno = $session->get('id');
        $idCiclo  = $request->getPost('ciclo');

        // Generar UN SOLO Folio para todos los pagos del carrito
        $idFolio = $folioModel->generarNuevo();

        // Recibir los arreglos del carrito
        $conceptos  = $request->getPost('conceptos');
        $meses      = $request->getPost('meses');
        $cantidades = $request->getPost('cantidades');
        $modos      = $request->getPost('modos');
        $notas      = $request->getPost('notas');
        
        $archivos = $request->getFileMultiple('archivos_comprobantes');

        // Recorrer los pagos e insertarlos uno por uno
        if ($conceptos && is_array($conceptos)) {
            foreach ($conceptos as $index => $concepto) {
                
                // Procesar la imagen específica de este pago
                $archivo = $archivos[$index] ?? null;
                $nombreArchivo = '';

                if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
                    $newName = $idAlumno . '_' . $idFolio . '_' . $index . '_' . date('YmdHis') . '.' . $archivo->getExtension();
                    $archivo->move(ROOTPATH . 'public/pagos', $newName);
                    $nombreArchivo = $newName;
                }

                // Calcular montos (Protegido contra NULOS)
                $valorCantidad = isset($cantidades[$index]) ? $cantidades[$index] : 0;
                $cant = floatval($valorCantidad);

                // Preparar inserción
                $dataPago = [
                    'id_usr'        => $idAlumno,
                    'cantidad'      => $cant,
                    'recargos'      => 0, 
                    'total'         => $cant,
                    'mes'           => isset($meses[$index]) ? $meses[$index] : '',
                    'fechaPago'     => date('Y-m-d'),
                    'qrp'           => $session->get('nombre') . ' ' . $session->get('apellidos'),
                    'concepto'      => $concepto,
                    'modoPago'      => isset($modos[$index]) ? $modos[$index] : '',
                    'nota'          => isset($notas[$index]) ? $notas[$index] : '',
                    'validar_ficha' => 0, 
                    'ficha'         => $nombreArchivo,
                    'cilcoescolar'  => $idCiclo,
                    'id_folio'      => $idFolio, 
                    'fechaEnvio'    => date('Y-m-d H:i:s')
                ];

                $pagoModel->insert($dataPago);
            }
        }


        return redirect()->to(base_url("alumno/pagos/recibo/$idFolio"));
    }

    // VER RECIBO DE PAGO DESDE EL ALUMNO

    public function verRecibo($idFolio)
    {
        $session = session();
        $pagoModel = new PagoAlumnoModel(); 
        $folioModel = new FolioModel();
        
        // Obtener TODOS los pagos de ese folio 
        $pagos = $pagoModel->where('id_folio', $idFolio)
                           ->where('id_usr', $session->get('id'))
                           ->findAll();

        if (empty($pagos)) {
            return redirect()->to(base_url('alumno/pagos'));
        }

        $idAlumno = $session->get('id');
        $realizadoPor = $pagos[0]['qrp'];
        $db = \Config\Database::connect();
        
        $alumno = $db->table('usr')->where('id', $idAlumno)->get()->getRowArray();
        $grado = $db->table('grados')->where('Id_grado', $alumno['grado'])->get()->getRowArray();
        $alumno['nombreGrado'] = $grado ? $grado['nombreGrado'] : 'No asignado';

        $califModel = new CalificacionesModel();
        $config = $califModel->getConfiguracionActiva($alumno['nivel'] ?? 7); 
        $cicloRow = $db->table('cicloescolar')->where('Id_cicloEscolar', $config['id_ciclo'])->get()->getRowArray();
        $nombreCiclo = $cicloRow ? $cicloRow['nombreCicloEscolar'] : '2025-2026';

        return view('VistadelAlumno/recibo_pago', [
            'pagos'        => $pagos, 
            'folio'        => $folioModel->obtenerNumero($idFolio),
            'alumno'       => $alumno,
            'cicloEscolar' => $nombreCiclo,
            'realizadoPor' => $realizadoPor  
        ]);
    }

    private function enviarCorreo($datos, $numFolio)
    {
        // pendiente: implementar función de correo 
    }
}