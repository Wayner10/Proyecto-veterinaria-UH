<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h1>Panel</h1>

<div class="grid">
  <a class="card link" href="<?= base_url('cliente/mascotas') ?>" aria-label="Ir a Mis Mascotas">
    <h3>Mascotas</h3>
    <p class="big"><?= esc($mascotas) ?></p>
    <span class="hint">Ver todas</span>
  </a>

  <a class="card link" href="<?= base_url('cliente/citas') ?>" aria-label="Ir a Mis Citas">
    <h3>Citas próximas</h3>
    <p class="big"><?= esc($citasPend) ?></p>
    <span class="hint">Ver calendario</span>
  </a>

  <a class="card link" href="<?= base_url('cliente/facturas') ?>" aria-label="Ir a Mis Facturas">
    <h3>Facturas</h3>
    <p class="big"><?= esc($facturas) ?></p>
    <span class="hint">Ver historial</span>
  </a>
</div>

<style>
.grid{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
  gap:16px;
}
.card{
  background:#fff;
  border:1px solid #E5E7EB;
  border-radius:12px;
  padding:16px;
}
.card .big{font-size:28px;font-weight:800;margin:8px 0 0}
.card .hint{display:inline-block;margin-top:8px;font-size:12px;color:#64748B}
.card.link{
  text-decoration:none; color:inherit; transition:transform .06s ease, box-shadow .2s ease, border-color .2s ease;
}
.card.link:hover{
  border-color:#0EA5A4;
  box-shadow:0 8px 24px rgba(0,0,0,.06);
  transform:translateY(-1px);
}
.card.link:focus-visible{
  outline:none;
  box-shadow:0 0 0 3px rgba(14,165,164,.3);
}
</style>

<?= $this->endSection() ?>
