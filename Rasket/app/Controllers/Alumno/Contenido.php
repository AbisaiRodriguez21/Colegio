<?php

namespace App\Controllers\Alumno;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Models\MateriaModel;

class Contenido extends BaseController
{
    public function index()
    {
        $session = session();

        if (!$session->has('id') || $session->get('nivel') != 7) {
            return redirect()->to('/login');
        }

        // Obtener datos del alumno 
        $userModel = new UsuarioModel();
        $alumno = $userModel->find($session->get('id'));
        $idGrado = $alumno['grado']; // Dato clave

        // Obtener nombre del grado para mostrar en la vista
        $db = \Config\Database::connect();
        $gradoInfo = $db->table('grados')->select('nombreGrado')->where('id_grado', $idGrado)->get()->getRow();
        $nombreGrado = $gradoInfo ? $gradoInfo->nombreGrado : 'Desconocido';

        // Obtener Materias 
        $materiaModel = new MateriaModel();
        $listaMaterias = $materiaModel->obtenerPorGrado($idGrado);

        $data = [
            'nombre'      => $session->get('nombre'),
            'apellidos'   => $session->get('apellidos'),
            'nombreGrado' => $nombreGrado,
            'materias'    => $listaMaterias
        ];

        return view('VistadelAlumno/contenido_materias', $data);
    }
}