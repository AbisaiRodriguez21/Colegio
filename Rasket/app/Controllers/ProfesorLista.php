<?php namespace App\Controllers;

use App\Models\ProfesorModel;
use CodeIgniter\Controller;

class ProfesorLista extends BaseController
{
    

    // =========================================================================
    // 1. GUARDADO (POST)
    // =========================================================================

    /**
     * Guarda la carga completa de un grado específico.
     * Recibe un array de materias activas.
     */
    public function guardar_carga_grado()
    {
        // CANDADO
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acción no autorizada.');
        }

        $request = \Config\Services::request();
        $model = new ProfesorModel();

        // 1. Recibir datos
        $id_profesor = $request->getPost('id_profesor');
        $id_grado    = $request->getPost('id_grado');
        
        // Array de materias marcadas (checked). Si no hay ninguna, llega null o vacío.
        $materias_seleccionadas = $request->getPost('materias') ?? [];

        // 2. Ejecutar lógica en Modelo (Transacción segura)
        $model->guardarCargaMasiva($id_profesor, $id_grado, $materias_seleccionadas);

        // 3. Redirigir
        return redirect()->back()->with('success', 'Carga académica actualizada correctamente.');
    }

    /**
     * (LEGACY) Recibe el POST del formulario y guarda los cambios
     */
    public function guardar_materia()
    {
        // CANDADO
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acción no autorizada.');
        }

        $request = \Config\Services::request();
        $model = new ProfesorModel();

        // 1. Recibir datos del formulario
        $id_profesor = $request->getPost('id_profesor');
        $id_materia  = $request->getPost('id_materia');
        
        // El checkbox solo envía valor si está marcado. 
        $activo = $request->getPost('activo') ? 1 : 0; 

        // 2. Guardar en BD
        $model->actualizarAsignacion($id_profesor, $id_materia, $activo);

        // 3. Redirigir
        $mensaje = $activo ? 'Materia asignada correctamente.' : 'Materia desasignada correctamente.';
        return redirect()->back()->with('success', $mensaje);
    }

    // =========================================================================
    // 2. VISTAS (GET)
    // =========================================================================

    /**
     * Muestra la lista de profesores.
     */
    public function index()
    {
        // CANDADO
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tienes permiso para ver esta sección.');
        }

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
     * Muestra la carga de un profesor (Solo Lectura)
     */
    public function ver($id)
    {
        // CANDADO
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tienes permiso para ver esta sección.');
        }

        $model = new ProfesorModel();

        $profesor = $model->getProfesor($id);
        if (!$profesor) {
            return redirect()->back()->with('error', 'Profesor no encontrado.');
        }

        // Obtener lista plana
        $materiasRaw = $model->getMateriasAsignadas($id);

        $data = [
            'profesor' => $profesor,
            'carga'    => $materiasRaw // Pasamos el array directo
        ];
        
        return view('profesores/ver_materias', $data);
    }

    /**
     * Vista para Asignar (Acordeón con Switches)
     */
    public function asignar($id)
    {
        // CANDADO
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'No tienes permiso para ver esta sección.');
        }

        $model = new ProfesorModel();

        // 1. Datos del Profesor
        $profesor = $model->getProfesor($id);
        if (!$profesor) {
            return redirect()->back()->with('error', 'Profesor no encontrado.');
        }

        // 2. Obtener Grados
        $grados = $model->getGrados();
        
        // 3. Estructura Maestra: Vamos a meter las materias DENTRO de cada grado
        foreach ($grados as &$grado) {
            // Buscamos las materias de este grado
            $materias = $model->getMateriasPorGrado($grado['id_grado']);
            
            // Para cada materia, calculamos su estado
            foreach ($materias as &$materia) {
                // Pequeña protección extra por si tu BD usa 'Id_materia' o 'id_materia'
                $idMat = $materia['Id_materia'] ?? $materia['id_materia'];
                
                $estado = $model->verificarEstadoMateria($idMat, $id);
                $materia['estado_asignacion'] = $estado; // 'propia', 'libre'
            }
            unset($materia); // Romper referencia

            $grado['lista_materias'] = $materias;
        }
        unset($grado); // Romper referencia

        $data['profesor'] = $profesor;
        $data['grados_completos'] = $grados; // Este array trae TODO listo

        return view('profesores/carga_materias', $data);
    }

    // =========================================================================
    // 3. ELIMINAR / BAJA
    // =========================================================================

    /**
     * Da de baja a un profesor (Cambia estatus a 3).
     */
    public function eliminar($id)
    {
        // CANDADO
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acción no autorizada.');
        }

        $model = new ProfesorModel();

        // 1. Verificar que exista
        $profesor = $model->find($id);
        if (!$profesor) {
            return redirect()->back()->with('error', 'Profesor no encontrado.');
        }

        // 2. Verificar que NO tenga materias activas
        if ($model->tieneMaterias($id)) {
            return redirect()->back()->with('error', 'No se puede dar de baja: El profesor tiene materias activas asignadas. Primero retira su carga académica.');
        }

        // 3. DAR DE BAJA (Soft Delete)
        // Cambiamos estatus a 3 (Baja) en lugar de borrar el registro
        $model->update($id, ['estatus' => 3]);

        return redirect()->back()->with('success', 'El profesor ha sido dado de baja correctamente.');
    }
}