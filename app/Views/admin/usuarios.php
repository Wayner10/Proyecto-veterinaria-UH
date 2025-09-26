<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" href="<?= base_url('css/admin/usuario2.css') ?>">

<?php
    $usuarioModel = new \App\Models\UsuarioModel();
    $usuariosDesactivados = $usuarioModel->where('estado', false)->countAllResults();
?>

<div class="usuarios-container">
    <h2><i class="fas fa-users"></i> Gestión de Usuarios</h2>
    <p>Desde aquí puedes crear, editar o desactivar usuarios del sistema.</p>

    <div class="acciones">
        <a href="<?= base_url('/admin/usuarios/crear') ?>" class="btn-crear">
            <i class="fas fa-user-plus"></i> Crear Usuario
        </a>

        <?php if ($usuariosDesactivados > 0): ?>
            <a href="<?= base_url('/admin/usuarios/eliminados') ?>" class="btn-ver-eliminados">
                <i class="fas fa-trash"></i> Ver eliminados
            </a>
        <?php endif; ?>
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
                        <td>
                            <span class="<?= $usuario['estado'] ? 'estado-activo' : 'estado-inactivo' ?>">
                                <?= $usuario['estado'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= base_url('admin/usuarios/editar/' . $usuario['id_usuario']) ?>" class="btn-accion editar">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="<?= base_url('admin/usuarios/eliminar/' . $usuario['id_usuario']) ?>" class="btn-accion eliminar" onclick="return confirm('¿Estás seguro que deseas desactivar este usuario?')">
                                <i class="fas fa-user-slash"></i> Desactivar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No hay usuarios registrados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>
