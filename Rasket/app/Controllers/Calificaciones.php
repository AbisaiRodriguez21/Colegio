<?php namespace App\Controllers;

use App\Models\CalificacionesModel;

class Calificaciones extends BaseController
{
    // =========================================================================
    // 1. PANTALLA PRINCIPAL (La Sábana Editable)
    // =========================================================================
    public function editar($id_grado)
    {
        $session = session();

        // 1. CORRECCIÓN: Usamos 'id' que es como lo guarda tu Auth.php
        if (!$session->has('id')) {
            return redirect()->to('/login'); 
        }

        // 2. Obtener el modelo
        $model = new CalificacionesModel();

        // 3. Obtener la sábana
        $data = $model->getSabana($id_grado);

        if (!$data) {
            return "Error: Grado no encontrado o sin configuración.";
        }

        // 4. Pasar el nivel real a la vista (Tu Auth.php guarda 'nivel')
        $data['user_level'] = $session->get('nivel');

        return view('boletas/calificar_boleta', $data);
    }

    // =========================================================================
    // 2. ACTUALIZACIÓN AJAX (Edición Celda por Celda)
    // =========================================================================
    public function actualizar()
    {
        // Solo permitimos peticiones AJAX para no exponer la URL
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody("Prohibido");
        }

        $request = $this->request;
        $session = session();

        // 1. Recibir datos del formulario JS
        $id_cal = $request->getPost('scoreId');
        $valor  = $request->getPost('value');
        $tipo   = $request->getPost('type'); // 'score' o 'absence'

        // 2. SEGURIDAD: Obtener el nivel real desde la SESIÓN (No desde el POST)
        // Esto evita que un alumno hackee el sistema enviando "nivel=1"
        $nivel_usuario = $session->get('nivel'); 

        // Validar que tengamos datos mínimos
        if (!$id_cal || !isset($valor) || !$nivel_usuario) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Datos incompletos']);
        }

        $model = new CalificacionesModel();

        // 3. Ejecutar actualización
        $resultado = $model->updateCalificacion($id_cal, $tipo, $valor, $nivel_usuario);

        if ($resultado) {
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Guardado']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error en BD']);
        }
    }
}