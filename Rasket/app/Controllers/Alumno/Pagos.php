<?php

namespace App\Controllers\Alumno;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Models\PagoAlumnoModel; // <--- USAMOS EL NUEVO MODELO
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

        // Historial de Pagos (Usando el modelo exclusivo de alumno)
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
        $pagoModel  = new PagoAlumnoModel(); // <--- USAMOS EL NUEVO MODELO

        // A) Generar Folio
        $idFolio = $folioModel->generarNuevo();

        // B) Subir Imagen
        $archivo = $request->getFile('archivo_comprobante');
        $nombreArchivo = '';

        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $newName = $session->get('id') . '_' . $idFolio . '_' . date('YmdHis') . '.' . $archivo->getExtension();
            $archivo->move(ROOTPATH . 'public/pagos', $newName);
            $nombreArchivo = $newName;
        }

        // C) Guardar
        $dataPago = [
            'id_usr'        => $session->get('id'),
            'cantidad'      => $request->getPost('cantidad'),
            'recargos'      => $request->getPost('recargos') ?: 0,
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

        // D) Enviar Correo
        $this->enviarCorreo($dataPago, $folioModel->obtenerNumero($idFolio));

        return redirect()->to(base_url("alumno/pagos/recibo/$idFolio"));
    }

    public function verRecibo($idFolio)
    {
        $session = session();
        $pagoModel = new PagoAlumnoModel(); // <--- USAMOS EL NUEVO MODELO
        $folioModel = new FolioModel();
        
        $pago = $pagoModel->where('id_folio', $idFolio)
                          ->where('id_usr', $session->get('id'))
                          ->first();

        if (!$pago) {
            return redirect()->to(base_url('alumno/pagos'));
        }

        return view('VistadelAlumno/recibo_pago', [
            'pago'        => $pago, 
            'folioVisual' => $folioModel->obtenerNumero($idFolio),
            'nombre'      => $session->get('nombre') . ' ' . $session->get('apellidos')
        ]);
    }

    private function enviarCorreo($datos, $numFolio)
    {
        $email = \Config\Services::email();
        // $email->setFrom('pagos@sjs.edu.mx', 'Sistema Pagos');
        // $email->setTo('eve.consultores@gmail.com'); 
        $email->setSubject("Nuevo Pago - Folio #$numFolio");
        $email->setMessage("Alumno: {$datos['qrp']} <br> Monto: {$datos['cantidad']} <br> Concepto: {$datos['concepto']}");
        $email->send();
    }
}