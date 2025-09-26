<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/recepcionista/ver_factura.css') ?>">

<h2>Listado de Facturas</h2>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<table class="tabla-facturas">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Cliente</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($facturas as $factura): ?>
            <tr>
                <td><?= esc($factura->fecha) ?></td>
                <td><?= esc($factura->cliente_nombre) . ' ' . esc($factura->cliente_apellido) ?></td>
                <td>₡<?= number_format($factura->total, 2) ?></td>
                <td>
                    <?php if ($factura->estado == 1): ?>
                        <span class="badge badge-activa">Activa</span>
                    <?php else: ?>
                        <span class="badge badge-cancelada">Anulada</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?= base_url('recepcion/facturas/detalle/' . $factura->id_factura) ?>" class="btn-accion btn-detalle">
                        <i class="fa-solid fa-eye"></i> Ver
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="<?= base_url('recepcion/inicio') ?>" class="btn-volver">
    <i class="fa-solid fa-arrow-left"></i> Volver al inicio
</a>

<?= $this->endSection() ?>
