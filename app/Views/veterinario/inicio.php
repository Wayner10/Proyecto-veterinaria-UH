<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/veterinario/inicioCitas.css') ?>">

<div class="panel-wrapper">
    <header class="panel-header">
        <div>
            <h1 class="panel-title">Panel del Veterinario</h1>
            <p class="panel-subtitle">Accesos rápidos y resumen de tu agenda</p>
        </div>
        <div class="panel-date">
            <?= esc(date('Y-m-d')) ?>
        </div>
    </header>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alerta"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alerta advertencia"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <!-- KPIs -->
    <section class="kpis-grid">
        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">Citas de hoy</span>
                <span class="kpi-dot kpi-green"></span>
            </div>
            <div class="kpi-value"><?= esc((int) ($citasHoy ?? 0)) ?></div>
            <div class="kpi-hint">Rango: hoy 00:00–23:59</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-top">
                <span class="kpi-label">Pendientes</span>
                <span class="kpi-dot kpi-blue"></span>
            </div>
            <div class="kpi-value"><?= esc((int) ($pendientes ?? 0)) ?></div>
            <div class="kpi-hint">Desde hoy en adelante</div>
        </div>
    </section>

    <!-- Acciones rápidas -->
    <section class="quick-actions">
        <a href="<?= base_url('veterinario/citas') ?>" class="btn-lg">
            <i class="fa-solid fa-calendar-days"></i>
            <span>Ver Citas</span>
        </a>

        <a href="<?= base_url('veterinario/pacientes') ?>" class="btn-lg secondary">
            <i class="fa-solid fa-paw"></i>
            <span>Pacientes</span>
        </a>

        <a href="<?= base_url('auth/logout') ?>" class="btn-lg danger">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Cerrar Sesión</span>
        </a>
    </section>
</div>

<?= $this->endSection() ?>