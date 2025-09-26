<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?? 'Panel de Administración' ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


    <!-- Font Awesome (sin integrity roto) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" referrerpolicy="no-referrer" />

    <!-- Estilos personalizados (opcional, descomentar si existe el archivo) -->
    <!-- <link rel="stylesheet" href="<?= base_url('css/estilos.css') ?>"> -->

    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 CSS y JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="<?= base_url('css/main2.css') ?>">


    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token-value" content="<?= csrf_hash() ?>">

    <!-- baseUrl global para JS -->
    <script>
        const baseUrl = "<?= base_url() ?>";
    </script>

</head>
<body>
  <!-- TOPBAR -->
  <header class="topbar">
    <div class="brand">
      <i class="fa-solid fa-paw"></i>
      <span>Panel</span>
    </div>

    <!-- Nav principal (visible en desktop) -->
    <?php
      helper('url');
      $current = trim(uri_string(), '/');
      function active($path){ return trim($path, '/') === trim(uri_string(), '/') ? 'is-active' : ''; }
    ?>
    <nav class="nav-main">
      <a class="<?= active('admin/dashboard') ?>" href="<?= base_url('admin/dashboard') ?>">Dashboard</a>
      <a class="<?= active('admin/usuarios') ?>"  href="<?= base_url('admin/usuarios') ?>">Usuarios</a>
      <a class="<?= active('veterinario/inicio') ?>" href="<?= base_url('veterinario/inicio') ?>">Veterinario</a>
      <a class="<?= active('recepcion/inicio') ?>"  href="<?= base_url('recepcion/inicio') ?>">Recepción</a>
      <a class="<?= active('cliente/inicio') ?>"    href="<?= base_url('cliente/inicio') ?>">Cliente</a>
    </nav>

    <!-- Acciones a la derecha (perfil / logout) -->
    <div class="top-actions">
      <a class="btn-text" href="<?= base_url('auth/logout') ?>"><i class="fa-solid fa-right-from-bracket"></i> <span>Cerrar sesión</span></a>
    </div>

    <!-- Botón hamburguesa (móvil) -->
    <button class="btn-menu" id="btnMenu" aria-expanded="false" aria-controls="drawer" aria-label="Abrir menú">
      <i class="fa-solid fa-bars"></i>
    </button>
  </header>

  <!-- DRAWER móvil -->
  <aside class="drawer" id="drawer" aria-hidden="true">
    <nav class="drawer-nav">
      <a class="<?= active('admin/dashboard') ?>" href="<?= base_url('admin/dashboard') ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
      <a class="<?= active('admin/usuarios') ?>"  href="<?= base_url('admin/usuarios') ?>"><i class="fa-solid fa-users-gear"></i> Usuarios</a>
      <a class="<?= active('veterinario/inicio') ?>" href="<?= base_url('veterinario/inicio') ?>"><i class="fa-solid fa-user-doctor"></i> Veterinario</a>
      <a class="<?= active('recepcion/inicio') ?>"  href="<?= base_url('recepcion/inicio') ?>"><i class="fa-solid fa-bell-concierge"></i> Recepción</a>
      <a class="<?= active('cliente/inicio') ?>"    href="<?= base_url('cliente/inicio') ?>"><i class="fa-solid fa-user"></i> Cliente</a>
      <hr>
      <a href="<?= base_url('auth/logout') ?>"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
    </nav>
    <button class="drawer-backdrop" id="drawerBackdrop" aria-label="Cerrar menú"></button>
  </aside>

  <!-- CONTENIDO -->
  <main class="page">
    <div class="content-wrapper">
      <h1 class="page-title"><?= $this->renderSection('title') ?? 'Panel' ?></h1>
      <?= $this->renderSection('content') ?>
    </div>
  </main>

  <script>
    // Toggle del drawer móvil (accesible)
    const btn = document.getElementById('btnMenu');
    const drawer = document.getElementById('drawer');
    const backdrop = document.getElementById('drawerBackdrop');

    function toggleDrawer(open){
      const isOpen = open ?? !drawer.classList.contains('open');
      drawer.classList.toggle('open', isOpen);
      drawer.setAttribute('aria-hidden', String(!isOpen));
      btn.setAttribute('aria-expanded', String(isOpen));
      if(isOpen) drawer.querySelector('a').focus();
    }
    btn.addEventListener('click', () => toggleDrawer());
    backdrop.addEventListener('click', () => toggleDrawer(false));
    window.addEventListener('keydown', e => { if(e.key === 'Escape') toggleDrawer(false); });
  </script>
</body>

</html>
