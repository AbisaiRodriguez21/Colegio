<?php namespace App\Controllers;

use App\Models\PagoModel;

class VerificarPagos extends BaseController
{
    // =========================================================================
    // 1. VISTA PRINCIPAL
    // =========================================================================
    public function index()
    {
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $request = \Config\Services::request();
        $model = new PagoModel();

        // 1. Obtener parámetros (Búsqueda, Paginación y ORDENAMIENTO)
        $busqueda = $request->getGet('q');
        $columna  = $request->getGet('columna') ?? 'fecha'; // Por defecto fecha
        $dir      = $request->getGet('dir') ?? 'DESC';      // Por defecto descendente
        $perPage  = 30;

        // 2. Pasamos columna y dir al modelo
        $pagos = $model->getPagosPendientes($busqueda, $perPage, $columna, $dir);
        $pager = $model->pager;

        // 3. Cálculos de totales
        $totalRows = $pager->getTotal();
        $currentPage = $pager->getCurrentPage();
        $inicio = ($totalRows > 0) ? ($currentPage - 1) * $perPage + 1 : 0;
        $fin    = ($currentPage * $perPage);
        if ($fin > $totalRows) $fin = $totalRows;

        $data = [
            'pagos'    => $pagos,
            'pager'    => $pager,
            'busqueda' => $busqueda,
            // 4. Pasamos el orden actual a la vista para pintar las flechas
            'ordenActual' => [
                'columna' => $columna,
                'dir'     => $dir
            ],
            'info_paginacion' => [
                'inicio' => $inicio, 'fin' => $fin, 'total' => $totalRows
            ]
        ];

        return view('pagos/verificar', $data);
    }

    // =========================================================================
    // 2. ACCIÓN AJAX: VALIDAR
    // =========================================================================
    public function validar()
    {
        // 1. Seguridad (Candado)
        if (!$this->_verificarPermisos()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'msg' => 'No autorizado']);
        }

        // 2. Recibir el ID del pago
        $request = \Config\Services::request();
        $idPago = $request->getPost('id_pago');
        
        // 3. Obtener SOLO el NOMBRE del usuario logueado
        $quienValida = session('nombre'); 

        // Validación extra por si la sesión no trajera el nombre (evitar que se guarde vacío)
        if (empty($quienValida)) {
            $quienValida = 'Administrador';
        }

        $model = new PagoModel();
        
        // 4. Guardar en BD (Recuerda que el Modelo ya tiene el fix del 49)
        if ($model->validarPago($idPago, $quienValida)) {
            return $this->response->setJSON(['status' => 'success', 'msg' => 'Pago validado correctamente.']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al actualizar la base de datos.']);
        }
    }
}