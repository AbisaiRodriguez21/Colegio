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

        // 1. Se verifica que el usuario correcto esté logueado 
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

        // 4. Pasar el nivel real a la vista (para permisos)
        $data['user_level'] = $session->get('nivel');

        return view('boletas/calificar_boleta', $data);
    }

    // =========================================================================
    // 2. ACTUALIZACIÓN AJAX (Edición Celda por Celda)
    // =========================================================================
    public function actualizar()
    {
        // 1. Verificación de seguridad básica: Solo aceptamos peticiones AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody("Prohibido");
        }

        $request = $this->request;
        $session = session();

        // --- CANDADO DE SEGURIDAD (NUEVO) ---
        // Si el usuario es Alumno (Nivel 7), rechazamos la petición inmediatamente.
        // Esto protege el sistema incluso si intentan hackear la vista.
        if ($session->get('nivel') == 7) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No tienes permisos para editar.']);
        }
        // ------------------------------------

        // 2. Recibir datos del formulario
        $id_cal = $request->getPost('scoreId');
        $valor  = $request->getPost('value');
        $tipo   = $request->getPost('type'); 

        // 3. Obtenemos el ID del usuario logueado (quién hace el cambio)
        $id_usuario = $session->get('id'); 

        if (!$id_cal || !isset($valor) || !$id_usuario) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Datos incompletos o sesión expirada']);
        }

        $model = new CalificacionesModel();

        // 4. Ejecutar actualización pasando el ID de usuario
        $resultado = $model->updateCalificacion($id_cal, $tipo, $valor, $id_usuario);

        if ($resultado) {
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Guardado']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error en BD']);
        }
    }
}