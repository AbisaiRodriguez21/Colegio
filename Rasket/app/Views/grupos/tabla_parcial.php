<div class="table-responsive">
    <table class="table table-striped table-hover dt-responsive nowrap w-100">
        <thead class="table-light">
            <tr>
                <th>Matrícula</th>
                <th>Nombre del Alumno</th>
                <th>Grado (ID)</th>
                <th>Activo</th>
                <th class="text-center" style="width: 120px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($alumnos)): ?>
                <?php foreach ($alumnos as $alumno): ?>
                    <tr>
                        <td>
                            <strong><?= esc($alumno['matricula']) ?></strong>
                        </td>
                        <td>
                            <?= esc($alumno['Nombre']) . ' ' . esc($alumno['ap_Alumno']) . ' ' . esc($alumno['am_Alumno']) ?>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary">
                                <!-- Muestra el Nombre si existe, si no, muestra el ID numérico y el fallo de JOIN -->
                                <?= esc($alumno['nombreGrado'] ?? 'ID Fallido: ' . $alumno['grado_id']) ?>
                            </span>
                        </td>
                        <td>
                            <!-- Muestra el estado activo/inactivo -->
                            <?php if ($alumno['estado_activo'] === '1' || $alumno['estado_activo'] === 1): ?>
                                <span class="badge bg-success">Activo (1)</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactivo/Null (<?= esc($alumno['estado_activo'] ?? 'NULL') ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <a href="<?= base_url('alumnos/materias/' . $alumno['id']) ?>" 
                               class="btn btn-sm btn-info" 
                               data-bs-toggle="tooltip" 
                               title="Ver Materias">
                               <i class="ri-list-check"></i> Ver Materias
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">
                        <i class="ri-search-line fs-24"></i> <br>
                        No se encontraron alumnos en este grado.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>