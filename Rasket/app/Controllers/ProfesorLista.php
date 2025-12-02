<?php namespace App\Controllers;

use App\Models\ProfesorModel;
use CodeIgniter\Controller;

class ProfesorLista extends Controller
{
    public function index()
    {
        $model = new ProfesorModel();
        $profesores = $model->getProfesoresActivos();

        // Verificar si cada profesor tiene materias antes de enviar a la vista
        foreach ($profesores as &$p) {
            $p['tiene_materias'] = $model->tieneMaterias($p['id']);
        }
        unset($p); 

        $data['profesores'] = $profesores;
        
        return view('profesores/lista', $data);
    }

    /**
     * Elimina un profesor y redirige.
     */
    public function eliminar($id)
    {
        $model = new ProfesorModel();

        $profesor = $model->find($id);
        if (!$profesor) {
            return redirect()->to(base_url('profesor/lista'))->with('error', 'Profesor no encontrado.');
        }
        

        if (!$model->tieneMaterias($id)) {
            $model->delete($id);
            return redirect()->to(base_url('profesor/lista'))->with('success', 'Profesor eliminado correctamente.');
        } else {
             return redirect()->to(base_url('profesor/lista'))->with('error', 'No se puede eliminar: tiene materias asignadas activas.');
        }
    }
    
    /**
     * Muestra las materias asignadas a un profesor.
     */
    public function ver($id)
    {
        $model = new ProfesorModel();
        $data['profe'] = $model->getProfesor($id);
        $data['materias'] = $model->getMateriasAsignadas($id);
        
        return view('profesores/ver_materias', $data);
    }
}