<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\GradoModel;
use App\Models\CicloEscolarModel;
use CodeIgniter\Controller; 

class Alumnos extends BaseController 
{
    public function registro() 
    {
        $gradoModel = new GradoModel();
        $cicloModel = new CicloEscolarModel();

        $data = [
            'title'  => 'Registro de Alumnos',
            'grados' => $gradoModel->orderBy('id_grado', 'ASC')->asArray()->findAll(),
            'ciclos' => $cicloModel->orderBy('nombreCicloEscolar', 'ASC')->asArray()->findAll()
        ];
        
        return view('alumnos/registro', $data);
    }

    public function guardar()
    {
        $model = new UserModel();
        
        $data = $this->obtenerDatosDelPost();
        $data['estatus'] = 2; 

        $model->insert($data); 

        return redirect()->to('alumnos/registro')
                         ->with('success', 'Alumno registrado exitosamente.');
    }

    public function preinscripciones() 
    {
        $gradoModel = new GradoModel();
        $cicloModel = new CicloEscolarModel();

        $data = [
            'title'  => 'Preinscripciones', 
            'grados' => $gradoModel->orderBy('id_grado', 'ASC')->asArray()->findAll(),
            'ciclos' => $cicloModel->orderBy('nombreCicloEscolar', 'ASC')->asArray()->findAll()
        ];
        
        return view('alumnos/preinscripciones', $data);
    }

    public function guardar_preinscripcion()
    {
        $model = new UserModel();
        
        $data = $this->obtenerDatosDelPost();
        $data['estatus'] = 1; 

        $model->insert($data); 

        return redirect()->to('alumnos/preinscripciones')
                         ->with('success', 'Preinscripción guardada exitosamente.');
    }


    private function obtenerDatosDelPost() {
        return [
            'Nombre'        => $this->request->getPost('Nombre'),
            'ap_Alumno'     => $this->request->getPost('ap_Alumno'),
            'am_Alumno'     => $this->request->getPost('am_Alumno'),
            'curp'          => $this->request->getPost('curp'),
            'rfc'           => $this->request->getPost('rfc'),
            'nia'           => $this->request->getPost('nia'),
            'fechaNacAlumno' => $this->request->getPost('fechaNacAlumno'),
            'sexo_alum'     => $this->request->getPost('sexo_alum'),
            'direccion'     => $this->request->getPost('direccion'),
            'cp_alum'       => $this->request->getPost('cp_alum'),
            'telefono_alum' => $this->request->getPost('telefono_alum'),
            'email'         => $this->request->getPost('email'),
            'pass'          => $this->request->getPost('pass'),
            'grado'         => $this->request->getPost('grado'),
            'extra'         => $this->request->getPost('extra'),
            'matricula'     => $this->request->getPost('matricula'),
            'generacionactiva' => $this->request->getPost('cicloEscolar'), 
            'nivel'         => 7, 
        ];
    }
}