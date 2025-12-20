<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\GradoModel;
use App\Models\CicloEscolarModel;
use CodeIgniter\Controller; 

class Alumnos extends BaseController 
{
    // =========================================================================
    // 1. REGISTRO DE ALUMNOS (NIVEL 7)
    // =========================================================================
    public function registro() 
    {
        $gradoModel = new GradoModel();
        $cicloModel = new CicloEscolarModel();
        $userModel  = new UserModel();

        // PREDICCIÓN VISUAL: Calculamos qué matrícula sigue para mostrársela al usuario
        // OJO: Esta no se guarda todavía, es solo informativa.
        $prediccion = $userModel->generarProximaMatricula();

        $data = [
            'title'             => 'Registro de Alumnos',
            'grados'            => $gradoModel->orderBy('id_grado', 'ASC')->asArray()->findAll(),
            'ciclos'            => $cicloModel->orderBy('nombreCicloEscolar', 'ASC')->asArray()->findAll(),
            'proxima_matricula' => $prediccion
        ];
        
        return view('alumnos/registro', $data);
    }

    public function guardar()
    {
        $model = new UserModel();
        
        // SEGURIDAD DE CARRERA:
        // Ignoramos la matrícula que vio el usuario y calculamos la real en este milisegundo
        // para asegurar que sea única si dos personas guardan al mismo tiempo.
        $matriculaReal = $model->generarProximaMatricula();

        // Obtenemos los datos del formulario (Nombre, dirección, etc.)
        $data = $this->obtenerDatosDelPost();
        
        // INYECTAMOS LOS DATOS DE SISTEMA
        $data['matricula'] = $matriculaReal;
        $data['email']     = $matriculaReal . '@sjs.edu.mx'; // Email institucional automático
        $data['estatus']   = 2; // 2 = Alumno Activo (según tu lógica)

        if ($model->insert($data)) {
            return redirect()->to('alumnos/registro')
                             ->with('success', 'Alumno registrado exitosamente. Matrícula asignada: ' . $matriculaReal);
        } else {
            return redirect()->back()
                             ->with('error', 'Ocurrió un error al guardar.')
                             ->withInput();
        }
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
            // Datos Personales
            'Nombre'         => $this->request->getPost('Nombre'),
            'ap_Alumno'      => $this->request->getPost('ap_Alumno'),
            'am_Alumno'      => $this->request->getPost('am_Alumno'),
            'curp'           => $this->request->getPost('curp'),
            'rfc'            => $this->request->getPost('rfc'),
            'nia'            => $this->request->getPost('nia'),
            'fechaNacAlumno' => $this->request->getPost('fechaNacAlumno'),
            'sexo_alum'      => $this->request->getPost('sexo_alum'),
            
            // Contacto
            'direccion'      => $this->request->getPost('direccion'),
            'cp_alum'        => $this->request->getPost('cp_alum'),
            'telefono_alum'  => $this->request->getPost('telefono_alum'),
            // 'email' se genera automáticamente en guardar()
            
            // Seguridad
            'pass'           => '123456789', // CONTRASEÑA POR DEFECTO FIJA
            
            // Académico
            'grado'            => $this->request->getPost('grado'),
            'extra'            => $this->request->getPost('extra'),
            'generacionactiva' => $this->request->getPost('cicloEscolar'), 
            'nivel'            => 7, // 7 = Alumno
        ];
    }
}