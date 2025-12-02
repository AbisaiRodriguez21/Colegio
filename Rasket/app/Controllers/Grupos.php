<?php namespace App\Controllers;

use App\Models\GruposModel;

class Grupos extends BaseController {

    public function index() {
        $model = new GruposModel();

        $data = [
            'lista_grados' => $model->getGrados(),
            'alumnos'      => $model->getAlumnosPorGrado(null) 
        ];

        return view('grupos/lista_grupos', $data);
    }

    public function filtrar() {
        $request = \Config\Services::request();
        $gradoId = $request->getPost('id_grado'); 
        
        $model = new GruposModel();
        $alumnos = $model->getAlumnosPorGrado($gradoId);

        return view('grupos/tabla_parcial', ['alumnos' => $alumnos]);
    }
}