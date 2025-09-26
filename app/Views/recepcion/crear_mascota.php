<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/recepcionista/crear_mascotas.css') ?>">

<h2>Registrar Nueva Mascota</h2>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<form action="<?= base_url('recepcion/mascotas/guardar') ?>" method="post">
    <?= csrf_field() ?>

    <div>
        <label for="nombre">Nombre de la mascota:</label>
        <input type="text" name="nombre" id="nombre" value="<?= old('nombre') ?>" required>
    </div>

    <div>
        <label for="especie">Especie:</label>
        <select name="especie" id="especie" required>
            <option value="">Seleccione una especie</option>
            <option value="Perro" <?= old('especie') === 'Perro' ? 'selected' : '' ?>>Perro</option>
            <option value="Gato" <?= old('especie') === 'Gato' ? 'selected' : '' ?>>Gato</option>
            <option value="Ave" <?= old('especie') === 'Ave' ? 'selected' : '' ?>>Ave</option>
            <option value="Conejo" <?= old('especie') === 'Conejo' ? 'selected' : '' ?>>Conejo</option>
            <option value="Otro" <?= old('especie') === 'Otro' ? 'selected' : '' ?>>Otro</option>
        </select>
    </div>

    <div>
        <label for="raza">Raza:</label>
        <input type="text" name="raza" id="raza" value="<?= old('raza') ?>" required>
    </div>

    <div>
        <label for="edad">Edad (años):</label>
        <input type="number" name="edad" id="edad" value="<?= old('edad') ?>" min="0" max="100" required>
    </div>

    <div>
        <label for="peso">Peso (kg):</label>
        <input type="number" step="0.1" name="peso" id="peso" value="<?= old('peso') ?>" min="0.1" required>
    </div>

    <div>
        <label for="id_cliente">Cliente dueño:</label>
        <select name="id_cliente" id="id_cliente" required>
            <option value="">Seleccione un cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id_cliente'] ?>" <?= old('id_cliente') == $cliente['id_cliente'] ? 'selected' : '' ?>>
                    <?= esc($cliente['nombre'] . ' ' . $cliente['apellido']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="estado">Estado:</label>
        <select name="estado" id="estado" required>
            <option value="1" <?= old('estado') === '1' ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= old('estado') === '0' ? 'selected' : '' ?>>Inactivo</option>
        </select>
    </div>

    <br>
    <button type="submit">Registrar Mascota</button>
    <a href="<?= base_url('recepcion/inicio') ?>" class="btn-cancelar">Cancelar</a>
</form>

<!-- BOTÓN DE REGRESO -->
<div style="margin-top: 20px;">
    <a href="<?= base_url('recepcion/inicio') ?>" class="btn-volver" style="display: inline-block; padding: 10px 20px; background-color: #337ab7; color: white; text-decoration: none; border-radius: 5px;">Volver al inicio</a>
</div>

<?= $this->endSection() ?>
