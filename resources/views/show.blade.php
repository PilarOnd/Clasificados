@extends('layouts.private')

@section('title', 'Detalles del Anuncio')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('mis-anuncios') }}" class="text-decoration-none">
                            <i class="bi bi-arrow-left"></i> Mis Anuncios
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Detalles del Anuncio #{{ $anuncio['id'] }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="h4 mb-0">Anuncio #{{ $anuncio['id'] }}</h1>
                        @php
                            $estado = $anuncio['estado'] ?? null;
                            $badgeClass = $estado === 'Activo' ? 'text-bg-success' : 'text-bg-secondary';
                        @endphp
                        @if($estado)
                            <span class="badge {{ $badgeClass }} fs-6">{{ $estado }}</span>
                        @endif
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <div class="row">
                        <!-- Columna de la imagen (izquierda) -->
                        @if(!empty($anuncio['foto_url']))
                            <div class="col-md-4 mb-4 mb-md-0">
                                <h5 class="text-muted mb-3">Imagen del Anuncio</h5>
                                <div class="anuncio-image-container">
                                    <img src="{{ $anuncio['foto_url'] }}" 
                                         class="img-fluid anuncio-detail-image" 
                                         alt="Imagen del anuncio {{ $anuncio['id'] }}"
                                         style="width: 100%; max-height: 400px; object-fit: contain; border-radius: 0.375rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);">
                                </div>
                            </div>
                        @endif
                        
                        <!-- Columna de los datos (derecha) -->
                        <div class="col-md-8">
                            <!-- Descripción -->
                            <div class="mb-4">
                                <h5 class="text-muted mb-3">Descripción</h5>
                                <div class="anuncio-description">{!! nl2br(e(trim($anuncio['descripcion'] ?? 'Sin descripción'))) !!}</div>
                            </div>
                            
                            <!-- Información del anuncio -->
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <h6 class="text-muted mb-2">
                                        <i class="bi bi-calendar-event"></i> Fecha de Publicación
                                    </h6>
                                    <p class="mb-0">
                                        {{ \Illuminate\Support\Carbon::parse($anuncio['fecha_publicacion'] ?? now())->translatedFormat('l, d \d\e F \d\e Y') }}
                                    </p>
                                </div>
                                
                                <div class="col-sm-6 mb-3">
                                    <h6 class="text-muted mb-2">
                                        <i class="bi bi-hash"></i> ID del Anuncio
                                    </h6>
                                    <p class="mb-0 fw-semibold">#{{ $anuncio['id'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('mis-anuncios') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver a la Lista
                        </a>
                        
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="compartirAnuncio()">
                                <i class="bi bi-share"></i> Compartir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function compartirAnuncio() {
    if (navigator.share) {
        navigator.share({
            title: 'Anuncio #{{ $anuncio["id"] }}',
            text: '{{ Str::limit($anuncio["descripcion"] ?? "", 100) }}',
            url: window.location.href
        });
    } else {
        // Fallback para navegadores que no soportan Web Share API
        navigator.clipboard.writeText(window.location.href).then(function() {
            alert('Enlace copiado al portapapeles');
        });
    }
}
</script>

<style>
.anuncio-description {
    font-size: 1.1rem;
    line-height: 1.6;
    word-wrap: break-word;
    text-align: left;
    margin: 0;
    padding: 0;
    text-indent: 0;
    white-space: normal;
}

.anuncio-detail-image {
    transition: transform 0.2s ease-in-out;
    border: 1px solid #dee2e6;
}

.anuncio-detail-image:hover {
    transform: scale(1.02);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.anuncio-image-container {
    position: relative;
    overflow: hidden;
    border-radius: 0.375rem;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .anuncio-detail-image {
        max-height: 300px !important;
    }
}

@media print {
    .card-footer,
    .breadcrumb,
    .btn {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .anuncio-detail-image {
        max-height: 200px !important;
    }
}
</style>
@endsection
