<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/recepcionista/lista_mascotas.css') ?>">

<h2>Lista de Mascotas</h2>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<table class="table-mascotas">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Especie</th>
            <th>Raza</th>
            <th>Edad</th>
            <th>Peso</th>
            <th>Cliente</th>
            <th>Estado</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($mascotas as $mascota): ?>
            <tr>
                <td><?= $mascota->id_mascota ?></td>
                <td><?= esc($mascota->nombre) ?></td>
                <td><?= esc($mascota->especie) ?></td>
                <td><?= esc($mascota->raza) ?></td>
                <td><?= esc($mascota->edad) ?> años</td>
                <td><?= esc($mascota->peso) ?> kg</td>
                <td><?= esc($mascota->cliente_nombre . ' ' . $mascota->cliente_apellido) ?></td>
                <td>
                    <?php if ($mascota->estado): ?>
                        <span class="badge badge-success">Activo</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($mascota->estado): ?>
                        <a href="<?= base_url('recepcion/mascotas/desactivar/' . $mascota->id_mascota) ?>" class="btn-accion btn-desactivar">
                            <i class="fa-solid fa-user-slash"></i> Desactivar
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('recepcion/mascotas/activar/' . $mascota->id_mascota) ?>" class="btn-accion btn-activar">
                            <i class="fa-solid fa-user-check"></i> Activar
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<a href="<?= site_url('recepcion/mascotas/crear') ?>" class="btn-agregar">
    <i class="fa-solid fa-plus"></i> nueva mascota
</a>

<br><br>

<a href="<?= site_url('recepcion/inicio') ?>" class="btn-volver">
    <i class="fa-solid fa-arrow-left"></i> Volver al inicio
</a>


<?= $this->endSection() ?>
