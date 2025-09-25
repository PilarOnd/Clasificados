@extends('layouts.private')

@section('title', 'Editar Anuncio')

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Editar Anuncio #{{ $anuncio['id'] }}</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('anuncio.update', $anuncio['id']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción *</label>
                            <textarea 
                                class="form-control @error('descripcion') is-invalid @enderror" 
                                id="descripcion" 
                                name="descripcion" 
                                rows="4" 
                                required
                                maxlength="1000"
                            >{{ old('descripcion', $anuncio['descripcion'] ?? '') }}</textarea>
                            <div class="form-text">Máximo 1000 caracteres</div>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                    <input 
                                        type="date" 
                                        class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                        id="fecha_inicio" 
                                        name="fecha_inicio" 
                                        value="{{ old('fecha_inicio', $anuncio['fecha_inicio'] ?? '') }}"
                                    >
                                    @error('fecha_inicio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                    <input 
                                        type="date" 
                                        class="form-control @error('fecha_fin') is-invalid @enderror" 
                                        id="fecha_fin" 
                                        name="fecha_fin" 
                                        value="{{ old('fecha_fin', $anuncio['fecha_fin'] ?? '') }}"
                                    >
                                    @error('fecha_fin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="foto" class="form-label">Imagen</label>
                            <input 
                                type="file" 
                                class="form-control @error('foto') is-invalid @enderror" 
                                id="foto" 
                                name="foto" 
                                accept="image/*"
                            >
                            <div class="form-text">Formatos permitidos: JPEG, PNG, JPG, GIF. Máximo 2MB</div>
                            @error('foto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            @if(!empty($anuncio['foto_url']))
                                <div class="mt-2">
                                    <p class="small text-muted mb-1">Imagen actual:</p>
                                    <img src="{{ $anuncio['foto_url'] }}" alt="Imagen actual" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado Actual</label>
                            <div>
                                @php
                                    $pubPhase = $anuncio['publicacion_fase'] ?? null;
                                    $pubColor = $anuncio['publicacion_color'] ?? 'secondary';
                                @endphp
                                @if($pubPhase)
                                    <span class="badge text-bg-{{ $pubColor }}">{{ $pubPhase }}</span>
                                @else
                                    <span class="text-muted">Sin estado</span>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-lg me-1"></i>
                                Guardar Cambios
                            </button>
                            <a href="{{ route('anuncio.show', $anuncio['id']) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>
                                Cancelar
                            </a>
                            <a href="{{ route('mis-anuncios') }}" class="btn btn-outline-primary">
                                <i class="bi bi-list me-1"></i>
                                Ver Todos los Anuncios
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación de fechas
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    
    function validateDates() {
        if (fechaInicio.value && fechaFin.value) {
            if (new Date(fechaInicio.value) > new Date(fechaFin.value)) {
                fechaFin.setCustomValidity('La fecha de fin debe ser posterior a la fecha de inicio');
            } else {
                fechaFin.setCustomValidity('');
            }
        } else {
            fechaFin.setCustomValidity('');
        }
    }
    
    fechaInicio.addEventListener('change', validateDates);
    fechaFin.addEventListener('change', validateDates);
    
    // Contador de caracteres para descripción
    const descripcion = document.getElementById('descripcion');
    const maxLength = 1000;
    
    function updateCounter() {
        const remaining = maxLength - descripcion.value.length;
        const counter = document.querySelector('.form-text');
        if (counter) {
            counter.textContent = `${descripcion.value.length}/${maxLength} caracteres`;
            if (remaining < 50) {
                counter.classList.add('text-warning');
            } else {
                counter.classList.remove('text-warning');
            }
        }
    }
    
    descripcion.addEventListener('input', updateCounter);
    updateCounter(); // Inicializar contador
});
</script>
@endsection
