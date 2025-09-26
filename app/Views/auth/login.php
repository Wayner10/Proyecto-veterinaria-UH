<?php helper(['url', 'form']); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="<?= base_url('css/login2.css') ?>">
   <div class="login-container">
  <h2>Inicio de Sesión</h2>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error" role="alert" aria-live="assertive">
      <?= session()->getFlashdata('error') ?>
    </div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success" role="status" aria-live="polite">
      <?= session()->getFlashdata('success') ?>
    </div>
  <?php endif; ?>

  <?php if (isset($validation)): ?>
    <div class="alert alert-error" role="alert" aria-live="assertive">
      <?= $validation->listErrors() ?>
    </div>
  <?php endif; ?>

  <form method="post" action="<?= base_url('auth/login') ?>">
    <?= csrf_field() ?>

    <div class="form-group">
      <label for="correo">Correo electrónico:</label>
      <input type="email" name="correo_electronico" id="correo" required autofocus autocomplete="username">
    </div>

    <div class="form-group">
      <label for="contrasena">Contraseña:</label>
      <input type="password" name="contrasena" id="contrasena" required autocomplete="current-password">
    </div>

    <button type="submit" class="btn-primary">Ingresar</button>
  </form>
</div>

</body>
</html>
