<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="head">
  <h1>Mis Citas</h1>
  <a class="btn" href="<?= base_url('cliente/solicitar-cita') ?>">Solicitar cita</a>
</div>

<?php if(empty($citas)): ?>
  <p>No hay citas registradas.</p>
<?php else: ?>
  <table class="tabla">
    <thead><tr>
      <th>Fecha y hora</th><th>Mascota</th><th>Veterinario</th><th>Motivo</th><th>Estado</th>
    </tr></thead>
    <tbody>
    <?php foreach($citas as $c): ?>
      <tr>
        <td><?= esc($c->fecha_hora) ?></td>
        <td><?= esc($c->mascota) ?></td>
        <td><?= esc(trim(($c->veterinario ?? '').' '.($c->vet_apellido ?? ''))) ?></td>
        <td><?= esc($c->motivo) ?></td>
        <td><?= (int)$c->estado === 1 ? 'Programada' : ((int)$c->estado === 2 ? 'Atendida' : 'Cancelada') ?></td>
      </tr>
    <?php endforeach ?>
    </tbody>
  </table>
<?php endif ?>

<style>
.head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.btn{background:#0EA5A4;color:#fff;padding:10px 14px;border-radius:10px;text-decoration:none}
.tabla{width:100%;border-collapse:collapse;background:#fff;border:1px solid #E5E7EB;border-radius:12px;overflow:hidden}
.tabla th,.tabla td{padding:12px 14px;border-bottom:1px solid #E5E7EB;text-align:left}
.tabla thead th{background:#F8FAFC;color:#64748B}
</style>

<?= $this->endSection() ?>
