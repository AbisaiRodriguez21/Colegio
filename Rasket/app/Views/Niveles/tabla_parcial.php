<?php 
// Obtenemos nivel del que mira la pantalla
$miNivel = session()->get('nivel'); 
?>

<div class="table-responsive">
    <table class="table table-bordered table-striped dt-responsive nowrap w-100 align-middle">
        <thead class="table-light">
            <tr>
                <th style="cursor: pointer;" onclick="cambiarOrden('nombre')" class="<?= $columna === 'nombre' ? 'text-primary' : '' ?>">
                    Nombre <?php if($columna === 'nombre'): ?><i class="bx bx-sort<?= $orden === 'ASC' ? '-up' : '-down' ?>"></i><?php endif; ?>
                </th>
                <th style="cursor: pointer;" onclick="cambiarOrden('email')" class="<?= $columna === 'email' ? 'text-primary' : '' ?>">
                    Email <?php if($columna === 'email'): ?><i class="bx bx-sort<?= $orden === 'ASC' ? '-up' : '-down' ?>"></i><?php endif; ?>
                </th>
                <th>Estatus</th>
                <th>Contraseña</th>
                <th style="cursor: pointer;" onclick="cambiarOrden('nivel')" class="<?= $columna === 'nivel' ? 'text-primary' : '' ?>">
                    Nivel <?php if($columna === 'nivel'): ?><i class="bx bx-sort<?= $orden === 'ASC' ? '-up' : '-down' ?>"></i><?php endif; ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($usuarios)): ?>
                <?php foreach ($usuarios as $usr): ?>
                    <tr>
                        <td>
                            <h5 class="font-size-14 mb-1"><?= esc($usr['Nombre']) ?> <?= esc($usr['ap_Alumno']) ?></h5>
                        </td>
                        <td><?= esc($usr['email']) ?></td>
                        <td><span class="badge badge-soft-success font-size-11">Activo</span></td>
                        
                        <td>
                            <?php 
                            // Solo mostramos input SI:
                            // 1. Yo soy Nivel 1 (Super Admin)
                            // 2. El usuario de la fila NO es Nivel 1 (Otro Super Admin)
                            if ($miNivel == 1 && $usr['nivel'] != 1): 
                            ?>
                                <input type="text" 
                                       class="form-control form-control-sm " 
                                       style="min-width: 120px;"
                                       value="<?= esc($usr['pass']) ?>" 
                                       data-original="<?= esc($usr['pass']) ?>"
                                       onblur="guardarPass(this, <?= $usr['id'] ?>)"
                                       onkeydown="if(event.key === 'Enter') this.blur()">
                            <?php else: ?>
                                <span class="badge bg-secondary">Oculta</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info font-size-12 p-2">
                                <?= esc($usr['nombre_rol'] ?? 'Sin Nivel') ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted py-5">No se encontraron registros</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="row align-items-center mt-3">
    <div class="col-sm-12 col-md-5">
        <p class="text-muted mb-0">Mostrando <?= $info['inicio'] ?> a <?= $info['fin'] ?> de <?= $info['total'] ?></p>
    </div>
    <div class="col-sm-12 col-md-7">
        <div class="d-flex justify-content-end"><?= $pager->links('default', 'bootstrap_ajax') ?></div>
    </div>
</div>