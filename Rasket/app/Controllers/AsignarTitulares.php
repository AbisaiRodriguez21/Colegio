<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TitularModel;

class AsignarTitulares extends BaseController
{
    public function index()
    {
        if (!$this->_verificarPermisos()) {
            return redirect()->to('/dashboard')->with('error', 'No autorizado.');
        }

        $model = new TitularModel();

        $grados = $model->getGrados();

        // 1. Obtenemos la lista de ocupados
        $ocupadosRaw = $model->getNivelesOcupados();
        
        // 2. se extrae solo la columna nivelT 
        $ocupados = array_column($ocupadosRaw, 'nivelT');

        // 3. Convertimos todo a String.
        // para asegura que la comparación en la vista (in_array) sea exacta.
        $ocupados = array_map('strval', $ocupados);

        $data = [
            'grados'   => $grados,
            'ocupados' => $ocupados
        ];

        return view('titulares/asignar', $data);
    }

    public function guardar()
    {
        if (!$this->_verificarPermisos()) {
            return redirect()->to('/dashboard');
        }

        $request = \Config\Services::request();
        $model = new TitularModel();

        

        // Preparamos los datos
        $data = [
            'Nombre'    => $request->getPost('nombre'),
            'ap_Alumno' => $request->getPost('paterno'),
            'am_Alumno' => $request->getPost('materno'),
            'email'     => $request->getPost('email'),
            'pass'      => $request->getPost('password'),
            'nivel'     => 9, // Se guarda como Titular por defecto 
            'nivelT'    => $request->getPost('nivelT'),
            'activo'    => 1,  
            'estatus'  => 1  
        ];

        if ($model->insert($data)) {
            return redirect()->to('/asignar-titulares')->with('success', 'Titular registrado correctamente.');
        } else {
            return redirect()->back()->with('error', 'Error al guardar en la base de datos.');
        }
    }
}