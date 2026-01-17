<?php namespace App\Controllers;

use App\Models\GlobalConfigModel;

class GlobalConfig extends BaseController
{
    // Cargar datos para llenar el modal
    public function getDatos($id_config)
    {
        // Seguridad: Solo Nivel 1
        if (session()->get('nivel') != 1) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        $model = new GlobalConfigModel();

        $data = [
            'meses'  => $model->getMeses(),
            'ciclos' => $model->getCiclos(),
            'actual' => $model->getConfiguracionActual($id_config)
        ];

        return $this->response->setJSON($data);
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