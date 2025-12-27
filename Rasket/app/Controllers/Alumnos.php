<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\GradoModel;
use App\Models\CicloEscolarModel;
use CodeIgniter\Controller; 

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
        $userModel  = new UserModel();

        $prediccion = $userModel->generarProximaMatricula();

        $data = [
            'title'             => $titulo,
            'estatus_form'      => $estatus,
            'grados'            => $gradoModel->orderBy('id_grado', 'ASC')->asArray()->findAll(),
            'ciclos'            => $cicloModel->orderBy('nombreCicloEscolar', 'ASC')->asArray()->findAll(),
            'proxima_matricula' => $prediccion
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
        
        // 1. Generar matrícula real al momento 
        $matriculaReal = $model->generarProximaMatricula();

        // 2. Obtener datos
        $data = $this->obtenerDatosDelPost();
        
        // 3. Inyectar datos de sistema
        $data['matricula'] = $matriculaReal;
        $data['email']     = $matriculaReal . '@sjs.edu.mx'; 
        
        // Estatus dinámico (o forzar preinscripción si falla)
        $data['estatus']   = $this->request->getPost('estatus') ?? 2; 
        
        $tipoRegistro = ($data['estatus'] == 1) ? 'Alumno registrado' : 'Preinscripción guardada';

        if ($model->insert($data)) {
            return redirect()->back()
                             ->with('success', "¡ÉXITO! $tipoRegistro correctamente.\nMatrícula asignada: $matriculaReal");
        } else {
            return redirect()->back()
                             ->with('error', 'Ocurrió un error al guardar.')
                             ->withInput();
        }
    }

    // =========================================================================
    // 4. MAPEO DE DATOS
    // =========================================================================
    private function obtenerDatosDelPost() {
        return [
            // Identidad (Mayúsculas para estandarizar)
            'Nombre'         => strtoupper($this->request->getPost('Nombre')),
            'ap_Alumno'      => strtoupper($this->request->getPost('ap_Alumno')),
            'am_Alumno'      => strtoupper($this->request->getPost('am_Alumno')),
            'curp'           => strtoupper($this->request->getPost('curp')),
            'rfc'            => strtoupper($this->request->getPost('rfc')),
            'nia'            => strtoupper($this->request->getPost('nia')),
            'fechaNacAlumno' => $this->request->getPost('fechaNacAlumno'),
            'sexo_alum'      => $this->request->getPost('sexo_alum'),
            
            // Contacto
            'direccion'      => strtoupper($this->request->getPost('direccion')),
            'cp_alum'        => $this->request->getPost('cp_alum'),
            'telefono_alum'  => $this->request->getPost('telefono_alum'),
            'mail_alumn'     => $this->request->getPost('email_tutor'), 
            
            // Académico
            'grado'            => $this->request->getPost('grado'),
            'generacionactiva' => $this->request->getPost('cicloEscolar'), 
            'extra'            => $this->request->getPost('extra'),
            
            // Fijos
            'pass'             => '123456789', 
            'nivel'            => 7, 
            'activo'           => 1 
        ];
    }
}