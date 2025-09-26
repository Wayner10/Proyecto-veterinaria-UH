<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h1>Mis Mascotas</h1>

<?php if(empty($mascotas)): ?>
  <p>No tienes mascotas registradas.</p>
<?php else: ?>
  <table class="tabla">
    <thead><tr>
      <th>Nombre</th><th>Especie</th><th>Raza</th><th>Edad</th><th>Estado</th>
    </tr></thead>
    <tbody>
      <?php foreach($mascotas as $m): ?>
      <tr>
        <td><?= esc($m['nombre'] ?? '') ?></td>
        <td><?= esc($m['especie'] ?? '') ?></td>
        <td><?= esc($m['raza'] ?? '') ?></td>
        <td><?= esc($m['edad'] ?? '') ?></td>
        <td><?= (int)($m['estado'] ?? 1) === 1 ? 'Activa' : 'Inactiva' ?></td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
<?php endif ?>

<style>
.tabla{width:100%;border-collapse:collapse;background:#fff;border:1px solid #E5E7EB;border-radius:12px;overflow:hidden}
.tabla th,.tabla td{padding:12px 14px;border-bottom:1px solid #E5E7EB;text-align:left}
.tabla thead th{background:#F8FAFC;color:#64748B}
</style>

<?= $this->endSection() ?>
