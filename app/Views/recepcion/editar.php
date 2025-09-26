<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h2>Editar Cita</h2>

<?php if (session()->getFlashdata('error')): ?>
    <div class="error"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form action="<?= base_url('recepcion/citas/actualizar/' . $cita->id_cita) ?>" method="post">
    <?= csrf_field() ?>

    <!-- Mascota -->
    <div class="form-group">
        <label for="id_mascota">Mascota:</label>
        <select name="id_mascota" id="id_mascota" required>
            <option value="">Seleccione una mascota</option>
            <?php foreach ($mascotas as $m): ?>
                <option value="<?= esc($m->id_mascota) ?>" <?= $m->id_mascota == $cita->id_mascota ? 'selected' : '' ?>>
                    <?= esc($m->nombre) ?> (<?= esc($m->especie) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Veterinario -->
    <div class="form-group">
        <label for="id_veterinario">Veterinario:</label>
        <select name="id_veterinario" id="id_veterinario" required>
            <option value="">Seleccione un veterinario</option>
            <?php foreach ($veterinarios as $v): ?>
                <option value="<?= esc($v->id_veterinario) ?>" <?= $v->id_veterinario == $cita->id_veterinario ? 'selected' : '' ?>>
                    <?= esc($v->nombre) ?> <?= esc($v->apellido) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Fecha y Hora -->
    <div class="form-group">
        <label for="fecha_hora">Fecha y Hora:</label>
        <?php
            $valor_fecha = date('Y-m-d\TH:i', strtotime($cita->fecha_hora));
        ?>
        <input type="datetime-local" name="fecha_hora" id="fecha_hora" value="<?= esc($valor_fecha) ?>" required>
    </div>

    <!-- Motivo -->
    <div class="form-group">
        <label for="motivo">Motivo:</label>
        <textarea name="motivo" id="motivo" rows="3" required><?= esc($cita->motivo) ?></textarea>
    </div>

    <!-- Botones -->
    <div class="form-group">
        <button type="submit">Actualizar Cita</button>
        <a href="<?= base_url('recepcion/citas') ?>" class="btn-cancelar">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>
