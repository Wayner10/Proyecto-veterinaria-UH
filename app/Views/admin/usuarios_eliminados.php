<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/admin/usuario_eliminado.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="usuarios-container">
    <div class="encabezado">
        <h2><i class="fas fa-user-slash"></i> Usuarios Desactivados</h2>
        <p>Estos son los usuarios que han sido desactivados.</p>
    </div>

    <div class="acciones" style="margin-bottom: 20px;">
        <a href="<?= base_url('/admin/usuarios') ?>" class="btn btn-azul">
            <i class="fas fa-arrow-left"></i> Volver a activos
        </a>
    </div>

    <table class="tabla-usuarios">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($usuarios)): ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= intval($usuario['id_usuario']) ?></td>
                        <td><?= esc($usuario['nombre']) ?></td>
                        <td><?= esc($usuario['correo_electronico']) ?></td>
                        <td>
                            <?php
                            $roles = [
                                1 => 'Administrador',
                                2 => 'Veterinario',
                                3 => 'Recepcionista',
                                4 => 'Cliente',
                            ];
                            echo esc($roles[$usuario['id_rol']] ?? 'Desconocido');
                            ?>
                        </td>
                        <td><span class="estado-activo inactivo">Inactivo</span></td>
                        <td>
                            <!-- Botón Reactivar en la tabla -->
                            <button class="btn btn-verde btn-reactivar"
                                data-bs-toggle="modal"
                                data-bs-target="#modal-confirma"
                                data-id="<?= $usuario['id_usuario'] ?>"
                                data-nombre="<?= esc($usuario['nombre']) ?>"
                                id="btn-reactivar-<?= $usuario['id_usuario'] ?>">
                                <i class="fas fa-user-check"></i> Reactivar
                            </button>


                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No hay usuarios desactivados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="modal-confirma" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel"><i class="fas fa-user-check"></i> Confirmar reactivación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                ¿Deseas reactivar al usuario <strong id="nombreUsuario"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a id="btnConfirmarReactivacion" href="#" class="btn btn-success">Sí, activar</a>
            </div>
        </div>
    </div>
</div>

<!-- Cargar script externo -->
<script src="<?= base_url('js/usuarios.js') ?>"></script>

<?= $this->endSection() ?>