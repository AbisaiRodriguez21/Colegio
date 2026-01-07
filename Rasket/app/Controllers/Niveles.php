<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NivelesModel;

class Niveles extends BaseController
{
    public function index()
    {
        if (!$this->_verificarPermisos()) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Acceso denegado.');
        }
        
        return view('Niveles/principal');
    }

    public function fetch()
    {
        $request = \Config\Services::request();
        $model = new NivelesModel();

        // 1. Obtener parámetros de la URL AJAX
        $busqueda  = $request->getGet('q');
        $orden     = $request->getGet('orden') ?? 'ASC';
        $columna   = $request->getGet('columna') ?? 'nombre'; // Por defecto nombre
        $porPagina = 25; 

        // 2. Obtener datos del modelo
        $usuarios = $model->getUsuarios($columna, $orden, $busqueda, $porPagina);

        // 3. Cálculos matemáticos para "Mostrando X a Y de Z"
        $pager = $model->pager;
        $total = $pager->getTotal();
        $paginaActual = $pager->getCurrentPage();

        $inicio = ($paginaActual - 1) * $porPagina + 1;
        $fin = $inicio + $porPagina - 1;

        if ($fin > $total) { $fin = $total; }
        if ($total == 0) { $inicio = 0; $fin = 0; }

        // 4. Empaquetar datos
        $data = [
            'usuarios' => $usuarios,
            'pager'    => $pager,
            'orden'    => $orden,
            'columna'  => $columna, // Enviamos cuál es la columna activa
            'busqueda' => $busqueda,
            'info'     => [
                'inicio' => $inicio,
                'fin'    => $fin,
                'total'  => number_format($total)
            ]
        ];

        // 5. Renderizar vista parcial
        $html = view('Niveles/tabla_parcial', $data);

        return $this->response->setJSON(['html' => $html]);
    }

    // =========================================================================
    // ACTUALIZAR CONTRASEÑA (SOLO NIVEL 1)
    // =========================================================================
    public function actualizarPassword()
    {
        // Solo Nivel 1 puede hacer esto
        if (session()->get('nivel') != 1) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'msg' => 'No autorizado']);
        }

        $request = \Config\Services::request();
        $idUsuario = $request->getPost('id');
        $newPass   = trim($request->getPost('pass'));

        // Validación básica
        if (!$idUsuario || $newPass === '') {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'La contraseña no puede estar vacía']);
        }

        $model = new NivelesModel();
        
        // Guardamos tal cual (sin encriptar por solicitud)
        if ($model->update($idUsuario, ['pass' => $newPass])) {
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al actualizar']);
        }
    }
}