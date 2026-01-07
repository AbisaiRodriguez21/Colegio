<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\RegistroProfesorModel;

class RegistroProfesor extends BaseController
{
    public function nuevo()
    {
        // Seguridad
        if (!$this->_verificarPermisos()) {
            return redirect()->to('/dashboard')->with('error', 'No autorizado.');
        }

        $model = new RegistroProfesorModel();
        
        $ciclos  = $model->getCiclos();
        $estados = $model->getEstados();

        // ========================================================
        // GENERACIÓN DE MATRÍCULA 
        // Formato: AñoMesDiaHora(12h)MinutosSegundos
        // ========================================================
        $matricula = date('Ymdgis'); 

        $data = [
            'ciclos'    => $ciclos,
            'estados'   => $estados,
            'matricula' => $matricula // Se envía a la vista
        ];

        return view('RegistroProfesor/formulario', $data);
    }

    public function guardar()
    {
        if (!$this->_verificarPermisos()) {
            return redirect()->to('/dashboard');
        }

        $request = \Config\Services::request();
        $model   = new RegistroProfesorModel();

        // Recibir contraseña en texto plano y asignarla directamente
        $passwordPlana = $request->getPost('pass');

        $data = [
            'generacionactiva' => $request->getPost('cescolar'),
            'Nombre'           => $request->getPost('Nombre'),
            'email'            => $request->getPost('email'),
            'pass'             => $passwordPlana, 
            'nivel'            => 5, 
            'sexo_alum'        => $request->getPost('sexo_alum'),
            'estado'           => $request->getPost('Estado'),
            'municipio'        => $request->getPost('Municipio'),
            'localidad'        => $request->getPost('localidad'),
            'direccion'        => $request->getPost('direccion_alum'), 
            'cp_alum'          => $request->getPost('cp_alum'),
            'ap_Alumno'        => $request->getPost('ap_Alumno'),
            'am_Alumno'        => $request->getPost('am_Alumno'),
            'fechaNacAlumno'   => $request->getPost('fechaNacAlumno'),
            'curp'             => $request->getPost('curp'),
            'rfc'              => $request->getPost('rfc'),
            
            // Recibimos la matrícula del input (que ahora será visible)
            'matricula'        => $request->getPost('matricula'),
            
            'extra'            => $request->getPost('extra'),
            'activo'           => 1 
        ];

        if ($model->insert($data)) {
            return redirect()->to(base_url('registro-profesor'))
                             ->with('success', 'Profesor registrado correctamente. (Matrícula: ' . $data['matricula'] . ')');
        } else {
            return redirect()->back()->withInput()->with('error', 'Error al guardar en la base de datos.');
        }
    }

    // AJAX de municipios y localidades
    public function getMunicipios($idEstado) {
         if (!$this->_verificarPermisos()) { return $this->response->setStatusCode(403); }
         $db = \Config\Database::connect();
         return $this->response->setJSON($db->table('municipios')->where('estado_id', $idEstado)->orderBy('nombre', 'ASC')->get()->getResultArray());
    }

    public function getLocalidades($idMunicipio) {
         if (!$this->_verificarPermisos()) { return $this->response->setStatusCode(403); }
         $db = \Config\Database::connect();
         return $this->response->setJSON($db->table('localidades')->where('municipio_id', $idMunicipio)->orderBy('nombre', 'ASC')->get()->getResultArray());
    }

    // ==========================================================================
    // SECCIÓN 2: GESTIÓN DE GRADOS
    // ==========================================================================

    public function grados()
    {
        if (!$this->_verificarPermisos()) {
            return redirect()->to('/dashboard')->with('error', 'No autorizado.');
        }

        $model = new RegistroProfesorModel();
        
        // Usamos la función específica creada en el modelo
        $data['grados'] = $model->listarGrados();

        return view('RegistroProfesor/grados', $data);
    }

    public function guardarGrado()
    {
        if (!$this->_verificarPermisos()) { return redirect()->to('/dashboard'); }

        $request = \Config\Services::request();
        $model   = new RegistroProfesorModel();

        $nombre = $request->getPost('nombreGrado'); // Ajustado al name del input nuevo

        if (!empty($nombre)) {
            $model->insertarGrado($nombre);
            return redirect()->to(base_url('registro/grados'))->with('success', 'Grado añadido correctamente.');
        } else {
            return redirect()->back()->with('error', 'El nombre del grado no puede estar vacío.');
        }
    }

    public function eliminarGrado($id)
    {
        if (!$this->_verificarPermisos()) { return redirect()->to('/dashboard'); }

        $model = new RegistroProfesorModel();
        $model->borrarGrado($id);

        return redirect()->to(base_url('registro/grados'))->with('success', 'Grado eliminado.');
    }
}