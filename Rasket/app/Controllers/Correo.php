<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CorreoModel;

class Correo extends BaseController
{

    /**
     * Muestra el detalle de un correo específico
     */
    public function ver($id)
    {
        $model = new CorreoModel();
        
        // Buscamos el correo por ID
        $correo = $model->find($id);

        if (!$correo) {
            return redirect()->to(base_url('correo'))->with('error', 'El correo no existe.');
        }

        $data['c'] = $correo; // Pasamos el correo como variable 'c'
        
        return view('correos/leer_correo', $data);
    }

    public function index()
    {
        $request = \Config\Services::request();
        $model = new CorreoModel();
        
        // Simulamos ID de usuario logueado (Cámbialo por session()->get('id') cuando tengas Auth)
        $userId = 1;

        // Filtro por defecto ahora es 'recibidos'
        $filtro = $request->getGet('filtro') ?? 'recibidos';
        
        $data['correos'] = $model->getCorreos($filtro, $userId);
        
        // Títulos
        $titulos = [
            'recibidos'  => 'Bandeja de Entrada',
            'enviados'   => 'Enviados / Historial',
            'destacados' => 'Destacados',
            'archivados' => 'Archivados',
            'papelera'   => 'Papelera'
        ];
        $data['titulo'] = $titulos[$filtro] ?? 'Bandeja';
        $data['filtro_actual'] = $filtro;
        
        return view('correos/correo_index', $data);
    }

    /**
     * Procesa las acciones de los checkboxes (Eliminar, Archivar, Destacar)
     */
    public function acciones_masivas()
    {
        $request = \Config\Services::request();
        $model = new CorreoModel();
        
        $accion = $request->getPost('accion'); 
        $ids = $request->getPost('ids'); 
        
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No seleccionaste ningún correo.');
        }

        switch ($accion) {
            // --- ACCIONES DE PAPELERA ---
            case 'papelera': // Mover a papelera
                $model->set(['eliminado' => 1])->whereIn('id', $ids)->update();
                $msj = 'Movido a la papelera.';
                break;
                
            case 'restaurar': // Sacar de papelera (Recuperar)
                $model->set(['eliminado' => 0])->whereIn('id', $ids)->update();
                $msj = 'Correos restaurados a la bandeja de entrada.';
                break;

            case 'eliminar_total': // Borrar para siempre
                $model->whereIn('id', $ids)->delete();
                $msj = 'Correos eliminados permanentemente.';
                break;

            // --- ACCIONES DE ARCHIVO ---
            case 'archivar': // Mover a archivo
                // Importante: archivado=1 y eliminado=0 (por si estaba en papelera)
                $model->set(['archivado' => 1, 'eliminado' => 0])->whereIn('id', $ids)->update();
                $msj = 'Correos archivados.';
                break;

            case 'desarchivar': // Sacar de archivo
                $model->set(['archivado' => 0])->whereIn('id', $ids)->update();
                $msj = 'Correos devueltos a la bandeja principal.';
                break;

            // --- ACCIONES DE ESTRELLA ---
            case 'destacar':
                $model->set(['destacado' => 1])->whereIn('id', $ids)->update();
                $msj = 'Marcado como destacado.';
                break;
                
            case 'no_destacar':
                $model->set(['destacado' => 0])->whereIn('id', $ids)->update();
                $msj = 'Desmarcado.';
                break;
        }

        return redirect()->back()->with('success', $msj);
    }

    public function redactar()
    {
        $model = new CorreoModel();
        $data['grados'] = $model->getGrados();

        $data['niveles_educativos'] = [
            0 => 'Sin Grado', 1 => 'Maternal', 2 => 'Kinder', 
            3 => 'Primaria', 4 => 'Secundaria', 5 => 'Bachiller'
        ];

        return view('correos/redactar_correo', $data);
    }

    /**
     * Devuelve los datos del correo en formato JSON para el Modal
     */
    public function ajax_ver($id)
    {
        $model = new CorreoModel();
        $correo = $model->find($id);

        if ($correo) {
            // Formateamos la fecha para que se vea bien
            $correo['fecha_formateada'] = date('d M Y, h:i A', strtotime($correo['fecha_envio']));
            
            // Si hay adjunto, preparamos la URL completa
            $correo['url_adjunto'] = $correo['adjunto'] ? base_url($correo['adjunto']) : null;
            
            return $this->response->setJSON($correo);
        } else {
            return $this->response->setJSON(['error' => 'Correo no encontrado']);
        }
    }

    public function enviar()
    {
        $request = \Config\Services::request();
        $model = new CorreoModel();
        $emailService = \Config\Services::email();

        // 1. Datos básicos
        $tipoDestinatario = $request->getPost('tipo_destinatario');
        $asunto           = $request->getPost('asunto');
        $mensaje          = $request->getPost('mensaje');
        $emisor_id        = 1; // IMPORTANTE: Cambia esto por session()->get('id') o similar

        // 2. Manejo del Archivo Adjunto
        $rutaAdjunto = null;
        $archivo = $this->request->getFile('adjunto');

        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            // Guardar en public/uploads/adjuntos
            $nombreNuevo = $archivo->getRandomName();
            $archivo->move(ROOTPATH . 'public/uploads/adjuntos', $nombreNuevo);
            $rutaAdjunto = 'uploads/adjuntos/' . $nombreNuevo;
        }

        // 3. Determinar Destinatarios
        $listaCorreos = [];
        $textoPara = ""; // Texto para guardar en la BD (ej: "3 Kinder")
        $gradoDestinoId = null;

        switch ($tipoDestinatario) {
            case 'individual':
                $email = $request->getPost('email_individual');
                if (!empty($email)) {
                    $listaCorreos[] = $email;
                    $textoPara = $email;
                }
                break;

            case 'grado':
                $id_grado = $request->getPost('id_grado');
                $gradoDestinoId = $id_grado;
                $resultados = $model->getEmailsPorGrado($id_grado);
                $listaCorreos = array_column($resultados, 'email');
                
                // Obtener nombre del grado para guardar en BD
                $grados = $model->getGrados(); 
                // Buscamos el nombre simple
                $key = array_search($id_grado, array_column($grados, 'id_grado'));
                $nombreGrado = ($key !== false) ? $grados[$key]['nombreGrado'] : "Grado ID: $id_grado";
                $textoPara = "Grado: " . $nombreGrado;
                break;

            case 'nivel':
                $id_nivel = $request->getPost('id_nivel');
                $resultados = $model->getEmailsPorNivel($id_nivel);
                $listaCorreos = array_column($resultados, 'email');
                $textoPara = "Nivel Completo ID: " . $id_nivel;
                break;
        }

        if (empty($listaCorreos)) {
            return redirect()->back()->withInput()->with('error', 'No hay correos destinatarios válidos.');
        }

        // 4. Configurar Email
        $emailService->setFrom('notificaciones@tucolegio.com', 'Sistema Escolar');
        $emailService->setTo('noreply@tucolegio.com'); 
        $emailService->setBCC($listaCorreos);
        $emailService->setSubject($asunto);
        $emailService->setMessage($mensaje);

        if ($rutaAdjunto) {
            $emailService->attach(ROOTPATH . 'public/' . $rutaAdjunto);
        }

        // 5. Enviar y Guardar en BD
        // --- INICIO DE CÓDIGO DE DEPURACIÓN ---
        
        // 1. Intentamos enviar el correo
        $enviado = $emailService->send();

        // 2. Intentamos guardar en BD INDEPENDIENTEMENTE de si se envió el correo
        // (Para probar que la BD funcione)
        $datosGuardar = [
            'emisor_id' => 1, // <--- OJO: Asegúrate que existe el usuario con ID 1 en tu tabla 'usr'
            'grado_destinario_id' => $gradoDestinoId,
            'fecha_envio' => date('Y-m-d H:i:s'),
            'asunto' => $asunto,
            'para' => $textoPara, 
            'mensaje' => $mensaje,
            'adjunto' => $rutaAdjunto,
            'estado' => $enviado ? 'enviado' : 'error_envio', // Guardamos estado según resultado
            'eliminado' => 0
        ];

        if (!$model->insert($datosGuardar)) {
            // Si falla el guardado, MUESTRA EL ERROR EN PANTALLA
            echo "<h1>Error al guardar en Base de Datos:</h1>";
            echo "<pre>";
            print_r($model->errors()); // Muestra por qué falló el modelo
            print_r($datosGuardar);    // Muestra qué datos intentó guardar
            echo "</pre>";
            die(); // Detiene todo para que leas el error
        }

        // Si llegó aquí, sí guardó en BD. Ahora vemos el correo.
        if ($enviado) {
            return redirect()->to(base_url('correo'))->with('success', 'Guardado y Enviado.');
        } else {
            // Imprimir error de correo si falló el envío SMTP
            // echo $emailService->printDebugger(['headers']); die(); 
            return redirect()->to(base_url('correo'))->with('warning', 'Se guardó en BD, pero el correo NO salió (Revisar SMTP).');
        }
        
        // --- FIN DE CÓDIGO DE DEPURACIÓN ---
    }
}