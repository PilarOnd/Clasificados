@extends('layouts.private')

@section('title', 'Mi Perfil')

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <strong>Mi Perfil</strong>
                </div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
                        @csrf
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4 text-center">
                                <img src="{{ isset($perfil['foto']) ? asset(ltrim($perfil['foto'], '/')) : asset(optional(Auth::user())->avatar_path ?? 'img/hany.png') }}" alt="Avatar actual" class="rounded-circle mb-2" style="width: 120px; height: 120px; object-fit: cover;">
                                <div>
                                    <label class="form-label">Cambiar foto</label>
                                    <input type="file" name="avatar" class="form-control" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $perfil['nombre'] ?? (optional(Auth::user())->name ?? 'Administrador')) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Correo</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $perfil['correo'] ?? (optional(Auth::user())->email ?? 'admin.prueba@gmail.com')) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nueva contraseña</label>
                                    <input type="password" name="password" class="form-control" placeholder="Opcional">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-success">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


