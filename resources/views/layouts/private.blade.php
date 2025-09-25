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

    

    @yield('styles')
</head>
<body>
    @php
        $appUserEmail = 'admin.prueba@gmail.com';
        $appUserAvatar = 'img/hany.png';
        try {
            $jsonPath = base_path('user.json');
            if (file_exists($jsonPath)) {
                $data = json_decode(file_get_contents($jsonPath), true);
                if (isset($data['usuarios'][0]['correo'])) {
                    $appUserEmail = $data['usuarios'][0]['correo'];
                }
                if (!empty($data['usuarios'][0]['foto'])) {
                    $appUserAvatar = ltrim($data['usuarios'][0]['foto'], '/');
                }
            }
        } catch (\Throwable $e) {
            // silencio: usamos valores por defecto
        }
        $appUserAvatarUrl = asset($appUserAvatar);
        $navbarAvatar = optional(Auth::user())->avatar_path ? asset(optional(Auth::user())->avatar_path) : $appUserAvatarUrl;
    @endphp
    <!-- Topbar -->
    <nav class="navbar navbar-expand navbar-dark bg-success fixed-top shadow-sm">
        <div class="container-fluid">
            <button class="navbar-toggler d-lg-none border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Alternar menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <span class="navbar-text ms-2 fw-semibold text-white">Dashboard</span>

            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-white small d-none d-md-inline">Bienvenido, {{ optional(Auth::user())->name ?? 'Administrador' }}</span>
                <a href="#" class="text-white d-none d-md-inline"><i class="bi bi-bell fs-5"></i></a>
                <div class="dropdown" id="userDropdownWrapper">
                    <a class="d-flex align-items-center text-white text-decoration-none" href="#" id="userDropdown" aria-expanded="false" role="button">
                        <img src="{{ $navbarAvatar }}" alt="Avatar" class="rounded-circle" style="width:32px; height:32px; object-fit:cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow p-0" aria-labelledby="userDropdown" id="userDropdownMenu">
                        <li class="px-3 py-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ $navbarAvatar }}" alt="Avatar" class="rounded-circle me-3" style="width:48px; height:48px; object-fit:cover;">
                                <div class="min-w-0">
                                    <div class="fw-semibold text-truncate">{{ optional(Auth::user())->name ?? 'Administrador' }}</div>
                                    <div class="text-muted small text-truncate" style="max-width: 180px;">{{ optional(Auth::user())->email ?? $appUserEmail }}</div>
                                </div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li><a class="dropdown-item py-2" href="{{ route('perfil') }}">Mi Perfil</a></li>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li><a class="dropdown-item py-2" href="{{ route('logout') }}">Cerrar sesión</a></li>
                    </ul>
                </div>
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
            <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ request()->is('/') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ url('/mis-anuncios') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ request()->is('mis-anuncios') ? 'active' : '' }}">
                <i class="bi bi-card-list"></i>
                <span>Mis Anuncios</span>
            </a>
            <a href="{{ route('perfil') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ request()->is('perfil') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i>
                <span>Mi Perfil</span>
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

    <script>
        // Fallback mínimo: mostrar/ocultar menú al hacer clic en la foto
        (function(){
            var wrapper = document.getElementById('userDropdownWrapper');
            var trigger = document.getElementById('userDropdown');
            var menu = document.getElementById('userDropdownMenu');
            if (!wrapper || !trigger || !menu) return;
            trigger.addEventListener('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                menu.classList.toggle('show');
            });
            document.addEventListener('click', function(e){
                if (!wrapper.contains(e.target)) menu.classList.remove('show');
            });
        })();
    </script>
</body>
</html>


