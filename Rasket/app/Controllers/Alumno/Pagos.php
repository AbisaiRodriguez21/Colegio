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

    public function guardar()
    {
        $session = session();
        $request = \Config\Services::request();
        
        $folioModel = new FolioModel();
        $pagoModel  = new PagoAlumnoModel(); 

        // A) Generar Folio
        $idFolio = $folioModel->generarNuevo();

        // B) Subir Imagen
        $archivo = $request->getFile('archivo_comprobante');
        $nombreArchivo = '';

        // Nombre del archivo: 
        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $newName = $session->get('id') . '_' . $idFolio . '_' . date('YmdHis') . '.' . $archivo->getExtension();
            // Ubicación de guardado 
            $archivo->move(ROOTPATH . 'public/pagos', $newName);
            $nombreArchivo = $newName;
        }

        // Calcular el monto total
        $cantidad = $request->getPost('cantidad');
        $recargos = $request->getPost('recargos') ?: 0;
        //TOTAL
        $total = $cantidad + $recargos;

        $dataPago = [
            'id_usr'        => $session->get('id'),
            'cantidad'      => $cantidad,
            'recargos'      => $recargos,
            'total'         => $total,
            'mes'           => $request->getPost('mes'),
            'fechaPago'     => date('Y-m-d'),
            'qrp'           => $session->get('nombre') . ' ' . $session->get('apellidos'),
            'concepto'      => $request->getPost('concepto'),
            'modoPago'      => $request->getPost('modoPago'),
            'nota'          => $request->getPost('nota'),
            'validar_ficha' => 48, // Estatus "Por revisar"
            'ficha'         => $nombreArchivo,
            'cilcoescolar'  => $request->getPost('ciclo'),
            'id_folio'      => $idFolio, 
            'fechaEnvio'    => date('Y-m-d H:i:s')
        ];

        $pagoModel->insert($dataPago);

        // Enviar Correo
        $this->enviarCorreo($dataPago, $folioModel->obtenerNumero($idFolio));

        return redirect()->to(base_url("alumno/pagos/recibo/$idFolio"));
    }

    public function verRecibo($idFolio)
    {
        $session = session();
        $pagoModel = new PagoAlumnoModel(); 
        $folioModel = new FolioModel();
        
        // Obtener el pago
        $pago = $pagoModel->where('id_folio', $idFolio)
                          ->where('id_usr', $session->get('id'))
                          ->first();

        if (!$pago) {
            return redirect()->to(base_url('alumno/pagos'));
        }

        $idAlumno = $session->get('id');
        $db = \Config\Database::connect();
        
        $alumno = $db->table('usr')->where('id', $idAlumno)->get()->getRowArray();
        $grado = $db->table('grados')->where('Id_grado', $alumno['grado'])->get()->getRowArray();
        $alumno['nombreGrado'] = $grado ? $grado['nombreGrado'] : 'No asignado';

        $califModel = new CalificacionesModel();
        $config = $califModel->getConfiguracionActiva($alumno['nivel'] ?? 5); 
        $cicloRow = $db->table('cicloEscolar')->where('Id_cicloEscolar', $config['id_ciclo'])->get()->getRowArray();
        $nombreCiclo = $cicloRow ? $cicloRow['nombreCicloEscolar'] : '2025-2026';

        return view('VistadelAlumno/recibo_pago', [
            'pagos'        => [$pago], 
            'folio'        => $folioModel->obtenerNumero($idFolio),
            'alumno'       => $alumno,
            'cicloEscolar' => $nombreCiclo,
            'realizadoPor' => $pago['qrp']  
        ]);
    }

    private function enviarCorreo($datos, $numFolio)
    {
        // pendiente: implementar función de correo 
    }
}