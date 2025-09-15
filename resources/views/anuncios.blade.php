@extends('layouts.private')

@section('title', 'Mis Anuncios')


@section('content')
@php
    $initialView = request('view') === 'table' ? 'table' : 'cards';
@endphp
<div class="container py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h4 m-0">Mis Anuncios</h1>
        <div class="d-flex gap-2">
            <div class="btn-group" role="group">
                <button type="button" id="viewCards" class="btn btn-outline-success {{ $initialView === 'cards' ? 'active' : '' }}" onclick="toggleView('cards')">
                    <i class="bi bi-grid-3x3-gap"></i>
                </button>
                <button type="button" id="viewTable" class="btn btn-outline-success {{ $initialView === 'table' ? 'active' : '' }}" onclick="toggleView('table')">
                    <i class="bi bi-table"></i>
                </button>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('mis-anuncios') }}" class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <input type="hidden" id="viewHidden" name="view" value="{{ $initialView }}">
                <div class="col-12 col-md-6 col-lg-5">
                    <label for="q" class="form-label small mb-1">Buscar</label>
                    <input type="text" id="q" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por descripción...">
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label for="desde" class="form-label small mb-1">Desde</label>
                    <input type="date" id="desde" name="desde" value="{{ request('desde') }}" class="form-control">
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <label for="hasta" class="form-label small mb-1">Hasta</label>
                    <input type="date" id="hasta" name="hasta" value="{{ request('hasta') }}" class="form-control">
                </div>
                <div class="col-12 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success w-100">Filtrar</button>
                    <a href="{{ route('mis-anuncios') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </div>
        </div>
    </form>

    @if(empty($anuncios))
        <div class="alert alert-warning">No hay anuncios para mostrar.</div>
    @else
        <!-- Vista de Tarjetas -->
        <div id="cardsView" class="row g-3 {{ $initialView === 'table' ? 'd-none' : '' }}">
            @foreach($anuncios as $anuncio)
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="card anuncio-card h-100">
                        @php
                            $estado = $anuncio['estado'] ?? null;
                            $badgeClass = $estado === 'Activo' ? 'text-bg-success' : 'text-bg-secondary';
                        @endphp
                        @if($estado)
                            <span class="badge estado-badge {{ $badgeClass }}">{{ $estado }}</span>
                        @endif
                        @if(!empty($anuncio['foto_url']))
                            <img src="{{ $anuncio['foto_url'] }}" class="card-img-top" alt="Imagen del anuncio {{ $anuncio['id'] }}">
                        @endif
                        <div class="card-body d-flex flex-column">
                            <p class="card-text mb-2">{{ $anuncio['descripcion'] ?? 'Sin descripción' }}</p>
                            <div class="mt-auto small text-secondary">
                                Publicado: {{ \Illuminate\Support\Carbon::parse($anuncio['fecha_publicacion'] ?? now())->translatedFormat('d M Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Vista de Tabla -->
        <div id="tableView" class="{{ $initialView === 'table' ? '' : 'd-none' }}">
            <div class="table-responsive">
                <table class="table table-hover anuncios-table">
                    <thead class="bg-white">
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Descripción</th>
                            <th>
                                @php
                                    $currentSort = request('sort');
                                    $currentDir = strtolower(request('dir','asc')) === 'desc' ? 'desc' : 'asc';
                                    $nextDirFecha = ($currentSort === 'fecha' && $currentDir === 'asc') ? 'desc' : 'asc';
                                    $nextDirEstado = ($currentSort === 'estado' && $currentDir === 'asc') ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ route('mis-anuncios', array_merge(request()->all(), ['sort' => 'fecha', 'dir' => $nextDirFecha, 'view' => 'table'])) }}" class="text-decoration-none text-reset">
                                    Fecha de Publicación
                                    @if($currentSort === 'fecha')
                                        <i class="bi {{ $currentDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('mis-anuncios', array_merge(request()->all(), ['sort' => 'estado', 'dir' => $nextDirEstado, 'view' => 'table'])) }}" class="text-decoration-none text-reset">
                                    Estado
                                    @if($currentSort === 'estado')
                                        <i class="bi {{ $currentDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                                    @endif
                                </a>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($anuncios as $anuncio)
                            <tr>
                                <td class="fw-semibold">{{ $anuncio['id'] ?? '-' }}</td>
                                <td>
                                    @if(!empty($anuncio['foto_url']))
                                        <img src="{{ $anuncio['foto_url'] }}" alt="Imagen del anuncio {{ $anuncio['id'] }}" 
                                             style="width: 60px; height: 60px; object-fit: cover;" class="rounded">
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $anuncio['descripcion'] ?? 'Sin descripción' }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($anuncio['fecha_publicacion'] ?? now())->translatedFormat('d M Y') }}</td>
                                @php
                                    $estado = $anuncio['estado'] ?? null;
                                    $badgeClass = $estado === 'Activo' ? 'text-bg-success' : 'text-bg-secondary';
                                @endphp
                                <td>
                                    @if($estado)
                                        <span class="badge {{ $badgeClass }}">{{ $estado }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function toggleView(viewType) {
    const cardsView = document.getElementById('cardsView');
    const tableView = document.getElementById('tableView');
    const cardsBtn = document.getElementById('viewCards');
    const tableBtn = document.getElementById('viewTable');
    const viewHidden = document.getElementById('viewHidden');
    
    if (viewType === 'cards') {
        cardsView.classList.remove('d-none');
        tableView.classList.add('d-none');
        cardsBtn.classList.add('active');
        tableBtn.classList.remove('active');
    } else {
        cardsView.classList.add('d-none');
        tableView.classList.remove('d-none');
        cardsBtn.classList.remove('active');
        tableBtn.classList.add('active');
    }
    if (viewHidden) viewHidden.value = viewType;
    // Actualizar query param sin recargar
    const url = new URL(window.location.href);
    url.searchParams.set('view', viewType);
    window.history.replaceState({}, '', url);
}
</script>
@endsection


