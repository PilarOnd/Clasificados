@extends('layouts.private')

@section('title', 'Dashboard')

@section('content')
<div class="container">
    <div class="row g-3">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Anuncios Totales</h6>
                        <div class="h3 mb-0">{{ $totalAnuncios }}</div>
                    </div>
                    <i class="bi bi-collection text-success" style="font-size: 2rem;"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


