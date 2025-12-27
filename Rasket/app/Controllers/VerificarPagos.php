<?php namespace App\Controllers;

use App\Models\PagoModel;

class VerificarPagos extends BaseController
{
    // =========================================================================
    // 1. VISTA PRINCIPAL
    // =========================================================================
    public function index()
    {
        // 1. Seguridad
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $request = \Config\Services::request();
        $model = new PagoModel();

        // 2. Parámetros de búsqueda y paginación
        $busqueda = $request->getGet('q');
        $perPage  = 30; // Mostrar 30 por página como en tu ejemplo

        // 3. Obtener datos
        $pagos = $model->getPagosPendientes($busqueda, $perPage);
        $pager = $model->pager;

        // 4. Cálculos para el texto "Mostrando X-Y de Z"
        $totalRows = $pager->getTotal();
        $currentPage = $pager->getCurrentPage();
        
        $inicio = ($totalRows > 0) ? ($currentPage - 1) * $perPage + 1 : 0;
        $fin    = ($currentPage * $perPage);
        if ($fin > $totalRows) $fin = $totalRows;

        $data = [
            'pagos'     => $pagos,
            'pager'     => $pager,
            'busqueda'  => $busqueda,
            // Datos para el texto informativo
            'info_paginacion' => [
                'inicio' => $inicio,
                'fin'    => $fin,
                'total'  => $totalRows
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
        // Si quieres nombre y apellido, puedes concatenar: session('Nombre') . ' ' . session('ap_Alumno');
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