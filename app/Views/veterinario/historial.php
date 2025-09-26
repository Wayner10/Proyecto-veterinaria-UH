<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h2>Historial Médico</h2>

<form action="<?= base_url('veterinario/historial/buscar') ?>" method="post">
    <input type="text" name="termino" placeholder="Buscar paciente..." class="form-control mb-2">
    <button type="submit" class="btn btn-primary">Buscar</button>
</form>

<?php if (!empty($historiales)): ?>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>Paciente</th>
                <th>Fecha</th>
                <th>Diagnóstico</th>
                <th>Tratamiento</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historiales as $h): ?>
                <tr>
                    <td><?= esc($h->paciente_nombre) ?></td>
                    <td><?= esc($h->fecha) ?></td>
                    <td><?= esc($h->diagnostico) ?></td>
                    <td><?= esc($h->tratamiento) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>

<?= $this->endSection() ?>
