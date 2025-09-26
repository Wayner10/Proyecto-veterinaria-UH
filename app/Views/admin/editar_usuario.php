<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/admin/editar_usuario.css') ?>">

<div class="form-wrapper">
    <h2>Editar Usuario</h2>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="msg msg-error"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php $validation = session('validation') ?? $validation ?? null; ?>

    <form action="<?= base_url('admin/usuarios/actualizar/' . $usuario['id_usuario']) ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= old('nombre', $usuario['nombre']) ?>" required>
            <?= $validation->showError('nombre', 'single') ?>
        </div>

        <div class="form-group">
            <label>Correo electrónico</label>
            <input type="email" name="correo_electronico" value="<?= old('correo_electronico', $usuario['correo_electronico']) ?>" required>
            <?= $validation->showError('correo_electronico', 'single') ?>
        </div>

        <div class="form-group">
            <label>Contraseña <span class="muted">(dejar en blanco para no cambiar)</span></label>
            <input type="password" name="contrasena">
            <?= $validation->showError('contrasena', 'single') ?>
        </div>

        <div class="form-group">
            <label>Rol</label>
            <?php $rolSel = old('id_rol', isset($usuario['id_rol']) ? (string)$usuario['id_rol'] : ''); ?>
            <select name="id_rol" id="id_rol" required>
                <option value="" disabled <?= $rolSel === '' ? 'selected' : '' ?>>Seleccione un rol</option>
                <option value="1" <?= $rolSel === '1' ? 'selected' : '' ?>>Administrador</option>
                <option value="2" <?= $rolSel === '2' ? 'selected' : '' ?>>Veterinario</option>
                <option value="3" <?= $rolSel === '3' ? 'selected' : '' ?>>Recepcionista</option>
                <option value="4" <?= $rolSel === '4' ? 'selected' : '' ?>>Cliente</option>
            </select>
            <?= $validation->showError('id_rol', 'single') ?>
        </div>

        <!-- ESPECIALIDAD: solo visible si Rol = 2 (Veterinario) -->
        <?php
            // Prefill seguro: usa old(), luego $usuario['especialidad'] si existe en la vista,
            // o $veterinario['especialidad'] si el controlador lo envía.
            $espSel = old('especialidad', $usuario['especialidad'] ?? ($veterinario['especialidad'] ?? ''));
        ?>
        <div class="form-group" id="especialidad-group" style="display:none;">
            <label>Especialidad</label>
            <select name="especialidad" id="especialidad">
                <option value="">Seleccione especialidad</option>
                <option value="Medicina general"     <?= $espSel==='Medicina general'?'selected':'' ?>>Medicina general</option>
                <option value="Cirugía"              <?= $espSel==='Cirugía'?'selected':'' ?>>Cirugía</option>
                <option value="Dermatología"         <?= $espSel==='Dermatología'?'selected':'' ?>>Dermatología</option>
                <option value="Odontología"          <?= $espSel==='Odontología'?'selected':'' ?>>Odontología</option>
                <option value="Oftalmología"         <?= $espSel==='Oftalmología'?'selected':'' ?>>Oftalmología</option>
                <option value="Anestesiología"       <?= $espSel==='Anestesiología'?'selected':'' ?>>Anestesiología</option>
                <option value="Medicina de exóticos" <?= $espSel==='Medicina de exóticos'?'selected':'' ?>>Medicina de exóticos</option>
                <option value="Fisioterapia"         <?= $espSel==='Fisioterapia'?'selected':'' ?>>Fisioterapia</option>
                <option value="Emergencias"          <?= $espSel==='Emergencias'?'selected':'' ?>>Emergencias</option>
            </select>
            <?= $validation->showError('especialidad', 'single') ?>
        </div>

        <div class="form-group">
            <label>Estado</label>
            <?php $estSel = old('estado', (string)$usuario['estado']); ?>
            <select name="estado" required>
                <option value="1" <?= $estSel === '1' ? 'selected' : '' ?>>Activo</option>
                <option value="0" <?= $estSel === '0' ? 'selected' : '' ?>>Inactivo</option>
            </select>
            <?= $validation->showError('estado', 'single') ?>
        </div>

        <button type="submit" class="btn-guardar">Actualizar</button>
        <a href="<?= base_url('admin/usuarios') ?>" class="btn-cancelar">Cancelar</a>
    </form>
</div>

<!-- Mostrar/ocultar Especialidad según Rol -->
<script>
document.addEventListener('DOMContentLoaded', function(){
  const rol   = document.getElementById('id_rol');
  const group = document.getElementById('especialidad-group');
  const esp   = document.getElementById('especialidad');

  function toggleEspecialidad(){
    const isVet = rol && rol.value === '2';
    group.style.display = isVet ? '' : 'none';
    if (esp) {
      esp.disabled = !isVet;
      esp.required = isVet;
      if (!isVet) esp.value = '';
    }
  }

  if (rol) {
    rol.addEventListener('change', toggleEspecialidad);
    toggleEspecialidad(); // estado inicial (respeta old() y datos cargados)
  }
});
</script>

<?= $this->endSection() ?>
