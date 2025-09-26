<div class="citas-wrapper">
  <div class="citas-header">

        <link rel="stylesheet" href="<?= base_url('css/veterinario/ver_citas.css') ?>">

    <h2>Listado de Citas</h2>
  </div>
  <p class="subtexto">Agenda del veterinario</p>

  <div class="table-responsive">
    <table class="tabla-citas">
      <thead>
        <tr>
          <th>ID Cita</th>
          <th>Mascota</th>
          <th>Veterinario</th>
          <th>Fecha y Hora</th>
          <th>Motivo</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($citas)) : ?>
          <?php foreach ($citas as $cita) : ?>
            <tr>
              <td><?= esc($cita->id_cita) ?></td>
              <td><?= esc($cita->nombre_mascota) ?></td>
              <td><?= esc($cita->nombre_veterinario) ?></td>
              <td><?= esc($cita->fecha_hora) ?></td>
              <td><?= esc($cita->motivo) ?></td>
              <td>
                <?php $isActiva = (int)$cita->estado === 1; ?>
                <span class="badge <?= $isActiva ? 'badge-activa' : 'badge-cancelada' ?>">
                  <?= $isActiva ? 'Activa' : 'Cancelada' ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr>
            <td colspan="6">No hay citas registradas.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="botones">
    <a href="<?= base_url('veterinario/inicio') ?>" class="btn-volver">← Volver al panel</a>
  </div>
</div>
