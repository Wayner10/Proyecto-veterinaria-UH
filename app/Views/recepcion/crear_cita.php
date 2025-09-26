<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/recepcionista/crear_cita.css') ?>">

<h2>Registrar Nueva Cita</h2>

<?php if (session()->getFlashdata('error')): ?>
    <div class="error"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form action="<?= base_url('recepcion/citas/guardar') ?>" method="post">
    <?= csrf_field() ?>

    <!-- MASCOTA -->
    <div class="form-group">
        <label for="id_mascota">Mascota:</label>
        <select name="id_mascota" id="id_mascota" required>
            <option value="">Seleccione una mascota</option>
            <?php foreach ($mascotas as $m): ?>
                <option value="<?= esc($m->id_mascota) ?>">
                    <?= esc($m->nombre) ?> (<?= esc($m->especie) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- VETERINARIO (solo visible para admin o recepción) -->
    <?php if (session()->get('id_rol') == 1 || session()->get('id_rol') == 3): ?> 
    <div class="form-group">
        <label for="id_veterinario">Veterinario:</label>
        <select name="id_veterinario" id="id_veterinario" required>
            <option value="">-- Seleccione un veterinario --</option>
            <?php foreach ($veterinarios as $v): ?>
                <option value="<?= esc($v->id_veterinario) ?>">
                    <?= esc($v->nombre) ?> <?= esc($v->apellido) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php else: ?>
        <input type="hidden" name="id_veterinario" value="<?= session()->get('id_usuario') ?>">
    <?php endif; ?>

    <!-- FECHA Y HORA -->
    <div class="form-group">
        <label for="fecha_hora">Fecha y Hora:</label>
        <input type="datetime-local" name="fecha_hora" id="fecha_hora" required>
    </div>

    <!-- MOTIVO -->
    <div class="form-group">
        <label for="motivo">Motivo:</label>
        <textarea name="motivo" id="motivo" rows="3" required><?= old('motivo') ?></textarea>
    </div>

    <!-- ESTADO -->
    <input type="hidden" name="estado" value="1">

    <!-- BOTONES -->
    <div class="form-group">
        <button type="submit">Guardar Cita</button>
        <a href="<?= base_url('recepcion/inicio') ?>" class="btn-cancelar">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>
