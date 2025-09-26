<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Factura #<?= esc($factura->id_factura) ?></title>
<style>
/* ===== Página / tipografía ===== */
@page { margin: 24mm 18mm 20mm 18mm; }
* { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
body { font-family: DejaVu Sans, sans-serif; font-size: 11.5px; color:#0F172A; }

/* ===== Titulares / utilitarios ===== */
h1 { font-size: 18px; margin: 0 0 6px; color:#0F172A; }
h2 { font-size: 14px; margin: 16px 0 6px; color:#0F172A; }
.muted { color:#64748B; }
.brand { font-weight: 700; font-size: 12px; color:#0F766E; } /* brand-700 */
.logo { height:38px; vertical-align:middle; }

/* ===== Layout (floats para compatibilidad PDF) ===== */
.row { width:100%; }
.left  { float:left;  width:58%; }
.right { float:right; width:40%; text-align:right; }
.clearfix { clear:both; }

/* ===== Cajas y separadores ===== */
.box { border:1px solid #E5E7EB; padding:10px 12px; border-radius:4px; display:inline-block; }
hr { border:0; border-top:1px solid #E5E7EB; margin:10px 0; }

/* ===== Tabla de ítems ===== */
table { width:100%; border-collapse: collapse; table-layout: fixed; }
th, td { border:1px solid #E5E7EB; padding:6px 8px; vertical-align: top; }
th { background:#E6FFFA; color:#0F172A; font-weight:700; }      /* head con tinte teal */
tbody tr:nth-child(even) td { background:#F8FAFC; }             /* zebra suave */

.text-right { text-align:right; }
.w-desc { width:54%; word-wrap:break-word; }
.w-qty  { width:10%; }
.w-price, .w-sub { width:18%; }

/* ===== Totales ===== */
.totals { margin-top:8px; width:100%; border-collapse:collapse; }
.totals td { padding:6px 8px; }
.totals .label { text-align:right; border:none; color:#64748B; }
.totals .val   { text-align:right; border:none; width:22%; color:#0F172A; }
.grand { font-weight:700; font-size:12.5px; border-top:2px solid #0EA5A4; background:#E6FFFA; }

/* ===== Notas y advertencias ===== */
.note { margin-top:14px; font-size:10.5px; color:#52525B; }
.warn { color:#B91C1C; font-size:10.5px; margin-top:6px; }

/* ===== Badges de estado (impresión) ===== */
.badge{
  display:inline-block; padding:4px 10px; border-radius:999px;
  font-weight:700; font-size:11px; border:1px solid transparent;
}
.badge-activa{
  background:#DCFCE7; color:#166534; border-color:#86EFAC;   /* verde soft */
}
.badge-cancelada{
  background:#FEE2E2; color:#7F1D1D; border-color:#FCA5A5;   /* rojo soft */
}

/* ===== Encabezado de factura (opcional bonito) ===== */
.fact-header{
  padding:8px 0 12px;
  border-bottom:2px solid #0EA5A4;
  margin-bottom:8px;
}
.header-meta { font-size:11px; color:#64748B; }

/* ===== Bloques info (cliente / emisor) ===== */
.info-block {
  border:1px solid #E5E7EB; border-radius:4px; padding:10px 12px; margin-top:8px;
}
.info-title { font-weight:700; color:#0F172A; margin-bottom:6px; }

/* ===== Ajustes finos para números ===== */
.nowrap { white-space:nowrap; }
.currency { font-variant-numeric: tabular-nums; letter-spacing: .1px; }

/* ===== Evitar cortes raros en impresión ===== */
tr, td, th { page-break-inside: avoid; }

/* ===== LOGO ===== */
.logo { 
  height:100px; /* o el valor que desees */
  width:auto;  /* mantiene proporción */
  vertical-align:middle; 
}
</style>

</head>
<body>

  <!-- Encabezado -->
  <div class="row">
    <div class="left">
      <?php if (!empty($logoBase64)): ?>
        <img class="logo" src="data:image/png;base64,<?= $logoBase64 ?>" alt="Logo">
      <?php endif; ?>
      
      <div class="brand"><?= esc($empresa['nombre'] ?? 'Clínica Veterinaria') ?></div>
      <div class="muted">
        <?= esc($empresa['direccion'] ?? '') ?><br>
        <?php if (!empty($empresa['telefono'])): ?>Tel: <?= esc($empresa['telefono']) ?><?php endif; ?>
        <?php if (!empty($empresa['telefono']) && !empty($empresa['correo'])): ?> · <?php endif; ?>
        <?php if (!empty($empresa['correo'])): ?><?= esc($empresa['correo']) ?><?php endif; ?>
      </div>
    </div>

    <div class="right">
      <div class="box">
        <h1>Factura #<?= esc($factura->id_factura) ?></h1>
        <div><strong>Fecha:</strong> <?= esc($factura->fecha ?? date('Y-m-d')) ?></div>
        <div><strong>Cliente:</strong>
          <?= esc(trim(($factura->cliente_nombre ?? '').' '.($factura->cliente_apellido ?? ''))) ?>
        </div>
      </div>
    </div>
  </div>
  <div class="clearfix"></div>

  <!-- Detalle -->
  <h2>Detalle</h2>
  <table>
    <thead>
      <tr>
        <th class="w-desc">Descripción</th>
        <th class="w-qty text-right">Cantidad</th>
        <th class="w-price text-right">Precio Unit.</th>
        <th class="w-sub text-right">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php
        // Formateador CRC local
        $crc = fn($n) => '₡'.number_format((float)$n, 2, ',', '.');
        foreach ($detalles as $it):
          $cant = (float)($it->cantidad ?? 0);
          $pu   = (float)($it->precio_unitario ?? 0);
          $sub  = isset($it->subtotal) ? (float)$it->subtotal : $cant * $pu;
      ?>
      <tr>
        <td class="w-desc"><?= esc($it->descripcion) ?></td>
        <td class="w-qty text-right"><?= number_format($cant, 0, ',', '.') ?></td>
        <td class="w-price text-right"><?= $crc($pu) ?></td>
        <td class="w-sub text-right"><?= $crc($sub) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Totales -->
  <?php
    // Valores base desde el backend, con fallback robusto
    $sb       = (float)($resumen['subtotal'] ?? 0.0);
    $dsMonto  = (float)($resumen['descuento'] ?? 0.0);  // MONTO
    if ($dsMonto < 0) $dsMonto = 0.0;
    if ($dsMonto > $sb) $dsMonto = $sb;

    // Porcentaje de descuento: usa el que viene o infiere
    $dsPct = isset($resumen['descuento_pct'])
      ? (float)$resumen['descuento_pct']
      : ($sb > 0 ? round(($dsMonto / $sb) * 100, 2) : 0.0);

    // Clamp por prolijidad
    if ($dsPct < 0) $dsPct = 0.0;
    if ($dsPct > 100) $dsPct = 100.0;

    $porcIva  = isset($resumen['iva_porcentaje']) ? (float)$resumen['iva_porcentaje'] : 13.0;
    $exento   = !empty($resumen['exento']);
    $baseImp  = max($sb - $dsMonto, 0.0);

    // IVA: usa el pasado o calcula
    $iv       = isset($resumen['iva']) ? (float)$resumen['iva'] : ($exento ? 0.0 : round($baseImp * ($porcIva / 100), 2));
    if (!isset($resumen['iva']) && !$exento && abs($iv) < 0.005) {
      $iv = round($baseImp * ($porcIva / 100), 2);
    }

    // Total calculado (o fallback)
    $tt       = isset($resumen['total_calc']) ? (float)$resumen['total_calc'] : round($baseImp + $iv, 2);

    // Comparación opcional con BD
    $tbd      = isset($resumen['total_bd']) ? (float)$resumen['total_bd'] : null;
    $coincide = isset($resumen['coincide']) ? (bool)$resumen['coincide'] : true;
  ?>
  <table class="totals">
    <tr>
      <td class="label" colspan="3"><strong>Subtotal</strong></td>
      <td class="val"><?= $crc($sb) ?></td>
    </tr>
    <tr>
      <td class="label" colspan="3">
        Descuento (<?= number_format($dsPct, 2, ',', '.') ?>%)
      </td>
      <td class="val">-<?= $crc($dsMonto) ?></td>
    </tr>
    <tr>
      <td class="label" colspan="3">
        <?= $exento ? 'IVA (exento)' : 'IVA ('.esc(number_format($porcIva, 0)).'%)' ?>
      </td>
      <td class="val"><?= $crc($iv) ?></td>
    </tr>
    <tr class="grand">
      <td class="label" colspan="3">Total</td>
      <td class="val"><?= $crc($tt) ?></td>
    </tr>
  </table>

  <!-- <?php if ($tbd !== null && !$coincide): ?>
    <div class="warn">
      Aviso: el total calculado (<?= $crc($tt) ?>) no coincide con el total guardado en BD (<?= $crc($tbd) ?>).
    </div>
  <?php endif; ?> -->

  <div class="note">* Precios en CRC. Documento generado electrónicamente.</div>

  <!-- Numeración de página Dompdf -->
  <script type="text/php">
    if (isset($pdf)) {
      $pdf->page_text(515, 820, "Página {PAGE_NUM} de {PAGE_COUNT}", "DejaVu Sans", 9, array(0,0,0));
    }
  </script>
</body>
</html>
