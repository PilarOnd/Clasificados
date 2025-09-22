<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Estilos globales del proyecto -->
    <link rel="stylesheet" href="{{ asset('css/private.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root { --navbar-height: 56px; --sidebar-width: 260px; }
        body { padding-top: var(--navbar-height); text-align: justify; overflow: hidden; }
        main#mainContent { height: calc(100vh - var(--navbar-height)); overflow: auto; }
        /* Sidebar fijo en pantallas grandes */
        @media (min-width: 992px) {
            #sidebarMenu { position: fixed; top: var(--navbar-height); bottom: 0; width: var(--sidebar-width); max-width: var(--sidebar-width); overflow-y: auto; overflow-x: hidden; }
            main#mainContent { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); }
        }

        /* Ajustes de colores para sidebar en bg-success */
        #sidebarMenu .list-group-item { background-color: transparent; color: #fff; border: 0; }
        #sidebarMenu .list-group-item:hover { background-color: rgba(255,255,255,0.12); color: #fff; }
        #sidebarMenu .list-group-item.active { background-color: rgba(255,255,255,0.25); color: #fff; border: 0; }
        #sidebarMenu .list-group-item i { color: #ffffff; }
        #sidebarMenu .list-group-item.active i { color: #ffffff; }
    </style>

    @yield('styles')
</head>
<body>
    <!-- Topbar -->
    <nav class="navbar navbar-expand navbar-dark bg-success fixed-top shadow-sm">
        <div class="container-fluid">
            <button class="navbar-toggler d-lg-none border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Alternar menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <span class="navbar-text ms-2 fw-semibold text-white">Dashboard</span>

            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-white small d-none d-md-inline">Bienvenido, Administrador</span>
                <a href="#" class="text-white"><i class="bi bi-bell fs-5"></i></a>
                <a href="#" class="text-white"><i class="bi bi-check-circle-fill fs-5"></i></a>
                @yield('navbar-actions')
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div id="sidebarMenu" class="collapse d-lg-block bg-success text-white">
        <div class="d-flex align-items-center justify-content-center w-100 px-3 py-3 border-bottom border-success-subtle">
            <a href="{{ url('/') }}" class="d-inline-flex align-items-center text-decoration-none">
                <img src="{{ asset('img/logo.png') }}" alt="EL DEBER" style="height:70px; width:auto;">
            </a>
        </div>
        <div class="list-group list-group-flush py-2">
            <a href="{{ url('/') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ request()->is('/') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ url('/mis-anuncios') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ request()->is('mis-anuncios') ? 'active' : '' }}">
                <i class="bi bi-card-list"></i>
                <span>Mis Anuncios</span>
            </a>
            @yield('sidebar-extra')
        </div>
    </div>

    <!-- Contenido principal -->
    <main id="mainContent" class="container-fluid py-3">
        @yield('content')
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Bootstrap Icons (opcional para los íconos del sidebar) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    @yield('scripts')
</body>
</html>


