<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/recepcionista/ver_citas.css') ?>">

<h2>Listado de Citas Programadas</h2>

<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Mascota</th>
            <th>Cliente</th>
            <th>Veterinario</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($citas as $cita): ?>
            <tr>
                <?php
                    $fechaHora = new \DateTime($cita->fecha_hora);
                    $fecha = $fechaHora->format('Y-m-d');
                    $hora  = $fechaHora->format('H:i');
                ?>
                <td><?= esc($fecha) ?></td>
                <td><?= esc($hora) ?></td>
                <td><?= esc($cita->mascota ?? '') ?></td>
                <td><?= esc($cita->cliente_nombre ?? '') . ' ' . esc($cita->cliente_apellido ?? '') ?></td>
                <td><?= esc($cita->veterinario ?? '') ?></td>
                <td>
                    <?php if ($cita->estado == 1): ?>
                        <span class="badge badge-activa">Activa</span>
                    <?php else: ?>
                        <span class="badge badge-cancelada">Cancelada</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?= base_url('recepcion/citas/editar/' . $cita->id_cita) ?>" class="btn-accion btn-editar">
                        <i class="fa-solid fa-file-pen"></i> Editar
                    </a>
                    <?php if ($cita->estado == 1): ?>
                        <a href="<?= base_url('recepcion/citas/cancelar/' . $cita->id_cita) ?>" class="btn-accion btn-cancelar" onclick="return confirm('¿Estás seguro de cancelar esta cita?')">
                           <i class="fa-solid fa-x"></i> Cancelar
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Botón de regreso -->
<a href="<?= base_url('recepcion/inicio') ?>" class="btn-volver"><i class="fa-solid fa-arrow-left"></i> Volver al inicio</a>


<?= $this->endSection() ?> 