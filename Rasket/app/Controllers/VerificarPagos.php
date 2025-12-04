<?php

namespace App\Controllers;
use App\Models\PagoModel;

class VerificarPagos extends BaseController
{
    public function index()
    {
        $request = service('request');
        $busqueda = $request->getGet('q'); 

        $pagoModel = new PagoModel();
        
        $data = $pagoModel->getPagosPaginados($busqueda, 10);
        
        $data['busqueda'] = $busqueda;
        $data['title'] = "Verificar Pagos";

        return view('pagos/verificar', $data);
    }

    public function validar() {
    }
}