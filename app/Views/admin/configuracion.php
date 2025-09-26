<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="configuracion-container">
    <h2><i class="fas fa-cogs"></i> Configuración del Sistema</h2>
    <p class="descripcion">Desde esta sección puedes ajustar parámetros generales del sistema. Usa los campos disponibles para aplicar tus cambios.</p>

    <!-- Mensajes de éxito o error -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert success"><?= session()->getFlashdata('success') ?></div>
    <?php elseif (session()->getFlashdata('error')): ?>
        <div class="alert error"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="config-box">
        <form action="<?= base_url('admin/configuracion/guardar') ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="nombre_sistema"><i class="fas fa-laptop-code"></i> Nombre del Sistema:</label>
                <input type="text" name="nombre_sistema" id="nombre_sistema" class="form-control"
                    value="<?= esc($nombre_sistema ?? '') ?>" placeholder="Ej: Sistema de Gestión Clínica" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="correo_contacto"><i class="fas fa-envelope"></i> Correo de Contacto:</label>
                <input type="email" name="correo_contacto" id="correo_contacto" class="form-control"
                    value="<?= esc($correo_contacto ?? '') ?>" placeholder="Ej: soporte@miapp.com" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="telefono"><i class="fas fa-phone"></i> Teléfono:</label>
                <input type="text" name="telefono" id="telefono" class="form-control"
                    value="<?= esc($telefono ?? '') ?>" placeholder="Ej: +506 8888 8888" required autocomplete="off">
            </div>

            <button type="submit" class="btn-guardar" onclick="this.innerText='Guardando...'; this.disabled=true;">
                <i class="fas fa-save"></i> Guardar cambios
            </button>
        </form>
    </div>
</div>

<style>
    .configuracion-container {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.05);
        max-width: 700px;
        margin: auto;
    }

    .configuracion-container h2 {
        font-size: 24px;
        color: #1f2937;
        margin-bottom: 10px;
    }

    .configuracion-container .descripcion {
        font-size: 15px;
        color: #6b7280;
        margin-bottom: 25px;
    }

    .config-box .form-group {
        margin-bottom: 20px;
    }

    .config-box label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        color: #374151;
    }

    .form-control {
        width: 100%;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
    }

    .btn-guardar {
        background-color: #2563eb;
        color: #fff;
        padding: 10px 20px;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: 0.3s ease;
    }

    .btn-guardar:hover {
        background-color: #1e40af;
    }

    .btn-guardar i {
        margin-right: 6px;
    }

    .alert {
        padding: 12px;
        margin-bottom: 20px;
        border-radius: 8px;
        font-weight: 500;
    }

    .alert.success {
        background-color: #d1fae5;
        color: #065f46;
    }

    .alert.error {
        background-color: #fee2e2;
        color: #991b1b;
    }
</style>

<?= $this->endSection() ?>
