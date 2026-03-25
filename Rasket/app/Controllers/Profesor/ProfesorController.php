<?php 

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\ProfesorModel;
use App\Models\BoletaModel;
use App\Models\CalificacionesBimestreModel;

class ProfesorController extends BaseController
{
    // =========================================================================
    // 1. DASHBOARD DEL PROFESOR  
    // =========================================================================
    public function dashboard()
    {
        $session = session();
        if (!$session->has('id') || $session->get('nivel') != 5) {
            return redirect()->to('/login');
        }

        $profesorModel = new ProfesorModel();
        
        $materias = $profesorModel->getMateriasDashboardProfesor($session->get('id'));

        $db = \Config\Database::connect();
        $user = $db->table('usr')->select('pass')->where('id', $session->get('id'))->get()->getRowArray();
        $passwordActual = $user ? $user['pass'] : '';

        $data = [
            'materias' => $materias,
            'nombre_profesor' => $session->get('Nombre'), 
            'passwordActual' => $passwordActual // 
        ];

        return view('profesor/dashboard', $data);
    }

    // =========================================================================
    // 2. VISTA DE "MINI-SÁBANA" PARA CALIFICAR
    // =========================================================================
    public function calificarMateria($id_grado, $id_materia)
    {
        $session = session();
        if (!$session->has('id') || $session->get('nivel') != 5) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $profesorModel = new ProfesorModel();
        $boletaModel = new BoletaModel();

        $misMaterias = $profesorModel->getMateriasDashboardProfesor($session->get('id'));
        $tieneAcceso = false;
        foreach ($misMaterias as $mm) {
            if ($mm['id_materia'] == $id_materia && $mm['id_grado'] == $id_grado) {
                $tieneAcceso = true; break;
            }
        }

        if (!$tieneAcceso) {
            return redirect()->to(base_url('profesor/dashboard'))->with('error', 'Acceso denegado: No impartes esta materia.');
        }

        $gradoInfo = $boletaModel->getInfoGrado($id_grado);
        $materiaInfo = $db->table('materia')->where('Id_materia', $id_materia)->get()->getRowArray();
        
        // Ciclo activo universal (Fila 1 de mesycicloactivo)
        $id_ciclo = $db->table('mesycicloactivo')->where('id', 1)->get()->getRow()->id_ciclo;

        // DEFINIR LAS COLUMNAS DINÁMICAS 
        $nivelGrado = $gradoInfo['nivel_grado']; // 
        $periodos = [];

        if ($nivelGrado <= 2) { 
            // Kinder / Maternal -> 3 Momentos
            $periodos = $db->table('momentos')->select('id, nombre')->get()->getResultArray();
        } elseif ($nivelGrado == 3 || $nivelGrado == 4) { 
            // Primaria / Secundaria -> 10 Meses
            $periodos = $db->table('mes')->select('id, nombre')->get()->getResultArray();
        } else { 
            // Bachillerato -> Bimestres
            $periodos = $db->table('bimestres')->select('id, nombre')->get()->getResultArray();
        }

        // ---------------------------------------------------------------------
        // TRAER ALUMNOS Y SUS CALIFICACIONES 
        // ---------------------------------------------------------------------
        $alumnos = $boletaModel->getAlumnosPorGrado($id_grado);

        $notasRaw = $db->table('calificacion')
                       ->where('id_grado', $id_grado)
                       ->where('id_materia', $id_materia)
                       ->where('cicloEscolar', $id_ciclo)
                       ->get()
                       ->getResultArray();

        // 3. Mapear las notas para la vista 
        $notasMap = [];
        foreach ($notasRaw as $nota) {
            $notasMap[$nota['id_usr']][$nota['id_mes']] = $nota['calificacion'];
        }

        $data = [
            'grado' => $gradoInfo,
            'materia' => $materiaInfo,
            'periodos' => $periodos,
            'alumnos' => $alumnos,
            'notasMap' => $notasMap, // La magia está aquí
            'id_ciclo' => $id_ciclo
        ];

        return view('profesor/calificar_materia', $data);
    }

    // =========================================================================
    // 3. GUARDAR CALIFICACIÓN (AJAX)
    // =========================================================================
    public function guardarNotaAJAX()
    {
        if (!$this->request->isAJAX()) {
            return "Prohibido";
        }

        $session = session();
        if ($session->get('nivel') != 5) {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'No tienes permisos de profesor.']);
        }

        $calificacionesModel = new CalificacionesBimestreModel();
        
        $db = \Config\Database::connect();
        $id_ciclo = $db->table('mesycicloactivo')->where('id', 1)->get()->getRow()->id_ciclo;

        $id_alumno  = $this->request->getPost('id_alumno');
        $id_grado   = $this->request->getPost('id_grado');
        $id_materia = $this->request->getPost('id_materia');
        $id_mes     = $this->request->getPost('id_mes');
        $valor      = $this->request->getPost('valor');

        $resultado = $calificacionesModel->guardarCalificacion(
            $id_alumno,
            $id_grado,
            $id_materia,
            $id_mes,
            $valor,
            $session->get('id'),  
            $id_ciclo
        );

        if ($resultado) {
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'msg' => 'Error en base de datos al guardar.']);
        }
    }
}