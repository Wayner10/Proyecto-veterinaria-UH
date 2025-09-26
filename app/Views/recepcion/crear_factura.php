<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/recepcionista/crear_factura.css') ?>">

<h2>Registrar Nueva Factura</h2>

<?php $errors = session()->getFlashdata('errors'); ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert" aria-live="polite">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= base_url('recepcion/facturas/guardar') ?>" method="post" id="form-factura" novalidate>
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <label for="id_cliente">Cliente:</label>
            <select name="id_cliente" id="id_cliente" required>
                <option value="" disabled <?= old('id_cliente') ? '' : 'selected' ?>>Seleccione un cliente</option>
                <?php foreach ($clientes as $cliente): $val = (string)$cliente['id_cliente']; ?>
                    <option value="<?= esc($val) ?>" <?= old('id_cliente') == $val ? 'selected' : '' ?>>
                        <?= esc($cliente['nombre']) ?> <?= esc($cliente['apellido'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="fecha">Fecha:</label>
            <input type="date" name="fecha" id="fecha" value="<?= esc(old('fecha', date('Y-m-d'))) ?>" required>
        </div>

        <div>
            <label for="estado">Estado:</label>
            <select name="estado" id="estado" required>
                <?php $estadoSel = old('estado', '1'); ?>
                <option value="1" <?= $estadoSel === '1' ? 'selected' : '' ?>>Activa</option>
                <option value="0" <?= $estadoSel === '0' ? 'selected' : '' ?>>Anulada</option>
            </select>
        </div>
    </div>

    <hr class="my-4">

    <h3 class="mb-2">Detalles de la Factura</h3>
    <p class="text-muted">En cada fila, elija <strong>un</strong> producto <em>o</em> un servicio.</p>

    <table id="tabla-detalle" class="table table-sm">
        <thead>
            <tr>
                <th style="min-width: 220px;">Producto</th>
                <th style="min-width: 220px;">Servicio</th>
                <th style="width: 120px;">Cantidad</th>
                <th style="width: 160px;">Precio Unitario</th>
                <th style="width: 160px;">Subtotal</th>
                <th style="width: 80px;">Acción</th>
            </tr>
        </thead>
        <tbody id="detalle-body">
            <tr class="detalle-row">
                <td>
                    <select name="id_producto[]" class="producto">
                        <option value="">-- Seleccione producto --</option>
                        <?php foreach ($productos as $p): ?>
                            <option value="<?= esc($p['id_producto']) ?>" data-precio="<?= esc(number_format((float)$p['precio'], 2, '.', '')) ?>">
                                <?= esc($p['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="id_servicio[]" class="servicio">
                        <option value="">-- Seleccione servicio --</option>
                        <?php foreach ($servicios as $s): ?>
                            <option value="<?= esc($s['id_servicio']) ?>" data-precio="<?= esc(number_format((float)$s['precio'], 2, '.', '')) ?>">
                                <?= esc($s['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="number" name="cantidad[]" class="cantidad" inputmode="decimal" step="1" min="1" placeholder="1" required></td>
                <td><input type="number" name="precio_unitario[]" class="precio" inputmode="decimal" step="0.01" min="0" placeholder="0.00" required></td>
                <td><input type="text" name="subtotal[]" class="subtotal" value="0.00" readonly tabindex="-1"></td>
                <td><button type="button" class="btn-eliminar" aria-label="Eliminar fila">X</button></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">
                    <button type="button" id="agregar-detalle" class="btn btn-outline-primary mt-2">Agregar otro detalle</button>
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- ====== Resumen financiero ====== -->
    <div class="card mt-3" style="padding:12px;max-width:560px">
        <div class="row" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <label>Subtotal
                <input type="text" id="subtotal_visible" class="form-control" value="₡0.00" readonly>
            </label>

            <label>Descuento (%)
                <input type="number" step="0.01" min="0" max="100" name="descuento" id="descuento" value="<?= esc(old('descuento', '0.00')) ?>" class="form-control">
            </label>

            <label>IVA % 
                <input type="number" step="0.01" min="0" id="iva_porcentaje" value="13.00" class="form-control" readonly>
            </label>

            <label>IVA (₡)
                <input type="text" id="iva_visible" class="form-control" value="₡0.00" readonly>
            </label>

            <label>Total (₡)
                <input type="text" id="total_visible" class="form-control" value="<?= esc(number_format((float)old('total', 0), 2)) ?>" readonly>
            </label>
        </div>

        <!-- Hidden para backend -->
        <input type="hidden" name="iva" id="iva" value="0.00">
        <input type="hidden" name="total" id="total" value="<?= esc(number_format((float)old('total', 0), 2, '.', '')) ?>">
        <input type="hidden" name="subtotal_backend" id="subtotal_backend" value="0.00">
    </div>

    <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-success">Guardar Factura</button>
        <a href="<?= base_url('recepcion/inicio') ?>" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>
</form>

<!-- Plantilla de fila para clonar -->
<template id="row-template">
<tr class="detalle-row">
    <td>
        <select name="id_producto[]" class="producto">
            <option value="">-- Seleccione producto --</option>
            <?php foreach ($productos as $p): ?>
                <option value="<?= esc($p['id_producto']) ?>" data-precio="<?= esc(number_format((float)$p['precio'], 2, '.', '')) ?>">
                    <?= esc($p['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
    <td>
        <select name="id_servicio[]" class="servicio">
            <option value="">-- Seleccione servicio --</option>
            <?php foreach ($servicios as $s): ?>
                <option value="<?= esc($s['id_servicio']) ?>" data-precio="<?= esc(number_format((float)$s['precio'], 2, '.', '')) ?>">
                    <?= esc($s['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
    <td><input type="number" name="cantidad[]" class="cantidad" inputmode="decimal" step="1" min="1" placeholder="1" required></td>
    <td><input type="number" name="precio_unitario[]" class="precio" inputmode="decimal" step="0.01" min="0" placeholder="0.00" required></td>
    <td><input type="text" name="subtotal[]" class="subtotal" value="0.00" readonly tabindex="-1"></td>
    <td><button type="button" class="btn-eliminar" aria-label="Eliminar fila">X</button></td>
</tr>
</template>

<script defer src="<?= base_url('js/crear_factura.js?v=3') ?>"></script>


<?= $this->endSection() ?>
