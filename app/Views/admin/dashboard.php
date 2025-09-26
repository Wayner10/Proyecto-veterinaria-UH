<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="<?= base_url('css/admin/admin.css') ?>">



    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

    <div class="admin-dashboard">
        <h1>Bienvenido, <?= esc(session('nombre') ?? 'Administrador') ?></h1>

        <div class="nav-links">
            <a href="<?= base_url('admin/usuarios') ?>" aria-label="Usuarios"><i class="fas fa-users"></i> Usuarios</a>
            <a href="<?= base_url('admin/configuracion') ?>" aria-label="Configuración"><i class="fas fa-cogs"></i> Configuración</a>
            <a href="<?= base_url('admin/reportes') ?>" aria-label="Reportes"><i class="fas fa-chart-line"></i> Reportes</a>
        </div>

        <div class="logout">
            <a href="<?= base_url('auth/logout') ?>" aria-label="Cerrar sesión"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
        </div>
    </div>

</body>
</html>
