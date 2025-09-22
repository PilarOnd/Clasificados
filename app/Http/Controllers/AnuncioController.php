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

        // Aplicar ordenamiento
        $sort = $request->get('sort');
        $dir = $request->get('dir', 'asc');
        $anuncios = $this->anuncioService->sortAnuncios($anuncios, $sort, $dir);

        return view('anuncios', compact('anuncios'));
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
}
