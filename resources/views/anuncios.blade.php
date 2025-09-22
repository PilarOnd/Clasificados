@extends('layouts.private')

@section('title', 'Mis Anuncios')


@section('content')
@php
    $initialView = request('view') === 'table' ? 'table' : 'cards';
    $activeStatus = $status ?? request('status');
    $tabs = [
        [ 'key' => null, 'label' => 'Todos', 'count' => null ],
        [ 'key' => 'borrador', 'label' => 'Borradores', 'count' => $counts['borrador'] ?? 0 ],
        [ 'key' => 'esperando_pago', 'label' => 'En espera de pago', 'count' => $counts['esperando_pago'] ?? 0 ],
        [ 'key' => 'esperando_aprobacion', 'label' => 'En espera de aprobación', 'count' => $counts['esperando_aprobacion'] ?? 0 ],
        [ 'key' => 'aprobado', 'label' => 'Aprobados', 'count' => $counts['aprobado'] ?? 0 ],
        [ 'key' => 'rechazado', 'label' => 'Rechazados', 'count' => $counts['rechazado'] ?? 0 ],
    ];
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

    <!-- Tabs de estado -->
    <div class="nav nav-tabs d-flex mb-3" role="tablist">
        @foreach($tabs as $tab)
            @php
                $isActive = ($activeStatus === ($tab['key'] ?? null)) || ($activeStatus === null && $tab['key'] === null);
                $urlParams = array_merge(request()->all(), ['status' => $tab['key'], 'view' => $initialView]);
                if ($tab['key'] === null) { unset($urlParams['status']); }
            @endphp
            <div class="nav-item flex-fill" role="presentation">
                <a href="{{ route('mis-anuncios', $urlParams) }}" class="nav-link {{ $isActive ? 'active' : '' }} text-center" aria-current="{{ $isActive ? 'page' : 'false' }}">
                    {{ $tab['label'] }}
                    @if(!is_null($tab['count']))
                        <span class="badge text-bg-secondary ms-1">{{ $tab['count'] }}</span>
                    @endif
                </a>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('mis-anuncios') }}" class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <input type="hidden" id="viewHidden" name="view" value="{{ $initialView }}">
                <input type="hidden" id="statusHidden" name="status" value="{{ $activeStatus }}">
                <div class="col-12 col-md-6 col-lg-5">
                    <label for="q" class="form-label small mb-1">Buscar</label>
                    <input type="text" id="q" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar por descripción...">
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <label class="form-label small mb-1">Fecha(s) de publicación</label>
                    <div class="input-group">
                        <input type="date" id="desde" name="desde" value="{{ request('desde') }}" class="form-control">
                        <span class="input-group-text">—</span>
                        <input type="date" id="hasta" name="hasta" value="{{ request('hasta') }}" class="form-control">
                    </div>
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
                    <a href="{{ route('anuncio.show', $anuncio['id']) }}" class="text-decoration-none">
                        <div class="card anuncio-card h-100 hover-card">
                            @php
                                $pubPhase = $anuncio['publicacion_fase'] ?? null;
                                $pubColor = $anuncio['publicacion_color'] ?? 'secondary';
                            @endphp
                            @if($pubPhase)
                                <span class="badge publicacion-badge text-bg-{{ $pubColor }}">{{ $pubPhase }}</span>
                            @endif
                            @if(!empty($anuncio['foto_url']))
                                <img src="{{ $anuncio['foto_url'] }}" class="card-img-top" alt="Imagen del anuncio {{ $anuncio['id'] }}">
                            @endif
                            <div class="card-body d-flex flex-column">
                                <p class="card-text mb-2 text-dark">{{ $anuncio['descripcion'] ?? 'Sin descripción' }}</p>
                                <div class="mt-auto small text-secondary">
                                    @php
                                        $fi = $anuncio['fecha_inicio'] ?? null;
                                        $ff = $anuncio['fecha_fin'] ?? null;
                                    @endphp
                                    @if($fi && $ff)
                                        {{ \Illuminate\Support\Carbon::parse($fi)->translatedFormat('d M Y') }} — {{ \Illuminate\Support\Carbon::parse($ff)->translatedFormat('d M Y') }}
                                    @elseif($fi)
                                        {{ \Illuminate\Support\Carbon::parse($fi)->translatedFormat('d M Y') }}
                                    @else
                                        Publicado: {{ \Illuminate\Support\Carbon::parse($anuncio['fecha_publicacion'] ?? now())->translatedFormat('d M Y') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
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
                            <th class="w-50">Descripción</th>
                            <th>
                                @php
                                    $currentSort = request('sort');
                                    $currentDir = strtolower(request('dir','asc')) === 'desc' ? 'desc' : 'asc';
                                    $nextDirFecha = ($currentSort === 'fecha' && $currentDir === 'asc') ? 'desc' : 'asc';
                                    $nextDirEstado = ($currentSort === 'estado' && $currentDir === 'asc') ? 'desc' : 'asc';
                                @endphp
                                <a href="{{ route('mis-anuncios', array_merge(request()->all(), ['sort' => 'fecha', 'dir' => $nextDirFecha, 'view' => 'table'])) }}" class="text-decoration-none text-reset">
                                    Fecha(s) de Publicación
                                    @if($currentSort === 'fecha')
                                        <i class="bi {{ $currentDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($anuncios as $anuncio)
                            <tr class="table-row-clickable" onclick="window.location.href='{{ route('anuncio.show', $anuncio['id']) }}'" style="cursor: pointer;">
                                <td class="fw-semibold">{{ $anuncio['id'] ?? '-' }}</td>
                                <td>
                                    @if(!empty($anuncio['foto_url']))
                                        <img src="{{ $anuncio['foto_url'] }}" alt="Imagen del anuncio {{ $anuncio['id'] }}" 
                                             style="width: 60px; height: 60px; object-fit: cover;" class="rounded">
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="w-50">
                                    <div class="description-cell">
                                        {{ $anuncio['descripcion'] ?? 'Sin descripción' }}
                                    </div>
                                </td>
                                <td style="white-space: nowrap;">
                                    @php
                                        $fi = $anuncio['fecha_inicio'] ?? null;
                                        $ff = $anuncio['fecha_fin'] ?? null;
                                    @endphp
                                    @if($fi && $ff)
                                        <div class="date-range">
                                            <div>{{ \Illuminate\Support\Carbon::parse($fi)->translatedFormat('d M Y') }}</div>
                                            <div class="text-muted small">{{ \Illuminate\Support\Carbon::parse($ff)->translatedFormat('d M Y') }}</div>
                                        </div>
                                    @elseif($fi)
                                        <div>{{ \Illuminate\Support\Carbon::parse($fi)->translatedFormat('d M Y') }}</div>
                                    @else
                                        <div>{{ \Illuminate\Support\Carbon::parse($anuncio['fecha_publicacion'] ?? now())->translatedFormat('d M Y') }}</div>
                                    @endif
                                </td>
                                @php
                                    $pubPhase = $anuncio['publicacion_fase'] ?? null;
                                    $pubColor = $anuncio['publicacion_color'] ?? 'secondary';
                                @endphp
                                <td>
                                    @if($pubPhase)
                                        <span class="badge text-bg-{{ $pubColor }}">{{ $pubPhase }}</span>
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
    const statusHidden = document.getElementById('statusHidden');
    
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
    // Actualizar query param sin recargar y preservar status
    const url = new URL(window.location.href);
    url.searchParams.set('view', viewType);
    if (statusHidden && statusHidden.value) {
        url.searchParams.set('status', statusHidden.value);
    }
    window.history.replaceState({}, '', url);
}
</script>

<style>
.hover-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}


.hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-color: #0d6efd;
}

.table-row-clickable:hover {
    background-color: #f8f9fa !important;
}

.anuncio-card {
    position: relative;
    overflow: hidden;
}

.publicacion-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 2;
}

.card-img-top {
    transition: transform 0.3s ease;
}

.hover-card:hover .card-img-top {
    transform: scale(1.05);
}

/* Mejorar la accesibilidad */
.hover-card:focus-within {
    outline: 2px solid #0d6efd;
    outline-offset: 2px;
}

/* Optimizaciones para la tabla */
.description-cell {
    word-wrap: break-word;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.date-range {
    line-height: 1.2;
}

</style>
@endsection


