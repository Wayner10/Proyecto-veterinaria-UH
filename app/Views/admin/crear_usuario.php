<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/admin/crear_usuario.css') ?>">

<h2>Crear Usuario</h2>

<form action="<?= base_url('admin/usuarios/guardar') ?>" method="post">
    <?= csrf_field() ?>

    <label>Nombre:</label><br>
    <input type="text" name="nombre" placeholder="Wayner Wilson" value="<?= old('nombre') ?>" required><br><br>

    <label>Correo electrónico:</label><br>
    <input type="email" name="correo_electronico" placeholder="correo@ejemplo.com" value="<?= old('correo_electronico') ?>" required><br><br>

    <label>Contraseña:</label><br>
    <input type="password" name="contrasena" placeholder="123456" required><br><br>

    <label>Rol</label>
    <select name="id_rol" id="id_rol" required>
        <?php $rolSel = old('id_rol', isset($usuario['id_rol']) ? $usuario['id_rol'] : ''); ?>
        <option value="" disabled <?= $rolSel === '' ? 'selected' : '' ?>>Seleccione un rol</option>
        <option value="1" <?= $rolSel == 1 ? 'selected' : '' ?>>Administrador</option>
        <option value="2" <?= $rolSel == 2 ? 'selected' : '' ?>>Veterinario</option>
        <option value="3" <?= $rolSel == 3 ? 'selected' : '' ?>>Recepcionista</option>
        <option value="4" <?= $rolSel == 4 ? 'selected' : '' ?>>Cliente</option>
    </select><br><br>

    <!-- Campo ESPECIALIDAD (solo para rol Veterinario) -->
    <div id="especialidad-group" style="display:none;">
        <label>Especialidad</label>
        <?php $espSel = old('especialidad'); ?>
        <select name="especialidad" id="especialidad">
            <option value="">Seleccione especialidad</option>
            <option value="Medicina general"   <?= $espSel==='Medicina general'?'selected':'' ?>>Medicina general</option>
            <option value="Cirugía"            <?= $espSel==='Cirugía'?'selected':'' ?>>Cirugía</option>
            <option value="Dermatología"       <?= $espSel==='Dermatología'?'selected':'' ?>>Dermatología</option>
            <option value="Odontología"        <?= $espSel==='Odontología'?'selected':'' ?>>Odontología</option>
            <option value="Oftalmología"       <?= $espSel==='Oftalmología'?'selected':'' ?>>Oftalmología</option>
            <option value="Anestesiología"     <?= $espSel==='Anestesiología'?'selected':'' ?>>Anestesiología</option>
            <option value="Medicina de exóticos" <?= $espSel==='Medicina de exóticos'?'selected':'' ?>>Medicina de exóticos</option>
            <option value="Fisioterapia"       <?= $espSel==='Fisioterapia'?'selected':'' ?>>Fisioterapia</option>
            <option value="Emergencias"        <?= $espSel==='Emergencias'?'selected':'' ?>>Emergencias</option>
        </select><br><br>
    </div>

    <label>Estado:</label><br>
    <select name="estado" required>
        <option value="1" <?= old('estado','1')==='1'?'selected':'' ?>>Activo</option>
        <option value="0" <?= old('estado')==='0'?'selected':'' ?>>Inactivo</option>
    </select><br><br>

    <button type="submit">Guardar</button>
</form>

<!-- Lógica para mostrar/ocultar especialidad según rol -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const rol = document.getElementById('id_rol');
  const group = document.getElementById('especialidad-group');
  const esp = document.getElementById('especialidad');

  function syncEspecialidad() {
    const isVet = rol && rol.value === '2';
    group.style.display = isVet ? '' : 'none';
    esp.disabled = !isVet;
    esp.required = isVet;
    if (!isVet) esp.value = '';
  }
  if (rol) {
    rol.addEventListener('change', syncEspecialidad);
    syncEspecialidad(); // estado inicial (soporta old() y edición)
  }
});
</script>

<?= $this->endSection() ?>
