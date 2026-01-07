<?php namespace App\Controllers;

use App\Controllers\BaseController; // Importante: Aquí es de donde heredamos
use App\Models\AlumnoBachillerModel;

class AsignarArea extends BaseController
{
    // Mapa de Áreas para la vista (Siglas)
    private $mapaAreas = [
        0 => '-',
        1 => 'ADM', // Administración
        2 => 'CE',  // Ciencias Exactas
        3 => 'CS',  // Ciencias Sociales
        4 => 'CC'   // Ciencias de la Comunicación
    ];

    /**
     * Muestra la lista de alumnos
     */
    public function index()
    {
        // 1. SEGURIDAD HEREDADA
        // Usamos la función del BaseController. Por defecto bloquea el nivel 7 (Alumnos).
        if (!$this->_verificarPermisos()) {
            return redirect()->to('/dashboard')->with('error', 'No tienes permiso para acceder a este módulo.');
        }

        $model = new AlumnoBachillerModel();

        // 2. Configuración
        $perPage = 30;
        $alumnos = $model->getAlumnosTercero($perPage);
        $pager = $model->pager;

        // 3. Cálculos del Contador
        $totalRows = $pager->getTotal();
        $currentPage = $pager->getCurrentPage();
        
        $inicio = ($totalRows > 0) ? ($currentPage - 1) * $perPage + 1 : 0;
        $fin    = $currentPage * $perPage;
        if ($fin > $totalRows) $fin = $totalRows;

        $data = [
            'alumnos' => $alumnos,
            'pager'   => $pager,
            'areas'   => $this->mapaAreas,
            'info_paginacion' => [
                'inicio' => $inicio,
                'fin'    => $fin,
                'total'  => $totalRows
            ]
        ];

        return view('bachillerato/asignar_area', $data);
    }

    /**
     * Procesa la actualización masiva
     */
    public function actualizar()
    {
        // 1. SEGURIDAD HEREDADA
        if (!$this->_verificarPermisos()) {
            return redirect()->to('/dashboard')->with('error', 'No autorizado.');
        }

        $request = \Config\Services::request();
        $model = new AlumnoBachillerModel();

        $ids = $request->getPost('id');
        $areas = $request->getPost('area');

        if (empty($ids) || empty($areas)) {
            return redirect()->back()->with('error', 'No hay datos para actualizar.');
        }

        if ($model->actualizarAreas($ids, $areas)) {
            return redirect()->back()->with('success', 'Áreas actualizadas correctamente.');
        } else {
            return redirect()->back()->with('error', 'Ocurrió un error al guardar los datos.');
        }
    }
}