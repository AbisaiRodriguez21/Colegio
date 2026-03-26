<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\GradoModel;
use App\Models\Model;
use CodeIgniter\Controller; 
use Dompdf\Dompdf;
use Dompdf\Options;

class Alumnos extends BaseController 
{
    

    // =========================================================================
    // 1. PANTALLAS DE CAPTURA
    // =========================================================================

    public function registro() 
    {
        // CANDADO ACTIVADO
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        return $this->_cargarFormulario('Registro de Alumnos', 1);
    }

    public function preinscripciones() 
    {
        // CANDADO ACTIVADO
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        return $this->_cargarFormulario('Preinscripciones', 2);
    }

    // =========================================================================
    // 2. LÓGICA INTERNA
    // =========================================================================

    private function _cargarFormulario($titulo, $estatus)
    {
        $gradoModel = new GradoModel();
        $cicloModel = new CicloEscolarModel();

        $data = [
            'title'         => $titulo,
            'estatus_form'  => $estatus,
            'grados'        => $gradoModel->orderBy('id_grado', 'ASC')->asArray()->findAll(),
            'ciclos'        => $cicloModel->orderBy('nombreCicloEscolar', 'ASC')->asArray()->findAll(),
        ];
        
        return view('alumnos/registro', $data);
    }

    // =========================================================================
    // 3. GUARDADO 
    // =========================================================================
    public function guardar()
    {
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acción no autorizada.');
        }

        $model = new UserModel();
        
        // 1. Obtener datos LIMPIOS (Sin acentos y en Mayúsculas)
        $data = $this->obtenerDatosDelPost();
        
        // 2. Generar matrícula real
        $matriculaReal = $model->generarProximaMatricula();
        
        // 3. Inyectar datos de sistema
        $data['matricula'] = $matriculaReal;
        $data['email']     = $matriculaReal . '@sjs.edu.mx'; 
        
        // 4. Estatus SIEMPRE es 2
        $data['estatus']   = 2; 

        if ($model->insert($data)) {
            // Muestra Matrícula y Correo
            $mensajeExito = "¡REGISTRO EXITOSO!\n\n";
            $mensajeExito .= "El alumno se guardó correctamente.\n";
            $mensajeExito .= "--------------------------------------\n";
            $mensajeExito .= "Matrícula: " . $matriculaReal . "\n";
            $mensajeExito .= "Correo: " . $data['email'];

            return redirect()->back()->with('success', $mensajeExito);
        } else {
            return redirect()->back()
                             ->with('error', 'Ocurrió un error al guardar.')
                             ->withInput();
        }
    }

    // =========================================================================
    // 4. MAPEO Y LIMPIEZA DE DATOS
    // =========================================================================
    private function obtenerDatosDelPost() {
        
        // LÓGICA DE GRADO: Si no seleccionan nada, asignamos 18 (Sin Grado)
        $gradoSeleccionado = $this->request->getPost('grado');
        $idGradoFinal = (!empty($gradoSeleccionado)) ? $gradoSeleccionado : 18;

        return [
            // Limpieza específica: Sin acentos, con Ñ, todo mayúsculas
            'Nombre'         => $this->_sanitizarInput($this->request->getPost('Nombre')),
            'ap_Alumno'      => $this->_sanitizarInput($this->request->getPost('ap_Alumno')),
            'am_Alumno'      => $this->_sanitizarInput($this->request->getPost('am_Alumno')),
            'curp'           => $this->_sanitizarInput($this->request->getPost('curp')),
            'rfc'            => $this->_sanitizarInput($this->request->getPost('rfc')),
            'nia'            => $this->_sanitizarInput($this->request->getPost('nia')),
            'direccion'      => $this->_sanitizarInput($this->request->getPost('direccion')),
            
            // Estos campos no necesitan limpieza de texto
            'fechaNacAlumno' => $this->request->getPost('fechaNacAlumno'),
            'sexo_alum'      => $this->request->getPost('sexo_alum'),
            'cp_alum'        => $this->request->getPost('cp_alum'),
            'telefono_alum'  => $this->request->getPost('telefono_alum'),
            'mail_alumn'     => $this->request->getPost('email_tutor'), 
            
            'grado'          => $idGradoFinal,
            
            'generacionactiva' => $this->request->getPost('cicloEscolar'), 
            'extra'          => $this->request->getPost('extra'),
            
            // Fijos
            'pass'           => '123456789', 
            'nivel'          => 7, 
            'activo'         => 1 
        ];
    }

    // HELPER: QUITA ACENTOS Y CONVIERTE A MAYÚSCULAS
    private function _sanitizarInput($texto)
    {
        if (empty($texto)) return '';

        // Mapa EXCLUSIVO para vocales (La Ñ no está aquí, así que se salva)
        $acentos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U'
        ];

        // Reemplazamos solo las vocales acentuadas
        $textoSinAcentos = strtr($texto, $acentos);

        // Convertimos a Mayúsculas usando MB_STRTOUPPER
        return mb_strtoupper($textoSinAcentos, 'UTF-8');
    }

    // EDITAR FICHA DESDE ADMIN 
    public function editarFichaAdmin($id_alumno)
    {
        $db = \Config\Database::connect();
        
        // Obtenemos los datos del alumno y el nombre de su grado
        $alumno = $db->table('usr')
                     ->select('usr.*, grados.nombreGrado')
                     ->join('grados', 'usr.grado = grados.Id_grado', 'left')
                     ->where('usr.id', $id_alumno)
                     ->get()
                     ->getRowArray();

        if (!$alumno) {
            return redirect()->back()->with('error', 'Alumno no encontrado');
        }

        // Preparamos la data para la vista reutilizada
        $data = [
            'alumno' => $alumno,
            'ruta_guardado' => base_url('alumnos/actualizar-ficha-admin') 
        ];

        // Cargamos la vista de la ficha
        return view('VistadelAlumno/ficha', $data);
    }

    public function actualizarFichaAdmin()
    {
        // Verificamos permisos de admin por seguridad
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acción no autorizada.');
        }

        $request = \Config\Services::request();
        $id = $request->getPost('id_alumno');

        $datosGuardar = [
            'nia'            => $this->_sanitizarInput($request->getPost('nia')),
            'Nombre'         => $this->_sanitizarInput($request->getPost('Nombre')),
            'ap_Alumno'      => $this->_sanitizarInput($request->getPost('ap_Alumno')),
            'am_Alumno'      => $this->_sanitizarInput($request->getPost('am_Alumno')),
            'curp'           => $this->_sanitizarInput($request->getPost('curp')),
            'rfc'            => $this->_sanitizarInput($request->getPost('rfc')),
            'fechaNacAlumno' => $request->getPost('fechaNacAlumno'), 

            'direccion'      => $this->_sanitizarInput($request->getPost('direccion_alum')),
            'cp_alum'        => $request->getPost('cp_alum'),
            'estado'         => $this->_sanitizarInput($request->getPost('estado')),
            'telefono_alum'  => $request->getPost('telefono_alum'),
            'mail_alumn'     => strtolower(trim($request->getPost('emailTutor') ?? '')), 
            
            'p_nombre'              => $this->_sanitizarInput($request->getPost('p_nombre')),
            'p_domicilio'           => $this->_sanitizarInput($request->getPost('p_domicilio')),
            'p_empresa'             => $this->_sanitizarInput($request->getPost('p_empresa')),
            'p_cargo'               => $this->_sanitizarInput($request->getPost('p_cargo')),
            'p_mail'                => strtolower(trim($request->getPost('p_mail') ?? '')),
            'p_tel_particular'      => $request->getPost('p_tel_particular'),
            'p_celular'             => $request->getPost('p_celular'),
            'p_parentesco'          => $this->_sanitizarInput($request->getPost('p_parentesco')),
            'p_ultimogradoestudios' => $this->_sanitizarInput($request->getPost('p_ultimogradoestudios')),

            'm_nombre'              => $this->_sanitizarInput($request->getPost('m_nombre')),
            'm_domicilio'           => $this->_sanitizarInput($request->getPost('m_domicilio')),
            'm_empresa'             => $this->_sanitizarInput($request->getPost('m_empresa')),
            'm_cargo'               => $this->_sanitizarInput($request->getPost('m_cargo')),
            'm_mail'                => strtolower(trim($request->getPost('m_mail') ?? '')),
            'm_tel_particular'      => $request->getPost('m_tel_particular'),
            'm_celular'             => $request->getPost('m_celular'),
            'm_parentesco'          => $this->_sanitizarInput($request->getPost('m_parentesco')),
            'm_ultimogradoestudios' => $this->_sanitizarInput($request->getPost('m_ultimogradoestudios')),

            'e_nombre'       => $this->_sanitizarInput($request->getPost('e_nombre')),
            'e_telefono'     => $request->getPost('e_telefono'),
            'extra'          => $this->_sanitizarInput($request->getPost('extra')),
            
            'fecha_actualizar' => date('Y-m-d H:i:s')
        ];

        // Actualizamos en la base de datos
        $db = \Config\Database::connect();
        $db->table('usr')->where('id', $id)->update($datosGuardar);

        return redirect()->to(base_url('cambio-grado'))->with('success', 'Ficha del alumno actualizada correctamente.');
    }

    // VER PAGOS DESDE ADMIN
    public function verPagosAdmin($id_alumno)
    {
        // Verificamos permisos
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acción no autorizada.');
        }

        $db = \Config\Database::connect();

        $pagosValidados = $db->table('pago')
                             ->where('validar_ficha', 49)
                             ->where('ficha IS NOT NULL')
                             ->get()->getResultArray();

        foreach ($pagosValidados as $pv) {
            if (!empty($pv['ficha'])) {
                $rutaFoto = FCPATH . 'pagos/' . $pv['ficha'];
                if (file_exists($rutaFoto)) {
                    unlink($rutaFoto); 
                }
                // Vacía el campo en la BD para evitar imágenes rotas
                $db->table('pago')->where('id_pago', $pv['id_pago'])->update(['ficha' => null]);
            }
        }
        // Obtenemos datos del alumno
        $alumno = $db->table('usr')->where('id', $id_alumno)->get()->getRowArray();
        if (!$alumno) {
            return redirect()->back()->with('error', 'Alumno no encontrado');
        }

        // Obtenemos el ciclo activo 
        $rowCiclo = $db->table('mesycicloactivo')->where('id', 1)->get()->getRow();
        $idCicloActivo = $rowCiclo ? $rowCiclo->id_ciclo : 11;

        // Obtenemos los pagos de ese alumno
        $pagoModel = new \App\Models\PagoAlumnoModel();
        $pagos = $pagoModel->where('id_usr', $id_alumno)
                           ->where('cilcoescolar', $idCicloActivo)
                           ->orderBy('Id_pago', 'DESC')
                           ->findAll();

        // Mandamos todo a la vista que ya existe
        return view('VistadelAlumno/pagos_lista', [
            'nombre'       => session('nombre'), 
            'apellidos'    => session('apellidos'), 
            'alumno'       => $alumno,
            'pagos'        => $pagos,
            'ciclo'        => $idCicloActivo,
            
            'es_admin'     => true,
            'ruta_regreso' => base_url('cambio-grado')
        ]);
    }
    
    // GUARDAR MÚLTIPLES PAGOS DESDE ADMIN
    public function guardarPagoAdmin()
    {
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acción no autorizada.');
        }

        $session = session();
        $request = \Config\Services::request();
        
        $folioModel = new \App\Models\FolioModel();
        $pagoModel  = new \App\Models\PagoAlumnoModel(); 

        $idAlumno = $request->getPost('id_alumno');
        $idCiclo  = $request->getPost('ciclo');

        $idFolio = $folioModel->generarNuevo();

        $conceptos  = $request->getPost('conceptos');
        $meses      = $request->getPost('meses');
        $cantidades = $request->getPost('cantidades');
        $modos      = $request->getPost('modos');
        $notas      = $request->getPost('notas');
        $archivos   = $request->getFileMultiple('archivos_comprobantes');

        if ($conceptos && is_array($conceptos)) {
            foreach ($conceptos as $index => $concepto) {
                
                $archivo = $archivos[$index] ?? null;
                $nombreArchivo = '';

                if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
                    $newName = $idAlumno . '_' . $idFolio . '_' . $index . '_' . date('YmdHis') . '.' . $archivo->getExtension();
                    $archivo->move(ROOTPATH . 'public/pagos', $newName);
                    $nombreArchivo = $newName;
                }

                // Aseguramos que la cantidad sea un número
                $valorCantidad = isset($cantidades[$index]) ? $cantidades[$index] : 0;
                $cant = floatval($valorCantidad);

                $dataPago = [
                    'id_usr'        => $idAlumno,
                    'cantidad'      => $cant,
                    'recargos'      => 0, 
                    'total'         => $cant, 
                    'mes'           => isset($meses[$index]) ? $meses[$index] : '',
                    'fechaPago'     => date('Y-m-d'), 
                    'qrp'           => $session->get('nombre') . ' ' . $session->get('apellidos') . ' (Admin)',
                    'concepto'      => $concepto,
                    'modoPago'      => isset($modos[$index]) ? $modos[$index] : '',
                    'nota'          => isset($notas[$index]) ? $notas[$index] : '',
                    'validar_ficha' => 49, 
                    'ficha'         => $nombreArchivo,
                    'cilcoescolar'  => $idCiclo,
                    'id_folio'      => $idFolio, 
                    'fechaEnvio'    => date('Y-m-d H:i:s')
                ];
                
                // Guardamos el pago y atrapamos su ID
                $idPagoInsertado = $pagoModel->insert($dataPago);

                // DISPARAMOS EL CORREO AUTOMÁTICO
                if ($idPagoInsertado) {
                    $this->_enviarCorreoConfirmacion($idPagoInsertado);
                }
            }
        }
        
        // Redirigimos al recibo y mandamos mensaje de éxito
        return redirect()->to(base_url("alumnos/pagos/recibo/$idFolio"))->with('success', 'Pago registrado y comprobante enviado al alumno.');
    }

    // VER RECIBO DE PAGO DESDE ADMIN
    public function verReciboAdmin($idFolio)
    {
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acción no autorizada.');
        }

        $pagoModel = new \App\Models\PagoAlumnoModel(); 
        $folioModel = new \App\Models\FolioModel();
        
        // Obtener el pago 
        $pagos = $pagoModel->where('id_folio', $idFolio)->findAll();

        if (empty($pagos)) {
            return redirect()->to(base_url('cambio-grado'))->with('error', 'Pago no encontrado.');
        }

        // Tomamos el ID del alumno y quién lo realizó basándonos en el primer registro del arreglo
        $idAlumno = $pagos[0]['id_usr'];
        $realizadoPor = $pagos[0]['qrp'];
        $db = \Config\Database::connect();
        
        // Traer datos del alumno y su grado
        $alumno = $db->table('usr')->where('id', $idAlumno)->get()->getRowArray();
        $grado = $db->table('grados')->where('Id_grado', $alumno['grado'])->get()->getRowArray();
        $alumno['nombreGrado'] = $grado ? $grado['nombreGrado'] : 'No asignado';

        // Obtener ciclo escolar activo
        $califModel = new \App\Models\CalificacionesModel();
        $config = $califModel->getConfiguracionActiva($alumno['nivel'] ?? 7); 
        $cicloRow = $db->table('cicloEscolar')->where('Id_cicloEscolar', $config['id_ciclo'])->get()->getRowArray();
        $nombreCiclo = $cicloRow ? $cicloRow['nombreCicloEscolar'] : '2025-2026';

        // Reutilizamos la vista del alumno
        return view('VistadelAlumno/recibo_pago', [
            'pagos'        => $pagos,
            'folio'        => $folioModel->obtenerNumero($idFolio),
            'alumno'       => $alumno,
            'cicloEscolar' => $nombreCiclo,
            'realizadoPor' => $realizadoPor,
            'ruta_regreso' => base_url("alumnos/ver-pagos/$idAlumno")
        ]);
    }

    // FUNCIÓN PRIVADA PARA ENVIAR EL COMPROBANTE EN PDF
    private function _enviarCorreoConfirmacion($idPago)
    {
        $db = \Config\Database::connect();
        
        $pago = $db->table('pago')->where('id_pago', $idPago)->get()->getRowArray();
        if (!$pago) return false;

        $alumno = $db->table('usr')->where('id', $pago['id_usr'])->get()->getRowArray();
        if (!$alumno) return false;

        $listaCorreos = [];
        if (!empty($alumno['email']))  $listaCorreos[] = $alumno['email'];
        if (!empty($alumno['p_mail'])) $listaCorreos[] = $alumno['p_mail'];
        if (!empty($alumno['m_mail'])) $listaCorreos[] = $alumno['m_mail'];
        $listaCorreos[] = env('SMTP_USER'); 

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
        $email->setSubject('Comprobante Oficial de Pago #' . $pago['id_folio'] . ' - St. Joseph School');

        $montoFormateado = number_format($pago['cantidad'] + $pago['recargos'], 2);
        $conceptoPago = !empty($pago['concepto']) ? $pago['concepto'] : 'N/A';
        $metodoPago   = !empty($pago['modoPago']) ? $pago['modoPago'] : 'N/A';
        $notasPago    = !empty($pago['nota']) ? $pago['nota'] : 'Ninguna';

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

        // URL de tu logo en Cloudinary
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
                        Le confirmamos que hemos recibido y registrado exitosamente su pago en nuestro sistema por la cantidad de <strong style=\"color: #0c335e;\">$ {$montoFormateado}</strong>.
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

        if (file_exists($rutaPdf)) {
            unlink($rutaPdf);
        }

        return $resultado;
    }
}