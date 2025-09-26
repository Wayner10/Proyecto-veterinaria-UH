<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h1>Mis Facturas</h1>

<?php if(empty($facturas)): ?>
  <p>No hay facturas registradas.</p>
<?php else: ?>
  <table class="tabla">
    <thead><tr>
      <th>#</th><th>Fecha</th><th>Estado</th><th>Total</th><th></th>
    </tr></thead>
    <tbody>
    <?php foreach($facturas as $f): ?>
      <tr>
        <td><?= esc($f['id_factura']) ?></td>
        <td><?= esc(substr($f['fecha'],0,10)) ?></td>
        <td><?= (int)$f['estado'] === 1 ? 'Activa' : 'Anulada' ?></td>
        <td><?= '₡'.number_format((float)$f['total'],2) ?></td>
        <td><a class="btn small" href="<?= base_url('cliente/facturas/'.$f['id_factura']) ?>">Ver</a></td>
      </tr>
    <?php endforeach ?>
    </tbody>
  </table>
  <div class="mt-3 text-center">
    <a href="<?= base_url('cliente/inicio') ?>" class="btn btn-secondary">
        ⬅ Volver al Inicio
    </a>
</div>
<?php endif ?>

<style>
.tabla{width:100%;border-collapse:collapse;background:#fff;border:1px solid #E5E7EB;border-radius:12px;overflow:hidden}
.tabla th,.tabla td{padding:12px 14px;border-bottom:1px solid #E5E7EB;text-align:left}
.tabla thead th{background:#F8FAFC;color:#64748B}
.btn.small{padding:8px 10px;border-radius:8px;background:#0EA5A4;color:#fff;text-decoration:none}
</style>

<?= $this->endSection() ?>
