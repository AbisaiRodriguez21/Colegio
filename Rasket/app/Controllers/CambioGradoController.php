<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CambioGradoModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class CambioGradoController extends BaseController
{
    public function index()
    {

        $model = new CambioGradoModel();
        $request = \Config\Services::request();
        $busqueda = $request->getGet('q');
        
        $data = [
            'alumnos'  => $model->getAlumnos($busqueda, 30),
            'grados'   => $model->getListaGrados(),
            'pager'    => $model->pager,
            'busqueda' => $busqueda,
            'info'     => [ 
                'inicio' => 0, 
                'fin' => 0, 
                'total' => number_format($model->pager->getTotal()) 
            ]
        ];
        return view('CambioGrado/CambioGrado', $data);
    }

    public function darBaja()
    {
        $request = \Config\Services::request();
        if (!$request->isAJAX()) return $this->response->setStatusCode(403);
        
        $model = new CambioGradoModel();
        if ($model->bajaAlumno($request->getPost('id'))) {
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Alumno dado de baja.']);
        }
        return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al procesar baja.']);
    }

    // CARGAR DATOS PARA EL MODAL
    public function getDatosModal()
    {
        $id = $this->request->getGet('id');
        $model = new CambioGradoModel();
        $alumno = $model->getAlumnoDetalle($id);
        
        if($alumno) {
            $alumno['NombreCompleto'] = $alumno['Nombre'] . ' ' . $alumno['ap_Alumno'] . ' ' . $alumno['am_Alumno'];
            return $this->response->setJSON([
                'status' => 'success', 
                'alumno' => $alumno, 
                'grados' => $model->getListaGrados()
            ]);
        }
        return $this->response->setJSON(['status' => 'error']);
    }

    // =======================================================
    // FUNCIÓN ACTIVAR (LÓGICA FINAL COMPLETA)
    // =======================================================
    public function activar()
    {
        $request = \Config\Services::request();
        if (!$request->isAJAX()) return $this->response->setStatusCode(403);

        $model = new CambioGradoModel();
        
        // Validar Datos
        $idAlumno = $request->getPost('id_alumno');
        $nuevoGrado = $request->getPost('nuevo_grado');

        if (empty($nuevoGrado) || $nuevoGrado == 0 || $nuevoGrado == "0") {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error: Debes seleccionar un Grado Siguiente válido.']);
        }
        
        $qrp = session('nombre');
        if (empty($qrp)) $qrp = 'Usuario Sistema';

        // OBTENER DATOS DE ENTORNO 
        $mesActualNombre = $this->getMesEspanol();
        
        // CONSULTAMOS LA BD PARA SABER EL CICLO ACTIVO 
        $idCicloActivo = $model->getCicloActivo(); 

        $datosPago = [
            'qrp'       => $qrp,
            'fechaPago' => $request->getPost('fecha_pago'),
            'cantidad'  => $request->getPost('cantidad'),
            'concepto'  => $request->getPost('concepto'),
            'modoPago'  => $request->getPost('modo_pago'),
            'nota'      => $request->getPost('nota'),
            'email'     => $request->getPost('email'),
            'mes'       => $mesActualNombre
        ];

        // ACTIVAR Y PAGAR 
        $resultado = $model->activarConPago($idAlumno, $nuevoGrado, $datosPago, $idCicloActivo);

        if (is_numeric($resultado)) {
            $folioGenerado = $resultado;

            // INICIALIZACIÓN ACADÉMICA 
            $model->inicializarCalificaciones($idAlumno, $nuevoGrado, $idCicloActivo);

            // NUEVO ENVÍO DE CORREO CON PDF
            $db = \Config\Database::connect();
            
            // Buscamos el ID del pago que acabamos de insertar usando el Folio
            $pagoRow = $db->table('pago')->where('id_folio', $folioGenerado)->orderBy('id_pago', 'DESC')->get()->getRowArray();
            
            $msgExtra = '';
            if ($pagoRow) {
                // Pasamos el ID del pago y también el correo que se escribió en el Modal
                $correoModal = $request->getPost('email');
                $enviado = $this->_enviarCorreoConfirmacion($pagoRow['id_pago'], $correoModal);
                
                if(!$enviado){
                    $msgExtra = ' (El alumno se activó, pero hubo un problema al enviar el correo)';
                }
            }

            return $this->response->setJSON([
                'status' => 'success', 
                'msg' => 'Alumno reactivado correctamente. Folio: ' . $folioGenerado . $msgExtra
            ]);

        } else {
            return $this->response->setJSON(['status' => 'error', 'msg' => $resultado]);
        }
    }

    private function getMesEspanol() {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[(int)date('n')];
    }

    // =========================================================================
    // FUNCIÓN PRIVADA PARA ENVIAR EL COMPROBANTE EN PDF (Reactivación)
    // =========================================================================
    private function _enviarCorreoConfirmacion($idPago, $correoExtra = null)
    {
        $db = \Config\Database::connect();
        
        $pago = $db->table('pago')->where('id_pago', $idPago)->get()->getRowArray();
        if (!$pago) return false;

        $alumno = $db->table('usr')->where('id', $pago['id_usr'])->get()->getRowArray();
        if (!$alumno) return false;

        // Juntamos los correos del alumno, padres, el escrito en el modal y la escuela
        $listaCorreos = [];
        if (!empty($alumno['email']))  $listaCorreos[] = $alumno['email'];
        if (!empty($alumno['p_mail'])) $listaCorreos[] = $alumno['p_mail'];
        if (!empty($alumno['m_mail'])) $listaCorreos[] = $alumno['m_mail'];
        if (!empty($correoExtra))      $listaCorreos[] = $correoExtra; 
        $listaCorreos[] = env('SMTP_USER'); 

        // Limpiamos repetidos 
        $listaCorreos = array_unique($listaCorreos);

        if (empty($listaCorreos)) {
            return false;
        }

        $email = \Config\Services::email();
        $config = [
            'protocol'   => 'smtp',
            'SMTPHost'   => 'smtp.gmail.com',
            'SMTPUser'   => env('SMTP_USER'), 
            'SMTPPass'   => env('SMTP_PASS'), 
            'SMTPPort'   => 465,
            'SMTPCrypto' => 'ssl',
            'mailType'   => 'html',
            'charset'    => 'utf-8',
            'wordWrap'   => true,
            'CRLF'       => "\r\n",
            'newline'    => "\r\n"
        ];
        $email->initialize($config);

        $email->setFrom(env('SMTP_USER'), 'St. Joseph School');
        $email->setTo(implode(',', $listaCorreos)); 
        
        // Asunto un poco distinto para que sepan que es de Reactivación
        $email->setSubject('Confirmación de Reactivación y Pago #' . $pago['id_folio'] . ' - St. Joseph School');

        $montoFormateado = number_format($pago['cantidad'] + $pago['recargos'], 2);
        $conceptoPago = !empty($pago['concepto']) ? $pago['concepto'] : 'N/A';
        $metodoPago   = !empty($pago['modoPago']) ? $pago['modoPago'] : 'N/A';
        $notasPago    = !empty($pago['nota']) ? $pago['nota'] : 'Ninguna';

        // MAGIA DOMPDF
        $opcionesPdf = new Options();
        $opcionesPdf->set('isRemoteEnabled', true); 
        $dompdf = new Dompdf($opcionesPdf);
        
        $htmlPdf = view('pagos/recibo_pdf', [
            'pago'            => $pago,
            'alumno'          => $alumno,
            'montoFormateado' => $montoFormateado
        ]);

        $dompdf->loadHtml($htmlPdf);
        $dompdf->setPaper('Letter', 'portrait');
        $dompdf->render();

        $nombreArchivo = 'Comprobante_Reactivacion_SJS_' . $pago['id_folio'] . '.pdf';
        $rutaPdf = WRITEPATH . 'uploads/' . $nombreArchivo;
        file_put_contents($rutaPdf, $dompdf->output());

        $email->attach($rutaPdf);

        // URL de tu logo en Cloudinary
        $urlLogo = 'https://res.cloudinary.com/do7jgbokk/image/upload/v1772642231/LogoST_otosas.png';

        // DISEÑO MEMBRETE OFICIAL
        $html = "
        <div style=\"font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f4f7f6; padding: 40px 20px;\">
            <div style=\"max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);\">
                
                <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color: #ffffff; border-bottom: 3px solid #0c335e;\">
                    <tr>
                        <td align=\"left\" valign=\"middle\" style=\"padding: 25px 30px; width: 50%;\">
                            <img src=\"{$urlLogo}\" alt=\"St. Joseph School\" style=\"max-width: 130px; height: auto; display: block;\">
                        </td>
                        <td align=\"right\" valign=\"middle\" style=\"padding: 25px 30px; width: 50%;\">
                            <h2 style=\"margin: 0; color: #0c335e; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;\">Reactivación Exitosa</h2>
                        </td>
                    </tr>
                </table>

                <div style=\"padding: 30px;\">
                    <p style=\"font-size: 16px; color: #333;\">Estimado(a) <strong>{$alumno['Nombre']} {$alumno['ap_Alumno']}</strong>,</p>
                    
                    <p style=\"font-size: 15px; color: #555; line-height: 1.6;\">
                        Le confirmamos que hemos recibido y registrado exitosamente su pago de reinscripción en nuestro sistema por la cantidad de <strong style=\"color: #0c335e;\">$ {$montoFormateado}</strong>. <strong>Con esto, su estatus como alumno activo ha sido restablecido.</strong>
                    </p>

                    <div style=\"background-color: #f8fbfd; border-left: 4px solid #0c335e; padding: 15px; margin: 25px 0; border-radius: 0 8px 8px 0;\">
                        <p style=\"margin: 5px 0; font-size: 14px; color: #555;\"><strong>Concepto:</strong> {$conceptoPago}</p>
                        <p style=\"margin: 5px 0; font-size: 14px; color: #555;\"><strong>Método de pago:</strong> {$metodoPago}</p>
                        <p style=\"margin: 5px 0; font-size: 14px; color: #555;\"><strong>Notas:</strong> {$notasPago}</p>
                    </div>

                    <p style=\"font-size: 15px; color: #555; line-height: 1.6;\">
                        Le enviamos adjunto a este correo su recibo oficial en formato <strong>PDF</strong> para sus registros personales.
                    </p>
                    
                    <p style=\"margin-top: 40px; font-size: 12px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 20px;\">
                        St. Joseph School<br>
                    </p>
                </div>
            </div>
        </div>
        ";

        $email->setMessage($html);
        $resultado = $email->send();

        // Limpieza del servidor
        if (file_exists($rutaPdf)) {
            unlink($rutaPdf);
        }
        
        return $resultado;
    }
}