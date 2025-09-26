<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h1>Mi Perfil</h1>

<?php if(session('error')): ?><div class="alert err"><?= esc(session('error')) ?></div><?php endif ?>
<?php if(session('success')): ?><div class="alert ok"><?= esc(session('success')) ?></div><?php endif ?>

<form method="post" action="<?= base_url('cliente/actualizar-perfil') ?>">
  <?= csrf_field() ?>

  <label>Nombre</label>
  <input type="text" name="nombre" value="<?= esc($usuario['nombre'] ?? '') ?>" required>

  <label>Teléfono</label>
  <input type="text" name="telefono" value="<?= esc($cliente['telefono'] ?? '') ?>">

  <label>Nueva contraseña (opcional)</label>
  <input type="password" name="contrasena" placeholder="Mínimo 6 caracteres">

  <div class="acciones">
    <button class="btn" type="submit">Guardar cambios</button>
  </div>
</form>

<style>
form{background:#fff;border:1px solid #E5E7EB;border-radius:12px;padding:16px;max-width:520px}
label{display:block;margin-top:10px;font-weight:600}
input{width:100%;padding:10px;border:1px solid #E5E7EB;border-radius:8px;margin-top:6px}
.acciones{display:flex;justify-content:flex-end;margin-top:14px}
.btn{background:#0EA5A4;color:#fff;padding:10px 14px;border-radius:10px;border:0;cursor:pointer}
.alert{padding:10px;border-radius:8px;margin-bottom:10px}
.alert.ok{background:#D1FAE5;color:#065F46}
.alert.err{background:#FEE2E2;color:#7F1D1D}
</style>

<?= $this->endSection() ?>
