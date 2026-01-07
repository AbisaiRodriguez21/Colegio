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

        $data = [
            'title'         => $titulo,
            'estatus_form'  => $estatus,
            'grados'        => $gradoModel->orderBy('id_grado', 'ASC')->asArray()->findAll(),
            'ciclos'        => $cicloModel->orderBy('nombreCicloEscolar', 'ASC')->asArray()->findAll(),
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
        
        // 1. Obtener datos LIMPIOS (Sin acentos y en Mayúsculas)
        $data = $this->obtenerDatosDelPost();
        
        // 2. Generar matrícula real
        $matriculaReal = $model->generarProximaMatricula();
        
        // 3. Inyectar datos de sistema
        $data['matricula'] = $matriculaReal;
        $data['email']     = $matriculaReal . '@sjs.edu.mx'; 
        
        // 4. Estatus SIEMPRE es 2
        $data['estatus']   = 2; 

        if ($model->insert($data)) {
            // Muestra Matrícula y Correo
            $mensajeExito = "¡REGISTRO EXITOSO!\n\n";
            $mensajeExito .= "El alumno se guardó correctamente.\n";
            $mensajeExito .= "--------------------------------------\n";
            $mensajeExito .= "Matrícula: " . $matriculaReal . "\n";
            $mensajeExito .= "Correo: " . $data['email'];

            return redirect()->back()->with('success', $mensajeExito);
        } else {
            return redirect()->back()
                             ->with('error', 'Ocurrió un error al guardar.')
                             ->withInput();
        }
    }

    // =========================================================================
    // 4. MAPEO Y LIMPIEZA DE DATOS
    // =========================================================================
    private function obtenerDatosDelPost() {
        
        // LÓGICA DE GRADO: Si no seleccionan nada, asignamos 18 (Sin Grado)
        $gradoSeleccionado = $this->request->getPost('grado');
        $idGradoFinal = (!empty($gradoSeleccionado)) ? $gradoSeleccionado : 18;

        return [
            // Limpieza específica: Sin acentos, con Ñ, todo mayúsculas
            'Nombre'         => $this->_sanitizarInput($this->request->getPost('Nombre')),
            'ap_Alumno'      => $this->_sanitizarInput($this->request->getPost('ap_Alumno')),
            'am_Alumno'      => $this->_sanitizarInput($this->request->getPost('am_Alumno')),
            'curp'           => $this->_sanitizarInput($this->request->getPost('curp')),
            'rfc'            => $this->_sanitizarInput($this->request->getPost('rfc')),
            'nia'            => $this->_sanitizarInput($this->request->getPost('nia')),
            'direccion'      => $this->_sanitizarInput($this->request->getPost('direccion')),
            
            // Estos campos no necesitan limpieza de texto
            'fechaNacAlumno' => $this->request->getPost('fechaNacAlumno'),
            'sexo_alum'      => $this->request->getPost('sexo_alum'),
            'cp_alum'        => $this->request->getPost('cp_alum'),
            'telefono_alum'  => $this->request->getPost('telefono_alum'),
            'mail_alumn'     => $this->request->getPost('email_tutor'), 
            
            'grado'          => $idGradoFinal, // <--- AQUÍ USAMOS EL VALOR CALCULADO
            
            'generacionactiva' => $this->request->getPost('cicloEscolar'), 
            'extra'          => $this->request->getPost('extra'),
            
            // Fijos
            'pass'           => '123456789', 
            'nivel'          => 7, 
            'activo'         => 1 
        ];
    }

    // =========================================================================
    // 5. HELPER: QUITA ACENTOS Y CONVIERTE A MAYÚSCULAS
    // =========================================================================
    private function _sanitizarInput($texto)
    {
        if (empty($texto)) return '';

        // 1. Mapa EXCLUSIVO para vocales (La Ñ no está aquí, así que se salva)
        $acentos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U'
        ];

        // 2. Reemplazamos solo las vocales acentuadas
        $textoSinAcentos = strtr($texto, $acentos);

        // 3. Convertimos a Mayúsculas usando MB_STRTOUPPER
        return mb_strtoupper($textoSinAcentos, 'UTF-8');
    }
}