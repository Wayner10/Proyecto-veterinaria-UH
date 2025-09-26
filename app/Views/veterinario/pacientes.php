<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/veterinario/ver_citas.css') ?>">

<div class="citas-wrapper">
  <div class="citas-header">
    <h2>Listado de Pacientes</h2>
  </div>
  <p class="subtexto">Mascotas registradas en la clínica</p>

  <div class="table-responsive">
    <table class="tabla-citas">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Especie</th>
          <th>Raza</th>
          <th>Edad</th>
          <th>Dueño</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($pacientes)) : ?>
          <?php foreach ($pacientes as $p) : ?>
            <tr>
              <td><?= esc($p->id_mascota) ?></td>
              <td><?= esc($p->nombre) ?></td>
              <td><?= esc($p->especie) ?></td>
              <td><?= esc($p->raza) ?></td>
              <td><?= esc($p->edad) ?> años</td>
              <td><?= esc($p->dueno) ?></td>
              <td>
                <?php $activo = (int)$p->estado === 1; ?>
                <span class="badge <?= $activo ? 'badge-activa' : 'badge-cancelada' ?>">
                  <?= $activo ? 'Activo' : 'Inactivo' ?>
                </span>
              </td>
              <td>
                <a href="<?= base_url('veterinario/tratamientos/' . $p->id_mascota) ?>" 
                   class="btn btn-sm">
                   Historial
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="8">No hay pacientes registrados.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="botones">
    <a href="<?= base_url('veterinario/inicio') ?>" class="btn-volver">← Volver al panel</a>
  </div>
</div>

<?= $this->endSection() ?>
