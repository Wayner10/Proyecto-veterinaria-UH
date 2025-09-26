<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h1>Solicitar Cita</h1>

<?php if(session('errors')): ?>
  <div class="alert"><?= implode('<br>', array_map('esc', session('errors'))) ?></div>
<?php endif ?>

<form method="post" action="<?= site_url('cliente/citas/guardar') ?>">

  <?= csrf_field() ?>

  <label>Mascota</label>
  <select name="id_mascota" required>
    <option value="">Seleccione...</option>
    <?php foreach($mascotas as $m): ?>
      <option value="<?= esc($m->id_mascota) ?>"><?= esc($m->nombre) ?></option>
    <?php endforeach ?>
  </select>

  <label>Veterinario</label>
  <select name="id_veterinario" required>
    <option value="">Seleccione...</option>
    <?php foreach($veterinarios as $v): ?>
      <option value="<?= esc($v->id_veterinario) ?>"><?= esc(trim($v->nombre.' '.$v->apellido)) ?></option>
    <?php endforeach ?>
  </select>

  <label>Fecha y hora</label>
  <input type="datetime-local" name="fecha_hora" required>

  <label>Motivo</label>
  <input type="text" name="motivo" maxlength="255" required>

  <div class="acciones">
    <a class="btn secondary" href="<?= base_url('cliente/citas') ?>">Cancelar</a>
    <button class="btn" type="submit">Enviar solicitud</button>
  </div>
</form>

<style>
form{background:#fff;border:1px solid #E5E7EB;border-radius:12px;padding:16px;max-width:640px}
label{display:block;margin-top:10px;font-weight:600}
select,input{width:100%;padding:10px;border:1px solid #E5E7EB;border-radius:8px;margin-top:6px}
.alert{background:#FEF3C7;border:1px solid #F59E0B;color:#7C2D12;padding:10px;border-radius:8px;margin-bottom:10px}
.acciones{display:flex;gap:10px;justify-content:flex-end;margin-top:14px}
.btn{background:#0EA5A4;color:#fff;padding:10px 14px;border-radius:10px;border:0;cursor:pointer;text-decoration:none}
.btn.secondary{background:#fff;color:#0F172A;border:1px solid #E5E7EB}
</style>

<?= $this->endSection() ?>
