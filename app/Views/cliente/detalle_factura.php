<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/recepcionista/detalle_facturas2.css') ?>">

<?php
$fmt = fn($n) => '₡' . number_format((float)$n, 2);
$fechaLegible = fn($ymd) => esc(substr((string)$ymd,0,10));
$subtotal = (float)$resumen['subtotal'];
$descuento = (float)$resumen['descuento'];
$iva = (float)$resumen['iva'];
$ivaPct = (float)$resumen['iva_porcentaje'];
$totalMostrar = (float)$resumen['total_bd'] ?: (float)$resumen['total_calc'];
?>

<div class="factura-wrapper" id="print-root">
  <header class="factura-header">
    <div>
      <h2 class="titulo">Factura #<?= esc($factura->id_factura) ?></h2>
      <p class="cliente"><strong>Fecha:</strong> <?= $fechaLegible($factura->fecha) ?></p>
    </div>
    <div class="header-right">
      <?php if ((int)$factura->estado === 1): ?>
        <span class="badge badge-activa">Activa</span>
      <?php else: ?>
        <span class="badge badge-cancelada">Anulada</span>
      <?php endif; ?>
      <div class="total-grande"><?= $fmt($totalMostrar) ?></div>
    </div>
  </header>

  <h3>Detalles</h3>

  <table class="tabla-detalle">
    <thead>
      <tr>
        <th>Descripción</th>
        <th style="width:120px">Cantidad</th>
        <th style="width:160px">Precio Unit.</th>
        <th style="width:160px">Subtotal</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($detalles as $d): ?>
      <tr>
        <td><?= esc($d->descripcion) ?></td>
        <td style="text-align:right"><?= esc($d->cantidad) ?></td>
        <td style="text-align:right"><?= $fmt($d->precio_unitario) ?></td>
        <td style="text-align:right"><?= $fmt($d->subtotal) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr><td colspan="3" class="tfoot-label">Subtotal</td><td class="tfoot-value"><?= $fmt($subtotal) ?></td></tr>
      <tr><td colspan="3" class="tfoot-label">Descuento (—)</td><td class="tfoot-value"><?= $fmt($descuento) ?></td></tr>
      <tr><td colspan="3" class="tfoot-label">IVA (<?= rtrim(rtrim(number_format($ivaPct,2),'0'),'.') ?> %)</td><td class="tfoot-value"><?= $fmt($iva) ?></td></tr>
      <tr><td colspan="3" class="tfoot-label tfoot-total">Total</td><td class="tfoot-value tfoot-total"><?= $fmt($totalMostrar) ?></td></tr>
    </tfoot>
  </table>

  <div class="botones no-print">
    <a href="<?= base_url('cliente/facturas') ?>" class="btn-volver">Volver</a>
    <!-- <button type="button" class="btn-volver secondary" onclick="window.print()">Imprimir</button> -->
    <a href="<?= base_url('cliente/facturas/descargar_pdf/' . $factura->id_factura) ?>" class="btn-volver">Exportar PDF</a>
  </div>
</div>

<?= $this->endSection() ?>
