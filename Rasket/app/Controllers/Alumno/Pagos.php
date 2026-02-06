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
        $pagoModel  = new PagoAlumnoModel(); 

        // A) Generar Folio
        $idFolio = $folioModel->generarNuevo();

        // B) Subir Imagen
        $archivo = $request->getFile('archivo_comprobante');
        $nombreArchivo = '';

        // Nombre del archivo: IDALUMNO_IDFOLIO_FECHAHORA.EXT
        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $newName = $session->get('id') . '_' . $idFolio . '_' . date('YmdHis') . '.' . $archivo->getExtension();
            // Ubicación de guardado: /public/pagos/
            $archivo->move(ROOTPATH . 'public/pagos', $newName);
            $nombreArchivo = $newName;
        }

        // Calcular el monto total
        $cantidad = $request->getPost('cantidad');
        $recargos = $request->getPost('recargos') ?: 0;
        //TOTAL
        $total = $cantidad + $recargos;

        // C) Guardar
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

        // D) Enviar Correo
        $this->enviarCorreo($dataPago, $folioModel->obtenerNumero($idFolio));

        return redirect()->to(base_url("alumno/pagos/recibo/$idFolio"));
    }

    public function verRecibo($idFolio)
    {
        $session = session();
        $pagoModel = new PagoAlumnoModel(); 
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
        
        $email->setFrom('pagos@sjs.edu.mx', 'Sistema Pagos SJS');
        
        // --- CAMBIO 1: PON AQUÍ TU CORREO PERSONAL ---
        $email->setTo('juancarlosqqq31@gmail.com'); 
        // ---------------------------------------------

        $email->setSubject("Nuevo Pago - Folio #$numFolio");

        // --- CAMBIO 2: USAR LA VISTA BONITA ---
        // Cargamos la vista que creamos en el Paso 1 y le pasamos los datos
        $mensaje = view('emails/nuevo_pago', [
            'datos' => $datos, 
            'folio' => $numFolio
        ]);
        
        $email->setMessage($mensaje);
        
        // Enviamos y si falla, mostramos el error en el log (opcional para debug)
        if (! $email->send()) {
            log_message('error', $email->printDebugger(['headers']));
        }
    }
}