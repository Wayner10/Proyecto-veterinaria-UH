<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('css/veterinario/ver_citas.css') ?>">

<?php
$fmt = static function($value) {
    if (empty($value) || $value === '0000-00-00') return '—';
    $ts = strtotime($value);
    return $ts ? date('Y-m-d', $ts) : esc($value);
};
$tieneEstado = !empty($tratamientos) && array_key_exists('estado', $tratamientos[0] ?? []);
?>

<div class="citas-wrapper">
  <div class="citas-header">
    <h2>Historial de Tratamientos</h2>
  </div>

  <p class="subtexto">
    Mascota:
    <strong><?= esc($mascota['nombre'] ?? '—') ?></strong>
    (<?= esc($mascota['especie'] ?? '—') ?> - <?= esc($mascota['raza'] ?? '—') ?>)
  </p>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alerta"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="tabla-citas">
      <thead>
        <tr>
          <th>#</th>
          <th>Descripción</th>
          <th>Fecha inicio</th>
          <th>Fecha fin</th>
          <?php if ($tieneEstado): ?><th>Estado</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($tratamientos)): ?>
          <?php foreach ($tratamientos as $t): ?>
            <tr>
              <td><?= esc($t['id_tratamiento'] ?? '—') ?></td>
              <td><?= esc($t['descripcion'] ?? '—') ?></td>
              <td><?= $fmt($t['fecha_inicio'] ?? null) ?></td>
              <td><?= $fmt($t['fecha_fin'] ?? null) ?></td>
              <?php if ($tieneEstado): ?>
                <?php $activo = (int)($t['estado'] ?? 0) === 1; ?>
                <td>
                  <span class="badge <?= $activo ? 'badge-activa' : 'badge-cancelada' ?>">
                    <?= $activo ? 'Activo' : 'Inactivo' ?>
                  </span>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="<?= $tieneEstado ? 5 : 4 ?>">No hay tratamientos registrados para esta mascota.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="botones">
    <a href="<?= base_url('veterinario/pacientes') ?>" class="btn-volver">← Volver a pacientes</a>

    <?php if (!empty($id_cita_ult)): ?>
      <!-- Hay al menos una cita con este vet: crear tratamiento ligado a esa cita -->
      <a href="<?= base_url('veterinario/tratamientos/crear/' . (int)$id_cita_ult) ?>" class="btn-volver secondary">
        + Agregar Tratamiento
      </a>
    <?php else: ?>
      <!-- No hay cita: guía a la agenda -->
      <a href="<?= base_url('veterinario/citas') ?>" class="btn-volver secondary" title="Primero registra una cita para esta mascota">
        + Agregar Tratamiento (requiere cita)
      </a>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
