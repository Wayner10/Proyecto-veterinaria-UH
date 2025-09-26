<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h2>Mi Perfil</h2>

<form action="<?= base_url('veterinario/perfil/actualizar') ?>" method="post">
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= esc($usuario->nombre) ?>" class="form-control">
    </div>
    <div class="form-group">
        <label>Correo</label>
        <input type="email" name="correo" value="<?= esc($usuario->correo) ?>" class="form-control">
    </div>
    <div class="form-group">
        <label>Teléfono</label>
        <input type="text" name="telefono" value="<?= esc($usuario->telefono) ?>" class="form-control">
    </div>
    <button type="submit" class="btn btn-success mt-3">Actualizar Perfil</button>
</form>

<?= $this->endSection() ?>
