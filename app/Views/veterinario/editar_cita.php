<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h2>Editar Cita</h2>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form action="<?= base_url('veterinario/citas/actualizar/' . $cita['id_cita']) ?>" method="post">
    <div class="form-group">
        <label for="fecha">Fecha:</label>
        <input type="date" name="fecha" id="fecha" class="form-control"
               value="<?= esc(date('Y-m-d', strtotime($cita['fecha_hora']))) ?>" required>
    </div>

    <div class="form-group">
        <label for="hora">Hora:</label>
        <input type="time" name="hora" id="hora" class="form-control"
               value="<?= esc(date('H:i', strtotime($cita['fecha_hora']))) ?>" required>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
    <a href="<?= base_url('veterinario/citas') ?>" class="btn btn-secondary mt-3">Cancelar</a>
</form>

<?= $this->endSection() ?>
