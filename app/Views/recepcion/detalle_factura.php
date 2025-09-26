<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/recepcionista/detalle_facturas2.css') ?>">


<?php
    // ——— Helpers ———
    $fmt = fn($n) => '₡' . number_format((float)$n, 2);
    $fechaLegible = function($ymd) { $f = substr((string)$ymd, 0, 10); return esc($f); };

    // ——— Cálculos base desde los detalles ———
    $subtotal = 0.0;
    if (!empty($detalles)) {
        foreach ($detalles as $d) { $subtotal += (float)($d->subtotal ?? 0); }
    }
    $subtotal = round($subtotal, 2);

    // ——— Descuento e IVA (valores flexibles) ———
    // Si en tu BD existen estos campos en facturas, se usan; si no, quedan por defecto.
    $descuento = isset($factura->descuento) ? (float)$factura->descuento : 0.0;

    // IVA: si viene guardado en factura lo usamos; si no, calculamos 13% del (subtotal - descuento)
    $ivaGuardado = isset($factura->iva) ? (float)$factura->iva : null;
    $ivaPorcentaje = 13.0; // cámbialo si tu IVA difiere
    $baseImponible = max($subtotal - $descuento, 0.0);
    $ivaCalculado = round($baseImponible * ($ivaPorcentaje / 100), 2);
    $iva = $ivaGuardado !== null ? $ivaGuardado : $ivaCalculado;

    // Total mostrado: si hay total en factura se respeta, si no se arma desde el cálculo
    $totalCalc = round($baseImponible + $iva, 2);
    $totalFacturaCampo = isset($factura->total) ? (float)$factura->total : null;
    $totalMostrar = $totalFacturaCampo !== null ? (float)$totalFacturaCampo : $totalCalc;

    // Coincidencia para advertencia
    $coincide = abs($totalMostrar - $totalCalc) < 0.005;

    // Utilidad: descripción única (producto o servicio)
    $descDe = function($d) { return $d->producto ?? $d->servicio ?? '-'; };
?>

<div id="print-root" class="factura-wrapper">
    <header class="factura-header">
        <div>
            <h2 class="titulo">Factura #<?= esc($factura->id_factura) ?></h2>
            <p class="cliente"><strong>Cliente:</strong>
                <?= esc(trim(($factura->cliente_nombre ?? '').' '.($factura->cliente_apellido ?? ''))) ?>
            </p>
            <p><strong>Fecha:</strong> <?= $fechaLegible($factura->fecha) ?></p>
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

    <hr>

    <h3>Detalles</h3>

    <table class="tabla-detalle">
        <thead>
            <tr>
                <th style="min-width:260px;">Descripción</th>
                <th style="width:120px;">Cantidad</th>
                <th style="width:160px;">Precio Unit.</th>
                <th style="width:160px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($detalles)): ?>
                <?php foreach ($detalles as $d): ?>
                    <tr>
                        <td><?= esc($descDe($d)) ?></td>
                        <td><?= esc((string)$d->cantidad) ?></td>
                        <td><?= $fmt($d->precio_unitario) ?></td>
                        <td><?= $fmt($d->subtotal) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No hay detalles disponibles para esta factura.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="tfoot-label">Subtotal</td>
                <td class="tfoot-value"><?= $fmt($subtotal) ?></td>
            </tr>
            <tr>
                <td colspan="3" class="tfoot-label">
                    Descuento <?= $descuento > 0 ? '' : '(—)' ?>
                </td>
                <td class="tfoot-value"><?= $fmt($descuento) ?></td>
            </tr>
            <tr>
                <td colspan="3" class="tfoot-label">
                    IVA (<?= rtrim(rtrim(number_format($ivaPorcentaje, 2), '0'), '.') ?> %)
                </td>
                <td class="tfoot-value"><?= $fmt($iva) ?></td>
            </tr>
            <tr>
                <td colspan="3" class="tfoot-label tfoot-total">Total</td>
                <td class="tfoot-value tfoot-total"><?= $fmt($totalMostrar) ?></td>
            </tr>
            <?php if (!$coincide): ?>
                
            <?php endif; ?>
        </tfoot>
    </table>

    <div class="botones no-print">
        <a href="<?= base_url('recepcion/facturas') ?>" class="btn-volver">
            <i class="fa-solid fa-arrow-left"></i> Volver a la lista
        </a>
        <!-- <button type="button" class="btn-volver" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Imprimir
        </button> -->
        <a href="<?= base_url('recepcion/facturas/descargar_pdf/' . $factura->id_factura) ?>" class="btn-volver">
            <i class="fa-solid fa-file-pdf"></i> Exportar PDF
        </a>
    </div>
</div>

<?= $this->endSection() ?>
