<?php namespace App\Controllers;

use App\Models\GruposModel;
use CodeIgniter\Controller;

class Grupos extends BaseController {

    

    // =========================================================================
    // 1. PANTALLA PRINCIPAL
    // =========================================================================
    public function index() {
        
        // CANDADO ACTIVADO
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tienes permiso para ver los grupos.');
        }

        // --- TU CÓDIGO ORIGINAL (INTACTO) ---
        $model = new GruposModel();

        $data = [
            'lista_grados' => $model->getGrados(),
            'alumnos'      => $model->getAlumnosPorGrado(null) 
        ];

        return view('grupos/lista_grupos', $data);
    }

    // =========================================================================
    // 2. FILTRO AJAX
    // =========================================================================
    public function filtrar() {
        
        // CANDADO ACTIVADO (Respuesta 403 para AJAX)
        if (!$this->_verificarPermisos()) {
            return $this->response->setStatusCode(403)->setBody('Acceso denegado');
        }

        // --- TU CÓDIGO ORIGINAL (INTACTO) ---
        $request = \Config\Services::request();
        $gradoId = $request->getPost('id_grado'); 
        
        $model = new GruposModel();
        $alumnos = $model->getAlumnosPorGrado($gradoId);

        return view('grupos/tabla_parcial', ['alumnos' => $alumnos]);
    }
}