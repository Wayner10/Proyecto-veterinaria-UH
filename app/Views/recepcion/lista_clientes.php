<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/recepcionista/lista_recepcion_clientes.css') ?>">

<h2>Lista de clientes</h2>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<input type="text" id="filtroClientes" placeholder="Buscar cliente por nombre, correo o teléfono..." class="form-control" style="margin: 15px 0; max-width: 400px;">

<table class="table-clientes">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre completo</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Estado</th>
            <th>Acción</th>
        </tr>
    </thead>
    <tbody id="tablaClientes">
        <?php foreach ($clientes as $cliente): ?>
            <tr>
                <td><?= $cliente['id_cliente'] ?></td>
                <td><?= esc($cliente['nombre'] . ' ' . $cliente['apellido']) ?></td>
                <td><?= esc($cliente['telefono']) ?></td>
                <td><?= esc($cliente['correo_electronico']) ?></td>
                <td>
                    <?php if ($cliente['estado']): ?>
                        <span class="badge badge-success">Activo</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($cliente['estado']): ?>
                        <a href="<?= base_url('recepcion/clientes/desactivar/' . $cliente['id_cliente']) ?>" 
                           class="btn-accion btn-desactivar"
                           title="Desactivar cliente">
                           <i class="fa-solid fa-user-slash"></i> Desactivar
                        </a>
                    <?php else: ?>
                        <a href="<?= base_url('recepcion/clientes/activar/' . $cliente['id_cliente']) ?>" 
                           class="btn-accion btn-activar"
                           title="Activar cliente">
                           <i class="fa-solid fa-user-check"></i> Activar
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- BOTÓN DE REGRESO -->
<div style="margin-top: 20px;">
    <a href="<?= base_url('recepcion/inicio') ?>" class="btn-volver" style="display: inline-block; padding: 10px 20px; background-color: #337ab7; color: white; text-decoration: none; border-radius: 5px;">⬅ Volver al inicio</a>
</div>

<script src="<?= base_url('js/filtro_clientes.js') ?>"></script>


<?= $this->endSection() ?>
