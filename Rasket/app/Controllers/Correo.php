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

    /**
     * BANDEJA PRINCIPAL
     */
    public function index()
    {
        $request = \Config\Services::request();
        $model = new CorreoModel();
        
        // 1. RECUPERAMOS EL ID DE LA SESIÓN (Gracias a tu Auth.php)
        $userId = session()->get('id'); 

        // Seguridad: Si por alguna razón no hay sesión (expiró), lo mandamos al login
        if (!$userId) { 
            return redirect()->to(base_url('login'))->with('error', 'Tu sesión ha expirado.'); 
        }

        // 2. Filtros y Lógica
        $filtro = $request->getGet('filtro') ?? 'recibidos';
        
        // El modelo ahora buscará correos donde emisor_id sea $userId (Enviados)
        // o donde emisor_id NO sea $userId (Recibidos)
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

        // 1. OBTENER ID DEL USUARIO LOGUEADO
        $emisor_id = session()->get('id');

        // Seguridad: Si no hay sesión, mandar al login
        if (!$emisor_id) {
            return redirect()->to(base_url('login'))->with('error', 'Tu sesión ha expirado.');
        }

        // 2. Recoger datos
        $tipoDestinatario = $request->getPost('tipo_destinatario');
        $asunto           = $request->getPost('asunto');
        $mensaje          = $request->getPost('mensaje');

        // 3. Archivo Adjunto
        $rutaAdjunto = null;
        $archivo = $this->request->getFile('adjunto');

        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $nombreNuevo = $archivo->getRandomName();
            $archivo->move(ROOTPATH . 'public/uploads/adjuntos', $nombreNuevo);
            $rutaAdjunto = 'uploads/adjuntos/' . $nombreNuevo;
        }

        // 4. Determinar Destinatarios
        $listaCorreos = [];
        $textoPara = ""; 
        $gradoDestinoId = null;

        switch ($tipoDestinatario) {
            case 'individual':
                $email = $request->getPost('email_individual');
                if (!empty($email)) {
                    $listaCorreos[] = $email;
                    $textoPara = $email; // Solo el correo, sin adornos
                }
                break;

            case 'grado':
                $id_grado = $request->getPost('id_grado');
                $gradoDestinoId = $id_grado;
                
                $resultados = $model->getEmailsPorGrado($id_grado);
                $listaCorreos = array_column($resultados, 'email');
                
                // Nombre bonito del grado
                $grados = $model->getGrados(); 
                $key = array_search($id_grado, array_column($grados, 'id_grado'));
                $nombreGrado = ($key !== false) ? $grados[$key]['nombreGrado'] : "Grado ID: $id_grado";
                $textoPara = "Grado: " . $nombreGrado;
                break;

            case 'nivel':
                $id_nivel = $request->getPost('id_nivel');
                
                $resultados = $model->getEmailsPorNivel($id_nivel);
                $listaCorreos = array_column($resultados, 'email');
                
                $mapaNiveles = [1=>'Maternal', 2=>'Kinder', 3=>'Primaria', 4=>'Secundaria', 5=>'Bachiller'];
                $nombreNivel = $mapaNiveles[$id_nivel] ?? "Nivel $id_nivel";
                $textoPara = "Todo el Nivel: " . $nombreNivel;
                break;
        }

        if (empty($listaCorreos)) {
            return redirect()->back()->withInput()->with('error', 'No se encontraron alumnos con correo.');
        }

        // 5. Configurar Email
        $emailService->setFrom('notificaciones@tucolegio.com', 'Sistema Escolar');
        $emailService->setTo('noreply@tucolegio.com'); 
        $emailService->setBCC($listaCorreos);
        $emailService->setSubject($asunto);
        $emailService->setMessage($mensaje);

        if ($rutaAdjunto) {
            $emailService->attach(ROOTPATH . 'public/' . $rutaAdjunto);
        }

        // --- CORRECCIÓN CLAVE: Lógica para el texto "Para" ---
        // Si hay más de 1 destinatario, agregamos el conteo (ej: "3 Primaria (25)")
        // Si es solo 1, dejamos el texto limpio (ej: "juan@gmail.com")
        $cantidad = count($listaCorreos);
        $paraFinal = ($cantidad > 1) ? $textoPara . " (" . $cantidad . ")" : $textoPara;
        // -----------------------------------------------------

        // 6. Enviar y Guardar
        if ($emailService->send()) {
            // ÉXITO
            $model->insert([
                'emisor_id'           => $emisor_id,
                'grado_destinario_id' => $gradoDestinoId,
                'fecha_envio'         => date('Y-m-d H:i:s'),
                'asunto'              => $asunto,
                'para'                => $paraFinal, // Usamos la variable corregida
                'mensaje'             => $mensaje,
                'adjunto'             => $rutaAdjunto,
                'estado'              => 'enviado',
                'eliminado'           => 0
            ]);

            return redirect()->to(base_url('correo?filtro=enviados'))->with('success', 'Correo enviado correctamente.');
        } else {
            // ERROR (SMTP fallido, guardamos historial)
            $model->insert([
                'emisor_id'           => $emisor_id,
                'grado_destinario_id' => $gradoDestinoId,
                'fecha_envio'         => date('Y-m-d H:i:s'),
                'asunto'              => $asunto,
                'para'                => $paraFinal, // Usamos la variable corregida
                'mensaje'             => $mensaje,
                'adjunto'             => $rutaAdjunto,
                'estado'              => 'error_envio',
                'eliminado'           => 0
            ]);
            
            return redirect()->to(base_url('correo?filtro=enviados'))->with('warning', 'Se guardó en historial, pero falló el envío real (Revisar SMTP).');
        }
    }
}