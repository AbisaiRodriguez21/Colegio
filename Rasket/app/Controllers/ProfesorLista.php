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

    public function asignar($id)
    {
        $model = new ProfesorModel();

        // 1. Datos del Profesor
        $profesor = $model->getProfesor($id);
        if (!$profesor) {
            return redirect()->back()->with('error', 'Profesor no encontrado.');
        }

        // 2. Obtener Grados
        $grados = $model->getGrados();
        
        // 3. Estructura Maestra: Vamos a meter las materias DENTRO de cada grado
        // para que la vista solo haga "foreach" y listo.
        foreach ($grados as &$grado) {
            // Buscamos las materias de este grado
            $materias = $model->getMateriasPorGrado($grado['id_grado']);
            
            // Para cada materia, calculamos su estado (si está ocupada, si es mía, etc)
            foreach ($materias as &$materia) {
                $estado = $model->verificarEstadoMateria($materia['id_materia'], $id);
                $materia['estado_asignacion'] = $estado; // 'propia', 'ocupada', 'libre'
            }
            unset($materia); // Romper referencia

            $grado['lista_materias'] = $materias;
        }
        unset($grado); // Romper referencia

        $data['profesor'] = $profesor;
        $data['grados_completos'] = $grados; // Este array trae TODO listo

        return view('profesores/carga_materias', $data);
    }


    /**
     * Recibe el POST del formulario y guarda los cambios
     */
    public function guardar_materia()
    {
        $request = \Config\Services::request();
        $model = new ProfesorModel();

        // 1. Recibir datos del formulario
        $id_profesor = $request->getPost('id_profesor');
        $id_materia  = $request->getPost('id_materia');
        
        // El checkbox solo envía valor si está marcado. 
        // Si viene '1', es activo. Si no viene nada, es inactivo (0).
        $activo = $request->getPost('activo') ? 1 : 0; 

        // 2. Guardar en BD
        $model->actualizarAsignacion($id_profesor, $id_materia, $activo);

        // 3. Redirigir de vuelta al acordeón (misma página)
        // Usamos 'with' para mostrar una alerta de éxito
        $mensaje = $activo ? 'Materia asignada correctamente.' : 'Materia desasignada correctamente.';
        
        return redirect()->back()->with('success', $mensaje);
    }
}