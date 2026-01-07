<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CambioGradoModel;

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
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Alumno dado de baja (Estatus 2).']);
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
        
        // 1. Validar Datos
        $idAlumno = $request->getPost('id_alumno');
        $nuevoGrado = $request->getPost('nuevo_grado');

        if (empty($nuevoGrado) || $nuevoGrado == 0 || $nuevoGrado == "0") {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error: Debes seleccionar un Grado Siguiente válido.']);
        }
        
        $qrp = session('nombre');
        if (empty($qrp)) $qrp = 'Usuario Sistema';

        // 2. OBTENER DATOS DE ENTORNO (Mes y Ciclo Real)
        $mesActualNombre = $this->getMesEspanol();
        
        // CONSULTAMOS LA BD PARA SABER EL CICLO ACTIVO (Igual que mesyciclo.php ID=1)
        $idCicloActivo = $model->getCicloActivo(); // Debería devolver 11

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

        // 3. ACTIVAR Y PAGAR (Pasamos el Ciclo Real)
        $resultado = $model->activarConPago($idAlumno, $nuevoGrado, $datosPago, $idCicloActivo);

        if (is_numeric($resultado)) {
            $folioGenerado = $resultado;

            // 4. INICIALIZACIÓN ACADÉMICA (Lógica vieja de materias y ceros)
            // Esto crea los registros en la tabla 'calificacion'
            $model->inicializarCalificaciones($idAlumno, $nuevoGrado, $idCicloActivo);

            // 5. ENVÍO DE CORREO
            $alumno = $model->getAlumnoDetalle($idAlumno);
            $alumno['NombreCompleto'] = $alumno['Nombre'] . ' ' . $alumno['ap_Alumno'] . ' ' . $alumno['am_Alumno'];
            
            $db = \Config\Database::connect();
            $rowGrado = $db->table('grados')->where('Id_grado', $nuevoGrado)->get()->getRow();
            $nombreGrado = $rowGrado ? $rowGrado->nombreGrado : 'Grado ID: ' . $nuevoGrado;

            $dataEmail = [
                'folio'             => $folioGenerado,
                'alumno'            => $alumno,
                'pago'              => $datosPago,
                'nuevo_grado_nombre'=> $nombreGrado,
                'qrp'               => $qrp
            ];

            $mensajeHTML = view('CambioGrado/email_recibo', $dataEmail);

            $email = \Config\Services::email();
            $email->setFrom('pagos@sjs.edu.mx', 'St Joseph School'); 
            $email->setTo($datosPago['email']);
            $email->setBCC('eve.consultores@gmail.com'); 
            $email->setSubject('Comprobante de Pago - Folio #' . $folioGenerado);
            $email->setMessage($mensajeHTML);
            $email->setMailType('html');

            $msgExtra = '';
            if(!$email->send()){
                $msgExtra = ' (Correo no enviado por config local)';
            }

            return $this->response->setJSON([
                'status' => 'success', 
                'msg' => 'Alumno activado en ciclo ' . $idCicloActivo . '. Folio: ' . $folioGenerado . $msgExtra
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
}