<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/recepcionista/recepcion_inicio.css') ?>">


<h1>Te damos una cálida bienvenida, <?= esc(session('nombre') ?? 'Recepcionista') ?></h1>

<!-- ✅ MENSAJE DE ÉXITO -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<!-- ✅ MENSAJE DE ERROR -->
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div id="botones-recepcion">

    <!-- Citas -->
    <a href="<?= base_url('recepcion/citas/crear') ?>" class="btn-dashboard btn-citas">
        <i class="fa-solid fa-plus"></i> Registrar nueva cita
    </a>

    <a href="<?= base_url('recepcion/citas') ?>" class="btn-dashboard btn-citas">
        <i class="fa-solid fa-calendar-days"></i> Ver citas programadas
    </a>

    <!-- Clientes -->
    <a href="<?= base_url('recepcion/usuarios/crear') ?>" class="btn-dashboard btn-clientes">
        <i class="fa-solid fa-user-plus"></i> Registrar cliente
    </a>

    <a href="<?= base_url('recepcion/clientes') ?>" class="btn-dashboard btn-clientes">
        <i class="fa-solid fa-users"></i> Ver lista de clientes
    </a>

    <!-- Mascotas -->
    <a href="<?= base_url('recepcion/mascotas/crear') ?>" class="btn-dashboard btn-mascotas">
        <i class="fa-solid fa-dog"></i> Registrar nueva mascota
    </a>

    <a href="<?= base_url('recepcion/mascotas') ?>" class="btn-dashboard btn-mascotas">
        <i class="fa-solid fa-paw"></i> Ver mascotas registradas
    </a>

    <!-- Facturas -->
    <a href="<?= base_url('recepcion/facturas/crear') ?>" class="btn-dashboard btn-facturas">
        <i class="fa-solid fa-file-invoice-dollar"></i> Crear nueva factura
    </a>

    <a href="<?= base_url('recepcion/facturas') ?>" class="btn-dashboard btn-facturas">
        <i class="fa-solid fa-file-invoice"></i> Ver facturas emitidas
    </a>

</div>

<!-- 🔢 TARJETAS ESTADÍSTICAS -->
<div class="dashboard-stats">
    <div class="card-stat blue">
        <i class="fa-solid fa-calendar-check"></i>
        <div>
            <h3><?= $citasHoy ?? '0' ?></h3>
            <p>Citas de hoy</p>
        </div>
    </div>

    <div class="card-stat green">
        <i class="fa-solid fa-users"></i>
        <div>
            <h3><?= $clientes ?? '0' ?></h3>
            <p>Clientes registrados</p>
        </div>
    </div>

    <div class="card-stat yellow">
        <i class="fa-solid fa-dog"></i>
        <div>
            <h3><?= $mascotas ?? '0' ?></h3>
            <p>Mascotas registradas</p>
        </div>
    </div>

    <div class="card-stat teal">
        <i class="fa-solid fa-file-invoice-dollar"></i>
        <div>
            <h3><?= $facturasHoy ?? '0' ?></h3>
            <p>Facturas de hoy</p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
