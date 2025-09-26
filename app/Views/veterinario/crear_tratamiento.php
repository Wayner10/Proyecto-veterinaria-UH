<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/ver_citas.css') ?>">

<div class="citas-wrapper">
  <div class="citas-header">
    <h2>Registrar Tratamiento</h2>
  </div>
  <p class="subtexto">
    Mascota:
    <strong><?= esc($mascota['nombre']) ?></strong>
    (<?= esc($mascota['especie']) ?> - <?= esc($mascota['raza']) ?>)
  </p>

  <form action="<?= base_url('veterinario/tratamientos/guardar/' . (int)$cita['id_cita']) ?>" method="post" novalidate>
    <?= csrf_field() ?>

    <!-- Descripción -->
    <div style="margin-bottom:12px">
      <label for="descripcion" style="display:block; font-weight:700; margin-bottom:6px;">Descripción del tratamiento</label>
      <textarea
        name="descripcion"
        id="descripcion"
        rows="4"
        required
        maxlength="1000"
        placeholder="Indique fármaco/dosis, pauta, frecuencia, recomendaciones..."
        style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:10px; font-size:14px; resize:vertical;"></textarea>
      <div class="subtexto" style="margin-top:4px">Máx. 1000 caracteres.</div>
    </div>

    <!-- Fechas -->
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
      <div>
        <label for="fecha_inicio" style="display:block; font-weight:700; margin-bottom:6px;">Fecha de inicio</label>
        <input
          type="date"
          name="fecha_inicio"
          id="fecha_inicio"
          required
          value="<?= esc(date('Y-m-d')) ?>"
          style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:10px; font-size:14px;">
      </div>

      <div>
        <label for="fecha_fin" style="display:block; font-weight:700; margin-bottom:6px;">Fecha de finalización (opcional)</label>
        <input
          type="date"
          name="fecha_fin"
          id="fecha_fin"
          style="width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:10px; font-size:14px;">
        <div class="subtexto" style="margin-top:4px">Déjalo vacío si aún no hay fecha prevista.</div>
      </div>
    </div>

    <!-- Botones -->
    <div class="botones">
      <button type="submit" class="btn" style="background:var(--secondary)"><?= esc('+ Guardar Tratamiento') ?></button>
      <a href="<?= base_url('veterinario/tratamientos/' . (int)$mascota['id_mascota']) ?>" class="btn-volver secondary">Cancelar</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
