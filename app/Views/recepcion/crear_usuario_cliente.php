<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/registrar_cliente.css') ?>">

<h2>Registrar nuevo cliente</h2>

<?php
// Mensaje de éxito opcional
if ($ok = session()->getFlashdata('ok')): ?>
  <div class="alert alert-success"><?= esc($ok) ?></div>
<?php endif; ?>

<?php
// Mostrar errores vengan como 'errors' (array),
// o como 'validation' (array u objeto de Validation)
$errs = session()->getFlashdata('errors');
$val  = session()->getFlashdata('validation');

if (is_array($errs) && !empty($errs)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errs as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php elseif (is_array($val) && !empty($val)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($val as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php elseif (is_object($val) && method_exists($val, 'listErrors')): ?>
  <div class="alert alert-danger"><?= $val->listErrors() ?></div>
<?php endif; ?>

<form action="<?= base_url('recepcion/usuarios/guardar') ?>" method="post">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="nombre">Nombre completo</label>
        <input type="text" name="nombre" id="nombre" class="form-control"
               value="<?= old('nombre') ?>" required>
    </div>

    <div class="form-group">
        <label for="correo_electronico">Correo electrónico</label>
        <input type="email" name="correo_electronico" id="correo_electronico"
               class="form-control" value="<?= old('correo_electronico') ?>" required>
    </div>

    <div class="form-group">
        <label for="telefono">Teléfono</label>
        <input type="text" name="telefono" id="telefono"
               class="form-control" value="<?= old('telefono') ?>" required>
    </div>

    <div class="form-group">
        <label for="contrasena">Contraseña</label>
        <input type="password" name="contrasena" id="contrasena"
               class="form-control" required>
    </div>

    <div class="form-group">
        <label for="estado">Estado</label>
        <select name="estado" id="estado" class="form-control" required>
            <option value="1" <?= old('estado') === '1' ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= old('estado') === '0' ? 'selected' : '' ?>>Inactivo</option>
        </select>
    </div>

    <br>
    <button type="submit" class="btn btn-primary">Registrar cliente</button>
</form>

<!-- BOTÓN DE REGRESO -->
<div style="margin-top: 20px;">
    <a href="<?= base_url('recepcion/inicio') ?>" class="btn-volver" style="display: inline-block; padding: 10px 20px; background-color: #337ab7; color: white; text-decoration: none; border-radius: 5px;">⬅ Volver al inicio</a>
</div>

<?= $this->endSection() ?>
