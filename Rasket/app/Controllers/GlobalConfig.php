<?php namespace App\Controllers;

use App\Models\GlobalConfigModel;

// Controlador para la configuración global (mes y ciclo activo)

class GlobalConfig extends BaseController
{
    // Cargar datos para llenar el modal
    public function getDatos($id_config)
    {
        if (session()->get('nivel') != 1) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        try {
            $model = new GlobalConfigModel();

            // --- LÓGICA CONDICIONAL ---
            $lista_tiempo = [];

            if ($id_config == 2) { 
                // CASO BACHILLERATO: Usamos los periodos 1-6
                $lista_tiempo = $model->getPeriodosBachillerato();
            } else {
                // CASO PRIMARIA/SECUNDARIA: Usamos los meses normales de la BD
                $lista_tiempo = $model->getMeses();
            }

            $data = [
                // 'meses' ahora puede contener Meses O Periodos, el JS no nota la diferencia
                'meses'  => $lista_tiempo, 
                'ciclos' => $model->getCiclos(),
                'actual' => $model->getConfiguracionActual($id_config)
            ];

            return $this->response->setJSON($data);

        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error Interno: ' . $e->getMessage()
            ]);
        }
    }

    // Guardar cambios
    public function update()
    {
        if (session()->get('nivel') != 1) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No autorizado']);
        }

        $id_config = $this->request->getPost('id_config');
        $id_mes    = $this->request->getPost('id_mes');
        $id_ciclo  = $this->request->getPost('id_ciclo');

        $model = new GlobalConfigModel();

        // Actualizamos la tabla mesycicloactivo
        $update = $model->update($id_config, [
            'id_mes'   => $id_mes,
            'id_ciclo' => $id_ciclo
        ]);

        if ($update) {
            // Guardamos un log o mensaje flash si quieres
            session()->setFlashdata('success', 'Configuración actualizada correctamente.');
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error al guardar en BD']);
        }
    }
}