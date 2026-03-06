<?php namespace App\Controllers;

use App\Models\PagoModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class VerificarPagos extends BaseController
{
    // =========================================================================
    // 1. VISTA PRINCIPAL
    // =========================================================================
    public function index()
    {
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $request = \Config\Services::request();
        $model = new PagoModel();

        // Obtener parámetros 
        $busqueda = $request->getGet('q');
        $columna  = $request->getGet('columna') ?? 'fecha'; 
        $dir      = $request->getGet('dir') ?? 'DESC';      
        $perPage  = 30;

        // Pasamos columna y dir al modelo
        $pagos = $model->getPagosPendientes($busqueda, $perPage, $columna, $dir);
        $pager = $model->pager;

        // Cálculos de totales
        $totalRows = $pager->getTotal();
        $currentPage = $pager->getCurrentPage();
        $inicio = ($totalRows > 0) ? ($currentPage - 1) * $perPage + 1 : 0;
        $fin    = ($currentPage * $perPage);
        if ($fin > $totalRows) $fin = $totalRows;

        $data = [
            'pagos'    => $pagos,
            'pager'    => $pager,
            'busqueda' => $busqueda,
            'ordenActual' => [
                'columna' => $columna,
                'dir'     => $dir
            ],
            'info_paginacion' => [
                'inicio' => $inicio, 'fin' => $fin, 'total' => $totalRows
            ]
        ];

        return view('pagos/verificar', $data);
    }

    // =========================================================================
    // 2. ACCIÓN AJAX: VALIDAR
    // =========================================================================
    public function validar()
    {
        // Seguridad
        if (!$this->_verificarPermisos()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'msg' => 'No autorizado']);
        }

        // Recibir el ID del pago
        $request = \Config\Services::request();
        $idPago = $request->getPost('id_pago');
        
        // Obtener SOLO el NOMBRE del usuario logueado
        $quienValida = session('nombre'); 

        if (empty($quienValida)) {
            $quienValida = 'Administrador';
        }

        $model = new PagoModel();
        
        // Guardar en BD y ENVIAR CORREO
        if ($model->validarPago($idPago, $quienValida)) {
            

            $this->_enviarCorreoConfirmacion($idPago);

            return $this->response->setJSON(['status' => 'success', 'msg' => 'Pago validado correctamente. Se ha notificado al alumno.']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al actualizar la base de datos.']);
        }
    }

    // 3. FUNCIÓN PRIVADA PARA ENVIAR CORREOS 
    private function _enviarCorreoConfirmacion($idPago)
    {
        $db = \Config\Database::connect();
        
        // Obtener datos
        $pago = $db->table('pago')->where('id_pago', $idPago)->get()->getRowArray();
        if (!$pago) return false;

        $alumno = $db->table('usr')->where('id', $pago['id_usr'])->get()->getRowArray();
        if (!$alumno) return false;

        $listaCorreos = [];
        
        if (!empty($alumno['email']))  $listaCorreos[] = $alumno['email'];    // Correo del alumno
        if (!empty($alumno['p_mail'])) $listaCorreos[] = $alumno['p_mail'];   // Correo del papá
        if (!empty($alumno['m_mail'])) $listaCorreos[] = $alumno['m_mail'];   // Correo de la mamá
        $listaCorreos[] = env('SMTP_USER'); 
        // $listaCorreos[] = 'correo_pendienteADMIN@sjs.edu.mx'; 

        if (empty($listaCorreos)) {
            return false;
        }

        // Configurar SMTP
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
        
        // ASUNTO ÚNICO CON NÚMERO DE FOLIO
        $email->setSubject('Comprobante Oficial de Pago #' . $pago['id_folio'] . ' - St. Joseph School');

        // PREPARAR VARIABLES 
        $montoFormateado = number_format($pago['cantidad'] + $pago['recargos'], 2);
        $conceptoPago = !empty($pago['concepto']) ? $pago['concepto'] : 'N/A';
        $metodoPago   = !empty($pago['modoPago']) ? $pago['modoPago'] : 'N/A';
        $notasPago    = !empty($pago['nota']) ? $pago['nota'] : 'Ninguna';

        // GENERAR EL PDF
        $opcionesPdf = new \Dompdf\Options();
        $opcionesPdf->set('isRemoteEnabled', true); 
        $dompdf = new \Dompdf\Dompdf($opcionesPdf);
        
        $htmlPdf = view('pagos/recibo_pdf', [
            'pago'            => $pago,
            'alumno'          => $alumno,
            'montoFormateado' => $montoFormateado
        ]);

        $dompdf->loadHtml($htmlPdf);
        $dompdf->setPaper('Letter', 'portrait');
        $dompdf->render();

        $nombreArchivo = 'Comprobante_SJS_' . $pago['id_folio'] . '.pdf';
        $rutaPdf = WRITEPATH . 'uploads/' . $nombreArchivo;
        file_put_contents($rutaPdf, $dompdf->output());

        $email->attach($rutaPdf);

        // DISEÑO DEL CUERPO DEL CORREO
        $urlLogo = 'https://res.cloudinary.com/do7jgbokk/image/upload/v1772642231/LogoST_otosas.png';
        $html = "
        <div style=\"font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f4f7f6; padding: 40px 20px;\">
            <div style=\"max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);\">
                
                <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color: #ffffff; border-bottom: 3px solid #0c335e;\">
                    <tr>
                        <td align=\"left\" valign=\"middle\" style=\"padding: 25px 30px; width: 50%;\">
                            <img src=\"{$urlLogo}\" alt=\"St. Joseph School\" style=\"max-width: 130px; height: auto; display: block;\">
                        </td>
                        <td align=\"right\" valign=\"middle\" style=\"padding: 25px 30px; width: 50%;\">
                            <h2 style=\"margin: 0; color: #0c335e; font-size: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;\">Pago Validado</h2>
                        </td>
                    </tr>
                </table>

                <div style=\"padding: 30px;\">
                    <p style=\"font-size: 16px; color: #333;\">Estimado(a) <strong>{$alumno['Nombre']} {$alumno['ap_Alumno']}</strong>,</p>
                    
                    <p style=\"font-size: 15px; color: #555; line-height: 1.6;\">
                        Le confirmamos que hemos recibido y validado exitosamente su pago en nuestro sistema por la cantidad de <strong style=\"color: #0c335e;\">$ {$montoFormateado}</strong>.
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

        // Enviar
        $resultado = $email->send();

        // BORRAMOS EL PDF TEMPORAL DEL SERVIDOR
        if (file_exists($rutaPdf)) {
            unlink($rutaPdf);
        }

        // AUTODESTRUCCIÓN DEL TICKET FÍSICO
        if (!empty($pago['ficha'])) {
            // Buscamos la foto original en la carpeta public/pagos/
            $rutaFotoTicket = FCPATH . 'pagos/' . $pago['ficha'];
            
            // Si el archivo físico existe, lo eliminamos
            if (file_exists($rutaFotoTicket)) {
                unlink($rutaFotoTicket);
            }
            
            // Vaciamos el campo en la BD para que el botón "Ver archivo" se oculte y no marque error
            $db->table('pago')->where('id_pago', $pago['id_pago'])->update(['ficha' => null]);
        }
        return $resultado;
    }
}