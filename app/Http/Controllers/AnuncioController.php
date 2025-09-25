<?php

namespace App\Http\Controllers;

use App\Services\AnuncioService;
use Illuminate\Http\Request;

class AnuncioController extends Controller
{
    protected AnuncioService $anuncioService;

    public function __construct(AnuncioService $anuncioService)
    {
        $this->anuncioService = $anuncioService;
    }

    /**
     * Muestra la lista de anuncios con filtros y ordenamiento
     */
    public function index(Request $request)
    {
        $anuncios = $this->anuncioService->getAllAnuncios();

        // Aplicar filtros
        $filters = [
            'q' => $request->get('q'),
            'desde' => $request->get('desde'),
            'hasta' => $request->get('hasta'),
        ];

        $anuncios = $this->anuncioService->filterAnuncios($anuncios, $filters);

        // Filtro por estado (pestañas)
        $status = $request->get('status');
        $anuncios = $this->anuncioService->filterByStatus($anuncios, $status);

        // Conteos por estado (sobre el conjunto total sin filtro de status)
        $counts = $this->anuncioService->getStatusCounts($this->anuncioService->getAllAnuncios());

        // Aplicar ordenamiento
        $sort = $request->get('sort');
        $dir = $request->get('dir', 'asc');
        $anuncios = $this->anuncioService->sortAnuncios($anuncios, $sort, $dir);

        return view('anuncios', compact('anuncios', 'counts', 'status'));
    }

    /**
     * Muestra un anuncio específico
     */
    public function show(int $id)
    {
        $anuncio = $this->anuncioService->findAnuncioById($id);

        if (!$anuncio) {
            abort(404, 'Anuncio no encontrado');
        }

        return view('show', compact('anuncio'));
    }

    /**
     * Muestra el formulario para editar un anuncio
     */
    public function edit(int $id)
    {
        $anuncio = $this->anuncioService->findAnuncioById($id);

        if (!$anuncio) {
            abort(404, 'Anuncio no encontrado');
        }

        return view('anuncios.edit', compact('anuncio'));
    }

    /**
     * Actualiza un anuncio existente
     */
    public function update(Request $request, int $id)
    {
        $anuncio = $this->anuncioService->findAnuncioById($id);

        if (!$anuncio) {
            abort(404, 'Anuncio no encontrado');
        }

        $request->validate([
            'descripcion' => 'required|string|max:1000',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['descripcion', 'fecha_inicio', 'fecha_fin']);
        
        // Manejar subida de imagen si se proporciona
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nombreArchivo = 'anuncio_' . $id . '_' . time() . '.' . $foto->getClientOriginalExtension();
            $foto->move(public_path('img'), $nombreArchivo);
            $data['foto'] = 'img/' . $nombreArchivo;
        }

        $this->anuncioService->updateAnuncio($id, $data);

        return redirect()->route('anuncio.show', $id)
            ->with('success', 'Anuncio actualizado correctamente');
    }
}
